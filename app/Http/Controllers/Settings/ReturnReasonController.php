<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ReturnReason;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReturnReasonController extends Controller
{
    public function index(): View
    {
        $returnReasons = ReturnReason::withCount(['saleReturns', 'purchaseReturns'])->orderBy('name')->paginate(20);

        return view('settings.return-reasons.index', compact('returnReasons'));
    }

    public function create(): View
    {
        return view('settings.return-reasons.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        ReturnReason::create($validated);

        return redirect()->route('return-reasons.index')->with('success', 'Return reason created.');
    }

    public function edit(ReturnReason $returnReason): View
    {
        return view('settings.return-reasons.edit', compact('returnReason'));
    }

    public function update(Request $request, ReturnReason $returnReason): RedirectResponse
    {
        $returnReason->update($this->validated($request));

        return redirect()->route('return-reasons.index')->with('success', 'Return reason updated.');
    }

    public function destroy(ReturnReason $returnReason): RedirectResponse
    {
        if ($returnReason->saleReturns()->exists() || $returnReason->purchaseReturns()->exists()) {
            return back()->with('error', 'Cannot delete a return reason that has been used.');
        }

        $returnReason->delete();

        return redirect()->route('return-reasons.index')->with('success', 'Return reason deleted.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
