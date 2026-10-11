<?php

namespace App\Services;

use App\Models\SchoolYear;
use App\Models\User;

class EnrollmentFee
{
    public static function forStudent(User $student, ?SchoolYear $schoolYear = null): float
    {
        $schoolYear ??= SchoolYear::query()->where('is_active', true)->first();

        return self::forYearLevel($student->year_level, $schoolYear?->semester);
    }

    public static function forYearLevel(?string $yearLevel, ?string $semester): float
    {
        if ($yearLevel === '1st Year' && $semester === SchoolYear::SEMESTER_FIRST) {
            return (float) config('ssc.enrollment_fee_first_year_first_semester_amount', 100);
        }

        return (float) config('ssc.enrollment_fee_amount', 50);
    }
}
