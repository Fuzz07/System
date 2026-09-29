<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Students the admin adds from the school roster, one at a time or from a CSV
 * export. They start without an email, so they cannot sign in until the
 * student registers with the same ID number and claims the record.
 */
class StudentRoster
{
    /** CSV columns, in the order the file must use. */
    public const COLUMNS = ['School Year', 'Department', 'First Name', 'Middle Name', 'Last Name', 'ID Number'];

    public const ID_PATTERN = '/^\d{4}-\d{4}$/';
    public const NAME_PATTERN = '/^[\pL][\pL\s.\'-]*$/u';

    /** Rows listed back to the admin when an import has problems. */
    public const MAX_REPORTED_ERRORS = 20;

    /**
     * "1st Year", "1st", "1", "First Year" and "YEAR 1" all mean 1st Year.
     * Null when the value is not one of the four year levels.
     */
    public static function normalizeYearLevel(?string $value): ?string
    {
        $value = mb_strtolower(trim((string) $value));
        $levels = StudentPromotionService::YEAR_LEVELS;
        $words = ['first' => 1, 'second' => 2, 'third' => 3, 'fourth' => 4];

        if (preg_match('/^(?:year\s*)?([1-4])(?:st|nd|rd|th)?(?:\s*year)?$/', $value, $m)) {
            return $levels[(int) $m[1] - 1];
        }

        foreach ($words as $word => $n) {
            if (preg_match('/^' . $word . '(?:\s*year)?$/', $value)) {
                return $levels[$n - 1];
            }
        }

        return null;
    }

    /** Matches a department code regardless of case, e.g. "bsit" => "BSIT". */
    public static function normalizeDepartment(?string $value): ?string
    {
        $value = Str::upper(trim((string) $value));

        return in_array($value, Budget::DEPARTMENTS, true) ? $value : null;
    }

    /**
     * Whether two spellings are the same name, ignoring case, spacing,
     * punctuation and accents, so "Dela Cruz", "DELA CRUZ" and "Dela-Cruz"
     * match, and so do "Salvaña" and "Salvana".
     */
    public static function namesMatch(?string $a, ?string $b): bool
    {
        $normalize = fn (?string $name) => preg_replace('/[^a-z]/', '', Str::lower(Str::ascii((string) $name)));

        return $normalize($a) !== '' && $normalize($a) === $normalize($b);
    }

    /** The unclaimed roster record a registering student may take over. */
    public static function claimableRecord(string $studentId): ?User
    {
        return User::where('role', 'student')
            ->where('student_id', $studentId)
            ->whereNull('email')
            ->notArchived()
            ->first();
    }

    /**
     * @param  array{first_name: string, middle_name: ?string, last_name: string, student_id: string, department: string, year_level: string, email?: ?string}  $details
     */
    public static function add(array $details): User
    {
        return User::create([
            'first_name' => $details['first_name'],
            'middle_name' => $details['middle_name'] ?: null,
            'last_name' => $details['last_name'],
            'fullname' => trim(implode(' ', array_filter([$details['first_name'], $details['middle_name'] ?? null, $details['last_name']]))),
            'student_id' => $details['student_id'],
            'department' => $details['department'],
            'year_level' => $details['year_level'],
            'email' => ($details['email'] ?? null) ?: null,
            // Never used: the student sets their own password when they claim
            // the record at registration, or through Forgot Password.
            'password' => Str::random(40),
            'role' => 'student',
            'status' => 'active',
        ]);
    }

    /**
     * Adds every valid row of a roster CSV. Rows whose ID number is already on
     * file are skipped, and invalid rows are reported back without stopping
     * the rest of the import.
     *
     * @return array{added: int, existing: int, invalid: int, errors: array<int, string>}
     */
    public function import(string $path): array
    {
        $result = ['added' => 0, 'existing' => 0, 'invalid' => 0, 'errors' => []];

        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new \RuntimeException('Failed to open the CSV file.');
        }

        try {
            $delimiter = $this->detectDelimiter($handle);

            DB::transaction(function () use ($handle, $delimiter, &$result) {
                $line = 0;

                while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                    $line++;
                    $cells = array_map(fn ($cell) => trim((string) $cell), array_pad($row, count(self::COLUMNS), ''));
                    if ($line === 1) {
                        $cells[0] = preg_replace('/^\xEF\xBB\xBF/', '', $cells[0]);
                    }

                    if (implode('', $cells) === '') {
                        continue;
                    }

                    if ($line === 1 && $this->isHeader($cells)) {
                        continue;
                    }

                    [$year, $department, $first, $middle, $last, $studentId] = $cells;
                    $problem = $this->problemWith($year, $department, $first, $middle, $last, $studentId);

                    if ($problem) {
                        $result['invalid']++;
                        if (count($result['errors']) < self::MAX_REPORTED_ERRORS) {
                            $result['errors'][] = "Row {$line}: {$problem}";
                        }
                        continue;
                    }

                    if (User::where('student_id', $studentId)->exists()) {
                        $result['existing']++;
                        continue;
                    }

                    self::add([
                        'first_name' => $first,
                        'middle_name' => $middle,
                        'last_name' => $last,
                        'student_id' => $studentId,
                        'department' => self::normalizeDepartment($department),
                        'year_level' => self::normalizeYearLevel($year),
                    ]);
                    $result['added']++;
                }
            });
        } finally {
            fclose($handle);
        }

        return $result;
    }

    protected function problemWith(string $year, string $department, string $first, string $middle, string $last, string $studentId): ?string
    {
        if (! self::normalizeYearLevel($year)) {
            return 'School Year must be 1st, 2nd, 3rd or 4th Year' . ($year !== '' ? " (got \"{$year}\")." : ' (it is empty).');
        }
        if (! self::normalizeDepartment($department)) {
            return 'Department must be one of ' . implode(', ', Budget::DEPARTMENTS) . ($department !== '' ? " (got \"{$department}\")." : ' (it is empty).');
        }
        foreach (['First Name' => $first, 'Last Name' => $last] as $label => $name) {
            if (mb_strlen($name) < 2 || mb_strlen($name) > 100 || ! preg_match(self::NAME_PATTERN, $name)) {
                return "{$label} is required and may contain letters, spaces, periods, apostrophes and hyphens only.";
            }
        }
        if ($middle !== '' && (mb_strlen($middle) > 100 || ! preg_match(self::NAME_PATTERN, $middle))) {
            return 'Middle Name may contain letters, spaces, periods, apostrophes and hyphens only.';
        }
        if (! preg_match(self::ID_PATTERN, $studentId)) {
            return 'ID Number must use the format YYYY-XXXX, e.g. 2024-0001' . ($studentId !== '' ? " (got \"{$studentId}\")." : ' (it is empty).');
        }

        return null;
    }

    /** A first row that is not itself a valid student is taken as the header. */
    protected function isHeader(array $cells): bool
    {
        return ! self::normalizeYearLevel($cells[0]) && ! preg_match(self::ID_PATTERN, $cells[5]);
    }

    /** Excel saves "CSV" with semicolons in some regional settings. */
    protected function detectDelimiter($handle): string
    {
        $firstLine = (string) fgets($handle);
        rewind($handle);

        return substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
    }
}
