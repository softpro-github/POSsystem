<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Accounting</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('accounting._tabs')
            @include('reports._date_filter')

            <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
                <div class="px-4 py-3 border-b border-border font-semibold text-ink">Income</div>
                <table class="w-full text-sm text-left">
                    <tbody>
                        @forelse ($income as $row)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-2 px-4">{{ $row['account']->name }}</td>
                                <td class="py-2 px-4 text-right"><x-money :amount="$row['balance']" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 px-4 text-center text-ink-muted">No income recorded.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-border font-semibold">
                            <td class="py-2 px-4">Total Income</td>
                            <td class="py-2 px-4 text-right"><x-money :amount="$totalIncome" /></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
                <div class="px-4 py-3 border-b border-border font-semibold text-ink">Expenses</div>
                <table class="w-full text-sm text-left">
                    <tbody>
                        @forelse ($expense as $row)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-2 px-4">{{ $row['account']->name }}</td>
                                <td class="py-2 px-4 text-right"><x-money :amount="$row['balance']" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 px-4 text-center text-ink-muted">No expenses recorded.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-border font-semibold">
                            <td class="py-2 px-4">Total Expenses</td>
                            <td class="py-2 px-4 text-right"><x-money :amount="$totalExpense" /></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg p-4 flex justify-between items-center">
                <span class="font-semibold text-ink">Net Income</span>
                <span class="text-xl font-semibold {{ $netIncome >= 0 ? 'text-emerald-400' : 'text-red-400' }}"><x-money :amount="$netIncome" /></span>
            </div>
        </div>
    </div>
</x-app-layout>
