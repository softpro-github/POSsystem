<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $accounts = Account::withCount('lines')->orderBy('type')->orderBy('code')->get()->groupBy('type');

        return view('accounting.accounts.index', compact('accounts'));
    }

    public function create(): View
    {
        $parents = Account::orderBy('code')->get();

        return view('accounting.accounts.create', compact('parents'));
    }

    public function store(Request $request): RedirectResponse
    {
        Account::create($this->validated($request));

        return redirect()->route('accounting.accounts.index')->with('success', 'Account created.');
    }

    public function edit(Account $account): View
    {
        $parents = Account::where('id', '!=', $account->id)->orderBy('code')->get();

        return view('accounting.accounts.edit', compact('account', 'parents'));
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $account->update($this->validated($request, $account));

        return redirect()->route('accounting.accounts.index')->with('success', 'Account updated.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        if ($account->lines()->exists() || $account->children()->exists()) {
            return back()->with('error', 'Cannot delete an account that has journal activity or sub-accounts.');
        }

        $account->delete();

        return redirect()->route('accounting.accounts.index')->with('success', 'Account deleted.');
    }

    private function validated(Request $request, ?Account $account = null): array
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:accounts,code'.($account ? ','.$account->id : '')],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:asset,liability,equity,income,expense'],
            'parent_id' => ['nullable', 'exists:accounts,id'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
