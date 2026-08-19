<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class JournalEntryController extends Controller
{
    public function __construct(private JournalService $journalService) {}

    public function index(Request $request): View
    {
        $entries = JournalEntry::with(['lines', 'createdBy'])
            ->when($request->filled('from'), fn ($q) => $q->whereDate('entry_date', '>=', $request->string('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('entry_date', '<=', $request->string('to')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('entry_date')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('accounting.journal.index', compact('entries'));
    }

    public function create(): View
    {
        $accounts = Account::where('is_active', true)->orderBy('code')->get();

        return view('accounting.journal.create', compact('accounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'entry_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account' => ['required', 'exists:accounts,code'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $lines = collect($validated['lines'])->map(fn ($line) => [
            'account' => $line['account'],
            'debit' => (float) ($line['debit'] ?? 0),
            'credit' => (float) ($line['credit'] ?? 0),
        ])->all();

        $this->journalService->postEntry(
            $lines,
            $validated['description'],
            null,
            Carbon::parse($validated['entry_date']),
        );

        return redirect()->route('accounting.journal.index')->with('success', 'Journal entry posted.');
    }

    public function show(JournalEntry $journalEntry): View
    {
        $journalEntry->load(['lines.account', 'createdBy', 'reversedEntry']);
        $reversedBy = JournalEntry::where('reversed_entry_id', $journalEntry->id)->first();

        return view('accounting.journal.show', compact('journalEntry', 'reversedBy'));
    }

    public function reverse(JournalEntry $journalEntry): RedirectResponse
    {
        $this->journalService->reverseEntry($journalEntry);

        return redirect()->route('accounting.journal.show', $journalEntry)->with('success', 'Entry reversed.');
    }
}
