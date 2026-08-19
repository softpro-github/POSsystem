<?php

namespace App\Http\Controllers\Expenses;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\ExpenseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseCategoryController extends Controller
{
    public function index(): View
    {
        $expenseCategories = ExpenseCategory::with('account')->withCount('expenses')->orderBy('name')->paginate(20);

        return view('expenses.categories.index', compact('expenseCategories'));
    }

    public function create(): View
    {
        $accounts = Account::where('type', 'expense')->where('is_active', true)->orderBy('code')->get();

        return view('expenses.categories.create', compact('accounts'));
    }

    public function store(Request $request): RedirectResponse
    {
        ExpenseCategory::create($this->validated($request));

        return redirect()->route('expense-categories.index')->with('success', 'Expense category created.');
    }

    public function edit(ExpenseCategory $expenseCategory): View
    {
        $accounts = Account::where('type', 'expense')->where('is_active', true)->orderBy('code')->get();

        return view('expenses.categories.edit', compact('expenseCategory', 'accounts'));
    }

    public function update(Request $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $expenseCategory->update($this->validated($request));

        return redirect()->route('expense-categories.index')->with('success', 'Expense category updated.');
    }

    public function destroy(ExpenseCategory $expenseCategory): RedirectResponse
    {
        if ($expenseCategory->expenses()->exists()) {
            return back()->with('error', 'Cannot delete an expense category that has expenses recorded against it.');
        }

        $expenseCategory->delete();

        return redirect()->route('expense-categories.index')->with('success', 'Expense category deleted.');
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'account_id' => ['required', 'exists:accounts,id'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
