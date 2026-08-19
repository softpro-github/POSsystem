<?php

namespace App\Http\Controllers;

use App\Models\PrintJob;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrintJobController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:receipt,label'],
            'sale_id' => ['nullable', 'exists:sales,id'],
            'payload' => ['nullable', 'array'],
            'reference' => ['required', 'string', 'max:255'],
        ]);

        $printJob = PrintJob::create([
            ...$validated,
            'requested_by' => $request->user()->id,
            'store_id' => current_store()?->id,
            'status' => 'opened',
            'opened_at' => now(),
        ]);

        return response()->json(['id' => $printJob->id]);
    }

    public function markClosed(PrintJob $printJob): JsonResponse
    {
        $printJob->update(['closed_at' => now()]);

        return response()->json(['ok' => true]);
    }

    public function index(Request $request): View
    {
        $printJobs = PrintJob::with('requestedBy')
            ->when(current_store(), fn ($q) => $q->where('store_id', current_store()->id))
            ->latest()
            ->paginate(20);

        return view('print-jobs.index', compact('printJobs'));
    }
}
