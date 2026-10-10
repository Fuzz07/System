<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Services\SemesterReport;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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

    /** Rows per table on the portal page; the printed sheets always carry every row. */
    private const PER_PAGE = 8;

    public function index(Request $request)
    {
        $terms = SemesterReport::reportableTerms();
        $term = SemesterReport::resolveTerm($request->query('term'));
        $report = $term ? SemesterReport::forTerm($term) : null;

        return view('shared.semester-report', [
            'terms'    => $terms,
            'term'     => $term,
            'report'   => $report,
            'projects' => $this->paginate($report?->projects ?? collect(), 'projects', $request),
            'expenses' => $this->paginate($report?->expenses ?? collect(), 'expenses', $request),
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

    /** The semester's Records of Expenses: every month of the term, itemised. */
    public function records(SchoolYear $schoolYear)
    {
        return view('shared.semester-records', [
            'report'    => SemesterReport::forTerm($schoolYear),
            'portal'    => $this->portal(),
            'backUrl'   => route($this->portal() . '.semester_reports', ['term' => $schoolYear->id]),
            'backLabel' => 'Back to Semester Reports',
        ]);
    }

    /**
     * Pages one of the report's tables. Each table keeps its own page number so
     * stepping through projects does not reset the expense list, and the chosen
     * term rides along in the links.
     */
    private function paginate(Collection $items, string $pageName, Request $request): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage($pageName);

        return new LengthAwarePaginator(
            $items->forPage($page, self::PER_PAGE)->values(),
            $items->count(),
            self::PER_PAGE,
            $page,
            [
                'path' => $request->url(),
                'pageName' => $pageName,
                'query' => Arr::except($request->query(), $pageName),
            ]
        );
    }

    /** The signed-in user's portal, used to build links back into their own side of the app. */
    private function portal(): string
    {
        return self::PORTALS[Auth::user()->role] ?? 'student';
    }
}
