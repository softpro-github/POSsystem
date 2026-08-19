<?php

namespace App\Http\Controllers\Expenses;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(private JournalService $journalService) {}

    public function index(Request $request): View
    {
        $expenses = Expense::with(['category.account', 'user', 'store'])
            ->when($request->filled('from'), fn ($q) => $q->whereDate('expense_date', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('expense_date', '<=', $request->date('to')))
            ->latest('expense_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $totalThisMonth = Expense::whereMonth('expense_date', now()->month)->whereYear('expense_date', now()->year)->sum('amount');

        return view('expenses.index', compact('expenses', 'totalThisMonth'));
    }

    public function create(): View
    {
        $expenseCategories = ExpenseCategory::where('is_active', true)->orderBy('name')->get();

        return view('expenses.create', compact('expenseCategories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
            'paid_via' => ['required', 'in:cash,bank'],
            'expense_date' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $category = ExpenseCategory::with('account')->findOrFail($validated['expense_category_id']);

            $expense = Expense::create([
                'expense_category_id' => $category->id,
                'store_id' => current_store()?->id,
                'user_id' => $request->user()->id,
                'amount' => $validated['amount'],
                'description' => $validated['description'] ?? null,
                'paid_via' => $validated['paid_via'],
                'expense_date' => $validated['expense_date'],
            ]);

            $cashAccount = $validated['paid_via'] === 'cash' ? '1000' : '1010';

            $this->journalService->postEntry(
                lines: [
                    ['account' => $category->account->code, 'debit' => $validated['amount'], 'credit' => 0],
                    ['account' => $cashAccount, 'debit' => 0, 'credit' => $validated['amount']],
                ],
                description: 'Expense: '.$category->name.($validated['description'] ? ' — '.$validated['description'] : ''),
                reference: $expense,
                date: \Illuminate\Support\Carbon::parse($validated['expense_date']),
            );
        });

        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }
}
