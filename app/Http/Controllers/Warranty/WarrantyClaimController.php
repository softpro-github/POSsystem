<?php

namespace App\Http\Controllers\Warranty;

use App\Http\Controllers\Controller;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WarrantyClaimController extends Controller
{
    public function store(Request $request, Warranty $warranty): RedirectResponse
    {
        if ($warranty->status !== 'active') {
            return back()->with('error', 'Cannot file a claim on a warranty that is not active.');
        }

        $validated = $request->validate([
            'claim_date' => ['required', 'date'],
            'issue_description' => ['required', 'string'],
        ]);

        $warranty->claims()->create([
            'claim_date' => $validated['claim_date'],
            'issue_description' => $validated['issue_description'],
            'status' => 'open',
        ]);

        return redirect()->route('warranties.show', $warranty)->with('success', 'Warranty claim filed.');
    }

    public function update(Request $request, WarrantyClaim $warrantyClaim): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,rejected'],
            'resolution' => ['nullable', 'string'],
        ]);

        $warrantyClaim->update([
            'status' => $validated['status'],
            'resolution' => $validated['resolution'] ?? $warrantyClaim->resolution,
            'handled_by' => $request->user()->id,
        ]);

        return redirect()->route('warranties.show', $warrantyClaim->warranty)->with('success', 'Warranty claim updated.');
    }
}
