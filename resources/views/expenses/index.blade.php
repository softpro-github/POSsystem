<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Expenses</h2>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('expense-categories.index') }}" class="text-sm text-ink-muted hover:text-ink hover:underline">Manage Categories</a>
                <a href="{{ route('expenses.create') }}" class="inline-flex items-center px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">Record Expense</a>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-4">
        <x-flash-messages />

        <div class="bg-surface-raised border border-border rounded-lg p-4">
            <div class="text-xs text-ink-muted">This Month</div>
            <div class="text-xl font-semibold text-ink"><x-money :amount="$totalThisMonth" /></div>
        </div>

        <form method="GET" class="flex flex-wrap items-center gap-3 bg-surface-raised border border-border rounded-lg p-4">
            <label class="text-sm text-ink-muted">From
                <input type="date" name="from" value="{{ request('from') }}" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm ml-1">
            </label>
            <label class="text-sm text-ink-muted">To
                <input type="date" name="to" value="{{ request('to') }}" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm ml-1">
            </label>
            <button type="submit" class="px-4 py-2 bg-surface-hover text-ink-muted rounded-md text-sm hover:bg-zinc-700">Filter</button>
        </form>

        <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Category</th>
                        <th class="py-3 px-4">Description</th>
                        <th class="py-3 px-4">Paid Via</th>
                        <th class="py-3 px-4">Recorded By</th>
                        <th class="py-3 px-4 text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-4">{{ $expense->expense_date->format('Y-m-d') }}</td>
                            <td class="py-3 px-4">{{ $expense->category->name }}</td>
                            <td class="py-3 px-4 text-ink-muted">{{ $expense->description ?? '—' }}</td>
                            <td class="py-3 px-4">{{ ucfirst($expense->paid_via) }}</td>
                            <td class="py-3 px-4 text-ink-muted">{{ $expense->user->name }}</td>
                            <td class="py-3 px-4 text-right"><x-money :amount="$expense->amount" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-6 px-4 text-center text-ink-muted">No expenses recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $expenses->links() }}
    </div>
</x-app-layout>
