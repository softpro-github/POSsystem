<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Reports</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('reports._tabs')
            @include('reports._date_filter')

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Revenue</div><div class="text-xl font-semibold"><x-money :amount="$summary['revenue']" /></div></div>
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Cost of Goods Sold</div><div class="text-xl font-semibold"><x-money :amount="$summary['cost']" /></div></div>
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Gross Profit</div><div class="text-xl font-semibold text-emerald-400"><x-money :amount="$summary['profit']" /></div></div>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Product</th>
                            <th class="py-3 px-4 text-right">Qty Sold</th>
                            <th class="py-3 px-4 text-right">Revenue</th>
                            <th class="py-3 px-4 text-right">Cost</th>
                            <th class="py-3 px-4 text-right">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4">{{ $row['product']->name }}</td>
                                <td class="py-3 px-4 text-right">{{ $row['quantity'] }}</td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$row['revenue']" /></td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$row['cost']" /></td>
                                <td class="py-3 px-4 text-right font-medium"><x-money :amount="$row['profit']" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 px-4 text-center text-ink-muted">No sales in this period.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
