<?php

namespace App\Http\Controllers\Treasurer;

use App\Http\Controllers\Controller;
use App\Helpers\SscHelper;
use App\Models\Announcement;
use App\Models\BudgetRelease;
use App\Models\Proposal;
use App\Support\UploadValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReleaseController extends Controller
{
    /** Cash is handed over in person, so it has no reference / transaction number. */
    public const CASH = 'Cash';

    public const RELEASE_METHODS = [self::CASH, 'Bank Transfer', 'Check', 'GCash', 'Maya', 'Other'];

    /** Starts with a letter or digit; then letters, digits, dashes, slashes and spaces. */
    public const REFERENCE_PATTERN = '/^[A-Za-z0-9][A-Za-z0-9\-\/ ]*$/';

    public function index(Request $request)
    {
        $filterSearch = trim($request->query('search', ''));
        // After a failed submit, reopen the form on the proposal being released.
        $selectedPid = (int)$request->query('proposal_id', $request->old('proposal_id', 0));

        // Fetch all approved proposals with release summary
        $proposalsQuery = Proposal::where('status', 'Approved')
            ->with('officer')
            ->select('proposals.*')
            ->selectSub(function ($query) {
                $query->selectRaw('COALESCE(SUM(amount_released), 0)')
                    ->from('budget_releases')
                    ->whereColumn('proposal_id', 'proposals.id');
            }, 'total_released')
            ->selectSub(function ($query) {
                $query->selectRaw('COUNT(id)')
                    ->from('budget_releases')
                    ->whereColumn('proposal_id', 'proposals.id');
            }, 'release_count');

        if ($filterSearch !== '') {
            $proposalsQuery->where(function($q) use ($filterSearch) {
                $q->where('project_title', 'like', "%{$filterSearch}%")
                  ->orWhereHas('officer', function($uq) use ($filterSearch) {
                      $uq->where('fullname', 'like', "%{$filterSearch}%");
                  });
            });
        }

        $proposals = $proposalsQuery->orderBy('created_at', 'desc')->get();

        // All budget releases history
        $allReleases = BudgetRelease::with(['proposal.officer', 'treasurer'])
            ->orderBy('created_at', 'desc')
            ->get();

        // If a specific proposal is pre-selected
        $selectedProposal = null;
        if ($selectedPid > 0) {
            $selectedProposal = $proposals->firstWhere('id', $selectedPid);
        }

        return view('treasurer.release', compact(
            'filterSearch',
            'proposals',
            'allReleases',
            'selectedProposal',
            'selectedPid'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'proposal_id'     => 'required|integer|exists:proposals,id',
            'amount_released' => 'required|numeric|decimal:0,2|min:0.01',
            'release_method'  => 'required|in:' . implode(',', self::RELEASE_METHODS),
            'reference_no'    => ['exclude_if:release_method,' . self::CASH, 'required', 'string', 'min:4', 'max:50', 'regex:' . self::REFERENCE_PATTERN, 'unique:budget_releases,reference_no'],
            'release_status'  => 'required|in:Released,Partial',
            'notes'           => 'nullable|string|max:1000',
            'receipt'         => UploadValidation::optionalFile(),
        ], [
            'amount_released.decimal' => 'The amount to release can have at most 2 decimal places.',
            'release_method.in'       => 'Choose one of the listed release methods.',
            'reference_no.required'   => 'Enter the reference / transaction number for this release (e.g. voucher no., GCash ref, check no.).',
            'reference_no.min'        => 'The reference number must be at least 4 characters.',
            'reference_no.max'        => 'The reference number may be at most 50 characters.',
            'reference_no.regex'      => 'The reference number may only contain letters, numbers, dashes, slashes and spaces.',
            'reference_no.unique'     => 'Reference number ":input" is already recorded on another release. Check that this release was not entered twice.',
        ]);

        $pid = (int)$request->proposal_id;
        $proposal = Proposal::where('id', $pid)->where('status', 'Approved')->first();
        if (!$proposal) {
            throw ValidationException::withMessages(['proposal_id' => 'Only approved proposals can have their budget released.']);
        }

        // Compare in centavos so float rounding can't let an extra ₱0.01 through.
        $totalAlreadyReleased = (float)BudgetRelease::where('proposal_id', $pid)->sum('amount_released');
        $remainingCents = (int)round((float)$proposal->approved_budget * 100) - (int)round($totalAlreadyReleased * 100);
        $maxReleasable = $remainingCents / 100;

        $amtReleased = (float)$request->amount_released;
        $amountCents = (int)round($amtReleased * 100);

        if ($remainingCents <= 0) {
            throw ValidationException::withMessages(['amount_released' => "The approved budget for \"{$proposal->project_title}\" has already been fully released."]);
        }
        if ($amountCents > $remainingCents) {
            throw ValidationException::withMessages(['amount_released' => 'Cannot release ' . SscHelper::formatCurrency($amtReleased) . '. Only ' . SscHelper::formatCurrency($maxReleasable) . ' remains to be released for this project.']);
        }
        // "Released" is the full / final disbursement; anything less is a partial one.
        if ($request->release_status === 'Released' && $amountCents < $remainingCents) {
            throw ValidationException::withMessages(['release_status' => 'A full / final release must cover the whole remaining balance of ' . SscHelper::formatCurrency($maxReleasable) . '. Mark smaller amounts as a Partial Release.']);
        }
        if ($request->release_status === 'Partial' && $amountCents === $remainingCents) {
            throw ValidationException::withMessages(['release_status' => 'This amount releases the whole remaining balance. Mark it as "Released (Full / Final)" instead of Partial.']);
        }

        // Handle receipt upload
        $receiptFile = null;
        if ($request->hasFile('receipt')) {
            try {
                $receiptFile = SscHelper::uploadToCloudinary($request->file('receipt'), 'releases');
            } catch (\Exception $e) {
                \Log::warning('Cloudinary upload failed for budget release receipt, falling back to local public disk: ' . $e->getMessage());
                $receiptFile = $request->file('receipt')->store('releases', 'public');
            }
        }

        BudgetRelease::create([
            'proposal_id'     => $pid,
            'released_by'     => Auth::id(),
            'amount_released' => $amtReleased,
            'release_method'  => $request->release_method,
            'reference_no'    => $request->release_method === self::CASH ? null : $request->reference_no,
            'receipt_file'    => $receiptFile,
            'notes'           => $request->notes,
            'release_status'  => $request->release_status,
        ]);

        // If release status is "Released" (Final), it means fully released.
        // We can log this
        $formattedAmt = SscHelper::formatCurrency($amtReleased);
        SscHelper::logActivity(
            Auth::id(),
            'BUDGET_RELEASE',
            "Released {$formattedAmt} for Proposal ID {$pid} via {$request->release_method}"
        );

        return redirect()->route('treasurer.release')->with('success', "Budget of {$formattedAmt} released successfully for \"{$proposal->project_title}\".");
    }

    public function reports()
    {
        $sy = SscHelper::getActiveSchoolYear();

        $totalReleased = BudgetRelease::sum('amount_released');
        $countReleases = BudgetRelease::count();
        $approvedBudget = Proposal::where('status', 'Approved')->sum('approved_budget');

        $byMethod = BudgetRelease::select('release_method', DB::raw('COUNT(*) as cnt'), DB::raw('SUM(amount_released) as total'))
            ->groupBy('release_method')
            ->orderByDesc('total')
            ->get();

        $releases = BudgetRelease::with(['proposal.officer', 'treasurer'])
            ->orderBy('created_at', 'desc')
            ->get();

        $proposalSummary = Proposal::where('status', 'Approved')
            ->with('officer')
            ->select('proposals.*')
            ->selectSub(function ($query) {
                $query->selectRaw('COALESCE(SUM(amount_released), 0)')
                    ->from('budget_releases')
                    ->whereColumn('proposal_id', 'proposals.id');
            }, 'total_released')
            ->selectSub(function ($query) {
                $query->selectRaw('COUNT(id)')
                    ->from('budget_releases')
                    ->whereColumn('proposal_id', 'proposals.id');
            }, 'release_count')
            ->orderByDesc('total_released')
            ->get();

        return view('treasurer.reports', compact(
            'sy',
            'totalReleased',
            'countReleases',
            'approvedBudget',
            'byMethod',
            'releases',
            'proposalSummary'
        ));
    }

    public function announcements(Request $request)
    {
        $category = $request->input('category');

        $query = Announcement::with(['author', 'proposal'])->orderByDesc('created_at');
        if ($category) {
            $query->where('category', $category);
        }
        $announcements = $query->get();

        return view('treasurer.announcements', compact('announcements', 'category'));
    }
}
