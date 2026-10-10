<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Services\SemesterReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The per-semester transparency report. Students read it to see where their
 * contribution fees went; the treasurer and admin read and print the same
 * figures. One controller serves all three portals, as the notification pages
 * do, so the numbers can never drift between what staff see and what students see.
 */
class SemesterReportController extends Controller
{
    /** Portals that may open a semester report, mapped to their route name prefix. */
    private const PORTALS = [
        'admin'     => 'admin',
        'treasurer' => 'treasurer',
        'student'   => 'student',
    ];

    public function index(Request $request)
    {
        $terms = SemesterReport::reportableTerms();
        $term = SemesterReport::resolveTerm($request->query('term'));

        return view('shared.semester-report', [
            'terms'    => $terms,
            'term'     => $term,
            'report'   => $term ? SemesterReport::forTerm($term) : null,
            'sidebar'  => 'partials.sidebar-' . $this->portal(),
            'portal'   => $this->portal(),
        ]);
    }

    /** The printable sheet for one term, laid out like the treasurer's other reports. */
    public function print(SchoolYear $schoolYear)
    {
        return view('shared.semester-report-print', [
            'report'    => SemesterReport::forTerm($schoolYear),
            'portal'    => $this->portal(),
            'backUrl'   => route($this->portal() . '.semester_reports', ['term' => $schoolYear->id]),
            'backLabel' => 'Back to Semester Reports',
        ]);
    }

    /** The signed-in user's portal, used to build links back into their own side of the app. */
    private function portal(): string
    {
        return self::PORTALS[Auth::user()->role] ?? 'student';
    }
}
