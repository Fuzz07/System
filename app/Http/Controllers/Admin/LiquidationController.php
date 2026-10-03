<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Liquidation;
use App\Notifications\LiquidationReviewedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LiquidationController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', '');
        if ($status !== '' && !in_array($status, ['Pending', 'Approved', 'Rejected'], true)) {
            $status = '';
        }

        $liquidations = Liquidation::with(['proposal', 'officer', 'reviewer'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByRaw("CASE WHEN status = 'Pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin.liquidations', compact('liquidations', 'status'));
    }

    public function review(Request $request, Liquidation $liquidation)
    {
        $validated = $request->validate([
            'action' => 'required|in:approve,reject',
            'review_notes' => 'nullable|required_if:action,reject|string|max:1000',
        ], [
            'review_notes.required_if' => 'Add a reason when rejecting a liquidation report.',
        ]);

        $liquidation = DB::transaction(function () use ($liquidation, $validated): Liquidation {
            $lockedLiquidation = Liquidation::whereKey($liquidation->id)->lockForUpdate()->firstOrFail();
            if ($lockedLiquidation->status !== 'Pending') {
                abort(403, 'This liquidation report has already been reviewed.');
            }

            $lockedLiquidation->update([
                'status' => $validated['action'] === 'approve' ? 'Approved' : 'Rejected',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'review_notes' => $validated['review_notes'] ?? null,
            ]);

            return $lockedLiquidation->load('officer');
        });

        try {
            $liquidation->officer?->notify(new LiquidationReviewedNotification($liquidation));
        } catch (\Throwable $exception) {
            Log::error('Unable to notify an officer about a liquidation review.', [
                'liquidation_id' => $liquidation->id,
                'officer_id' => $liquidation->officer_id,
                'message' => $exception->getMessage(),
            ]);
        }

        SscHelper::logActivity(
            Auth::id(),
            $liquidation->status === 'Approved' ? 'LIQUIDATION_APPROVE' : 'LIQUIDATION_REJECT',
            ucfirst(strtolower($liquidation->status)) . " liquidation report: {$liquidation->title}"
        );

        return redirect()->route('admin.liquidations')->with(
            'success',
            "Liquidation report {$liquidation->status}."
        );
    }
}
