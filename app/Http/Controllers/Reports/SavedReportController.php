<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\SavedReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SavedReportController extends Controller
{
    public function index(): View
    {
        $savedReports = SavedReport::with('user')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('reports.saved.index', compact('savedReports'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'report_type' => ['required', 'string'],
            'filters' => ['nullable', 'array'],
            'schedule_frequency' => ['nullable', 'in:daily,weekly,monthly'],
            'recipients' => ['nullable', 'string', 'max:1000'],
        ]);

        SavedReport::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'report_type' => $validated['report_type'],
            'filters' => $validated['filters'] ?? [],
            'schedule_frequency' => $validated['schedule_frequency'] ?? null,
            'recipients' => $validated['recipients'] ?? null,
        ]);

        return back()->with('success', 'Report view saved.');
    }

    public function destroy(SavedReport $savedReport): RedirectResponse
    {
        if ($savedReport->user_id !== auth()->id()) {
            abort(403);
        }

        $savedReport->delete();

        return redirect()->route('saved-reports.index')->with('success', 'Saved report deleted.');
    }
}
