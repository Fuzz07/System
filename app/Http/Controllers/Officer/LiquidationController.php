<?php

namespace App\Http\Controllers\Officer;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Liquidation;
use App\Models\Proposal;
use App\Support\UploadValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LiquidationController extends Controller
{
    public function index()
    {
        // A project can be liquidated once the treasurer has released its whole
        // budget; the rest are listed as waiting so the officer knows why.
        [$proposals, $awaitingRelease] = Proposal::where('officer_id', Auth::id())
            ->where('status', 'Approved')
            ->withSum('releases', 'amount_released')
            ->orderBy('project_title')
            ->get()
            ->partition->isBudgetFullyReleased();

        $liquidations = Liquidation::with('proposal')
            ->where('officer_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('officer.liquidation', compact('proposals', 'awaitingRelease', 'liquidations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'proposal_id' => ['required', Rule::exists('proposals', 'id')->where(function ($query) {
                $query->where('officer_id', Auth::id())->where('status', 'Approved');
            })],
            'liq_file'    => UploadValidation::requiredFile(),
            'notes'       => 'nullable|string',
        ]);

        $proposal = Proposal::whereKey($request->proposal_id)
            ->where('officer_id', Auth::id())
            ->where('status', 'Approved')
            ->firstOrFail();

        if (!$proposal->isBudgetFullyReleased()) {
            throw ValidationException::withMessages([
                'proposal_id' => "The treasurer has not released the full budget for \"{$proposal->project_title}\" yet. You can upload its liquidation once it is released.",
            ]);
        }

        try {
            $filePath = SscHelper::uploadToCloudinary($request->file('liq_file'), 'liquidation');
        } catch (\Exception $e) {
            \Log::warning('Cloudinary upload failed for liquidation report, falling back to local public disk: ' . $e->getMessage());
            $filePath = $request->file('liq_file')->store('liquidation', 'public');
        }

        Liquidation::create([
            'proposal_id' => $proposal->id,
            'officer_id'  => Auth::id(),
            'title'       => $request->title,
            'file_path'   => $filePath,
            'notes'       => $request->notes,
        ]);

        SscHelper::logActivity(Auth::id(), 'LIQUIDATION_UPLOAD', "Uploaded liquidation: {$request->title}");
        return redirect()->route('officer.liquidation')->with('success', 'Liquidation report uploaded successfully.');
    }
}