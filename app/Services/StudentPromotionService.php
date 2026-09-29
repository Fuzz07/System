<?php

namespace App\Services;

use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Keeps student year levels in step with the school year. When a later school
 * year becomes active, every student on the roster moves up one level for each
 * year it is ahead, and 4th years graduate: they are set inactive and archived.
 *
 * Each school year is marked once students have been moved into it, so
 * switching semesters, or re-activating a year by mistake and then going back,
 * never promotes anyone twice.
 */
class StudentPromotionService
{
    public const YEAR_LEVELS = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
    public const GRADUATED = 'Graduated';

    /**
     * How many year levels students would move up if $schoolYear became the
     * active one: 0 unless it is ahead of every year students were moved into.
     */
    public function yearsAhead(SchoolYear $schoolYear): int
    {
        if ($schoolYear->students_promoted_at || $schoolYear->start_year === null) {
            return 0;
        }

        $latest = $this->latestPromotedStartYear();

        return $latest === null ? 0 : max(0, $schoolYear->start_year - $latest);
    }

    /**
     * Run when $schoolYear becomes active.
     *
     * @return array{years: int, promoted: int, graduated: int}
     */
    public function advanceTo(SchoolYear $schoolYear): array
    {
        $result = ['years' => 0, 'promoted' => 0, 'graduated' => 0];

        if ($schoolYear->students_promoted_at || $schoolYear->start_year === null) {
            return $result;
        }

        $latest = $this->latestPromotedStartYear();
        $years = $latest === null ? 0 : $schoolYear->start_year - $latest;

        // Older than a year students already reached: leave levels alone.
        if ($latest !== null && $years <= 0) {
            return $result;
        }

        DB::transaction(function () use ($schoolYear, $latest, $years, &$result) {
            // With nothing marked yet, the levels on record already describe
            // this year, so it only becomes the starting point.
            if ($years > 0) {
                $result = ['years' => $years] + $this->promote($latest, min($years, count(self::YEAR_LEVELS)));
            }

            $schoolYear->forceFill(['students_promoted_at' => now()])->save();
        });

        return $result;
    }

    /**
     * @return array{promoted: int, graduated: int}
     */
    protected function promote(int $fromStartYear, int $steps): array
    {
        $roster = fn () => User::where('role', 'student')->notArchived();
        $onRoster = $roster()->whereIn('year_level', self::YEAR_LEVELS)->count();
        $finalYear = self::YEAR_LEVELS[count(self::YEAR_LEVELS) - 1];
        $graduated = 0;

        for ($step = 0; $step < $steps; $step++) {
            $endingYear = $fromStartYear + $step;

            $graduated += $roster()->where('year_level', $finalYear)->update([
                'year_level' => self::GRADUATED,
                'status' => 'inactive',
                'archived_at' => now(),
                'graduated_school_year' => $endingYear . '-' . ($endingYear + 1),
            ]);

            // Highest level first, so nobody moves up twice in the same step.
            for ($i = count(self::YEAR_LEVELS) - 1; $i > 0; $i--) {
                $roster()->where('year_level', self::YEAR_LEVELS[$i - 1])
                    ->update(['year_level' => self::YEAR_LEVELS[$i]]);
            }
        }

        return ['promoted' => $onRoster - $graduated, 'graduated' => $graduated];
    }

    protected function latestPromotedStartYear(): ?int
    {
        return SchoolYear::whereNotNull('students_promoted_at')->get()
            ->map(fn (SchoolYear $sy) => $sy->start_year)
            ->filter()
            ->max();
    }
}
