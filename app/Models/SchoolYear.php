<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SchoolYear extends Model
{
    public const SEMESTER_FIRST = 'first';
    public const SEMESTER_SECOND = 'second';

    public const SEMESTERS = [
        self::SEMESTER_FIRST => 'First Semester',
        self::SEMESTER_SECOND => 'Second Semester',
    ];

    protected $table = 'school_years';
    public $timestamps = false;
    protected $fillable = [
        'label',
        'semester',
        'starts_on',
        'ends_on',
        'is_active',
        'candidacy_open',
        'voting_open',
        'voting_starts_at',
        'voting_ends_at',
        'results_announced',
        'students_promoted_at',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'students_promoted_at' => 'datetime',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'is_active' => 'boolean',
        'candidacy_open' => 'boolean',
        'voting_open' => 'boolean',
        'voting_starts_at' => 'datetime',
        'voting_ends_at' => 'datetime',
        'results_announced' => 'boolean',
    ];

    public function getSemesterLabelAttribute(): string
    {
        return self::SEMESTERS[$this->semester] ?? self::SEMESTERS[self::SEMESTER_FIRST];
    }

    public function getAcademicTermAttribute(): string
    {
        return $this->label . ' - ' . $this->semester_label;
    }

    /**
     * The first year of the label, e.g. 2026 for "2026-2027", used to tell how
     * far one school year is ahead of another. Null for a malformed label.
     */
    public function getStartYearAttribute(): ?int
    {
        return preg_match('/^(\d{4})-\d{4}$/', (string) $this->label, $m) ? (int) $m[1] : null;
    }

    /**
     * Where a term falls on the calendar when no one has set its dates: the
     * usual local academic year, first semester August-December and second
     * semester January-July of the following year. Null for a malformed label.
     *
     * @return array{0: ?Carbon, 1: ?Carbon} Start and end date.
     */
    public function defaultTermDates(): array
    {
        $startYear = $this->start_year;

        if ($startYear === null) {
            return [null, null];
        }

        if (($this->semester ?: self::SEMESTER_FIRST) === self::SEMESTER_FIRST) {
            return [Carbon::create($startYear, 8, 1)->startOfDay(), Carbon::create($startYear, 12, 31)->startOfDay()];
        }

        return [Carbon::create($startYear + 1, 1, 1)->startOfDay(), Carbon::create($startYear + 1, 7, 31)->startOfDay()];
    }

    /** First day of the term, as set by an admin or derived from the calendar. */
    public function termStart(): ?Carbon
    {
        return $this->starts_on ? $this->starts_on->copy()->startOfDay() : $this->defaultTermDates()[0];
    }

    /** Last day of the term, as set by an admin or derived from the calendar. */
    public function termEnd(): ?Carbon
    {
        return $this->ends_on ? $this->ends_on->copy()->startOfDay() : $this->defaultTermDates()[1];
    }

    /** Whether the term has a usable date window to report on. */
    public function hasTermWindow(): bool
    {
        return $this->termStart() !== null && $this->termEnd() !== null;
    }

    /** "August 1 - December 31, 2026", for report headings. */
    public function termRangeLabel(): string
    {
        $start = $this->termStart();
        $end = $this->termEnd();

        if (!$start || !$end) {
            return 'Dates not set';
        }

        return $start->format('F j, Y') . ' - ' . $end->format('F j, Y');
    }

    /** Terms in calendar order, most recent first, with undated terms last. */
    public function scopeNewestTermFirst(Builder $query): Builder
    {
        return $query->orderByRaw('starts_on IS NULL')->orderByDesc('starts_on')->orderByDesc('id');
    }

    /**
     * Old payment rows stored only the school-year label. Treat those as first
     * semester records so upgrading does not ask already-paid students to pay again.
     *
     * @return array<int, string>
     */
    public function enrollmentTermKeys(): array
    {
        $keys = [$this->academic_term];

        if (($this->semester ?: self::SEMESTER_FIRST) === self::SEMESTER_FIRST) {
            $keys[] = $this->label;
        }

        return array_values(array_unique($keys));
    }
}
