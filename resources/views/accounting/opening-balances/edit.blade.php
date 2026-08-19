<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Opening Balances</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-4">
        <x-flash-messages />

        @if ($locked)
            <div class="bg-amber-500/10 border border-amber-500/30 text-amber-400 text-sm rounded-md px-4 py-3">
                Opening balances are locked — real transactions already exist in the books. Figures below are read-only.
            </div>
        @else
            <p class="text-sm text-ink-muted">Enter the starting balance for each account before your first real transaction. Assets/Expenses are debit balances, Liabilities/Equity/Income are credit balances. If your figures don't balance, the difference posts automatically to Retained Earnings.</p>
        @endif

        <form method="POST" action="{{ route('accounting.opening-balances.update') }}" class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            @csrf
            @method('PUT')
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Code</th>
                        <th class="py-3 px-4">Account</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4 text-right">Opening Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($accounts as $account)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-2 px-4">{{ $account->code }}</td>
                            <td class="py-2 px-4">{{ $account->name }}</td>
                            <td class="py-2 px-4">{{ ucfirst($account->type) }}</td>
                            <td class="py-2 px-4 text-right">
                                <input type="number" step="0.01" name="amounts[{{ $account->id }}]"
                                    value="{{ old('amounts.'.$account->id, $existingAmounts[$account->id] ?? '') }}"
                                    @disabled($locked)
                                    class="w-32 text-sm bg-surface-hover border-border-strong text-ink rounded-md text-right disabled:opacity-50">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @unless ($locked)
                <div class="p-4 border-t border-border">
                    <x-primary-button>Post Opening Balances</x-primary-button>
                </div>
            @endunless
        </form>
    </div>
</x-app-layout>
