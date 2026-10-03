<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ContributionFeeStudentImport
{
    public const COLUMNS = ['Student', 'Student ID', 'Department', 'Year'];

    public function import(string $path): array
    {
        $handle = fopen($path, 'r');
        if (! $handle) {
            throw new \RuntimeException('Failed to open the CSV file.');
        }

        try {
            return $this->read($handle, $this->delimiter($handle));
        } finally {
            fclose($handle);
        }
    }

    protected function read($handle, string $delimiter): array
    {
        $result = ['added' => 0, 'existing' => 0, 'invalid' => 0, 'errors' => []];
        $line = 0;
        DB::transaction(function () use ($handle, $delimiter, &$result, &$line) {
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $line++;
                $row = array_map(fn ($cell) => trim((string) $cell), $row);
                if ($line === 1) $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0] ?? '');
                if (implode('', $row) === '') continue;
                if (count($row) > 4) $row = [implode(', ', array_slice($row, 0, count($row) - 3)), ...array_slice($row, -3)];
                $row = array_pad(array_slice($row, 0, 4), 4, '');
                if ($line === 1 && mb_strtolower($row[0]) === 'student' && mb_strtolower($row[1]) === 'student id') continue;
                $this->addRow($row, $line, $result);
            }
        });
        return $result;
    }

    protected function nameParts(string $name): ?array
    {
        $name = preg_replace('/\s+/u', ' ', trim($name));
        if (str_contains($name, ',')) {
            [$last, $given] = array_map('trim', explode(',', $name, 2));
            $parts = preg_split('/\s+/u', $given, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $first = array_shift($parts) ?? '';
            $middle = implode(' ', $parts);
        } else {
            $parts = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            if (count($parts) < 2) return null;
            $first = array_shift($parts);
            $last = array_pop($parts);
            $middle = implode(' ', $parts);
        }
        foreach ([$first, $last] as $part) {
            if (mb_strlen($part) < 2 || mb_strlen($part) > 100 || ! preg_match(StudentRoster::NAME_PATTERN, $part)) return null;
        }
        if ($middle !== '' && ! preg_match(StudentRoster::NAME_PATTERN, $middle)) return null;
        return ['first_name' => $first, 'middle_name' => $middle ?: null, 'last_name' => $last];
    }

    protected function problem(?array $name, string $id, string $department, string $year): ?string
    {
        return null;
    }

    protected function delimiter($handle): string
    {
        return ',';
    }
}
