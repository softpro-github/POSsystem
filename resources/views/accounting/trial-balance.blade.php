<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Accounting</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('accounting._tabs')
            @include('reports._date_filter')

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Total Debit</div><div class="text-xl font-semibold"><x-money :amount="$totalDebit" /></div></div>
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Total Credit</div><div class="text-xl font-semibold"><x-money :amount="$totalCredit" /></div></div>
                <div class="bg-surface-raised border border-border rounded-lg p-4">
                    <div class="text-xs text-ink-muted">Balanced</div>
                    <div class="text-xl font-semibold {{ round($totalDebit - $totalCredit, 2) === 0.0 ? 'text-emerald-400' : 'text-red-400' }}">
                        {{ round($totalDebit - $totalCredit, 2) === 0.0 ? 'Yes' : 'No' }}
                    </div>
                </div>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Code</th>
                            <th class="py-3 px-4">Account</th>
                            <th class="py-3 px-4">Type</th>
                            <th class="py-3 px-4 text-right">Debit</th>
                            <th class="py-3 px-4 text-right">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4">{{ $row['account']->code }}</td>
                                <td class="py-3 px-4">{{ $row['account']->name }}</td>
                                <td class="py-3 px-4">{{ ucfirst($row['account']->type) }}</td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$row['debit']" /></td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$row['credit']" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 px-4 text-center text-ink-muted">No journal activity in this period.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($rows->isNotEmpty())
                        <tfoot>
                            <tr class="border-t border-border font-semibold">
                                <td class="py-3 px-4" colspan="3">Total</td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$totalDebit" /></td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$totalCredit" /></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
