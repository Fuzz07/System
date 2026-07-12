<?php

namespace App\Http\Controllers\Officer;

use App\Helpers\SscHelper;
use App\Http\Controllers\Controller;
use App\Models\Liquidation;
use App\Models\Proposal;
use App\Support\UploadValidation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LiquidationController extends Controller
{
    public function index()
    {
        $proposals = Proposal::where('officer_id', Auth::id())
            ->where('status', 'Approved')
            ->orderBy('project_title')
            ->get();

        $liquidations = Liquidation::with('proposal')
            ->where('officer_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        return view('officer.liquidation', compact('proposals', 'liquidations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'proposal_id' => 'nullable|exists:proposals,id',
            'liq_file'    => UploadValidation::requiredFile(),
            'notes'       => 'nullable|string',
        ]);

        $filePath = $request->file('liq_file')->store('liquidation', 'public');

        Liquidation::create([
            'proposal_id' => $request->proposal_id,
            'officer_id'  => Auth::id(),
            'title'       => $request->title,
            'file_path'   => $filePath,
            'notes'       => $request->notes,
        ]);

        SscHelper::logActivity(Auth::id(), 'LIQUIDATION_UPLOAD', "Uploaded liquidation: {$request->title}");
        return redirect()->route('officer.liquidation')->with('success', 'Liquidation report uploaded successfully.');
    }
}
