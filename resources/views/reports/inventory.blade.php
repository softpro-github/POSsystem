<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Reports</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('reports._tabs')

            <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Products</div><div class="text-xl font-semibold">{{ $summary['total_products'] }}</div></div>
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Units in Stock</div><div class="text-xl font-semibold">{{ $summary['total_units'] }}</div></div>
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Cost Value</div><div class="text-xl font-semibold"><x-money :amount="$summary['total_cost_value']" /></div></div>
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Retail Value</div><div class="text-xl font-semibold"><x-money :amount="$summary['total_retail_value']" /></div></div>
                <div class="bg-surface-raised border border-border rounded-lg p-4"><div class="text-xs text-ink-muted">Low Stock</div><div class="text-xl font-semibold text-red-400">{{ $summary['low_stock_count'] }}</div></div>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Product</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4 text-right">Qty</th>
                            <th class="py-3 px-4 text-right">Cost Value</th>
                            <th class="py-3 px-4 text-right">Retail Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4">{{ $product->name }}</td>
                                <td class="py-3 px-4">{{ $product->category?->name ?? '—' }}</td>
                                <td class="py-3 px-4 text-right {{ $product->quantity <= $product->reorder_level ? 'text-red-400 font-semibold' : '' }}">{{ $product->quantity }}</td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$product->quantity * $product->cost_price" /></td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$product->quantity * $product->selling_price" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
