<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'is_active',
        'candidacy_open',
        'voting_open',
        'voting_starts_at',
        'voting_ends_at',
        'results_announced',
    ];
    protected $casts = [
        'created_at' => 'datetime',
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
