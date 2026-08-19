<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <script type="application/json" id="dashboard-currency">@json(\App\Models\Setting::get('currency_symbol', '₦'))</script>
    <script type="application/json" id="sales-trend-data">@json($salesTrend)</script>
    <script type="application/json" id="category-mix-data">@json($categoryMix)</script>
    <script type="application/json" id="comparison-data">@json($comparison)</script>
    <script type="application/json" id="kpi-transactions-data">@json($kpiSparklines['transactions'])</script>
    <script type="application/json" id="kpi-customers-data">@json($kpiSparklines['new_customers'])</script>
    <script type="application/json" id="kpi-refunds-data">@json($kpiSparklines['refunds'])</script>

    <div class="space-y-6">

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-surface-raised border border-border rounded-lg p-4">
                <div class="text-xs text-ink-muted">Transactions (7d)</div>
                <div class="text-xl font-semibold text-ink mt-1">{{ collect($kpiSparklines['transactions'])->sum('value') }}</div>
                <div class="h-12 mt-1"><canvas id="kpi-transactions-chart"></canvas></div>
            </div>
            <div class="bg-surface-raised border border-border rounded-lg p-4">
                <div class="text-xs text-ink-muted">New Customers (7d)</div>
                <div class="text-xl font-semibold text-ink mt-1">{{ collect($kpiSparklines['new_customers'])->sum('value') }}</div>
                <div class="h-12 mt-1"><canvas id="kpi-customers-chart"></canvas></div>
            </div>
            <div class="bg-surface-raised border border-border rounded-lg p-4">
                <div class="text-xs text-ink-muted">Refunded (7d)</div>
                <div class="text-xl font-semibold text-ink mt-1"><x-money :amount="collect($kpiSparklines['refunds'])->sum('value')" /></div>
                <div class="h-12 mt-1"><canvas id="kpi-refunds-chart"></canvas></div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-surface-raised border border-border overflow-hidden rounded-lg p-6">
                <div class="text-sm text-ink-muted">Today's Sales</div>
                <div class="text-2xl font-semibold text-ink mt-1"><x-money :amount="$stats['todays_sales_total']" /></div>
            </div>
            <div class="bg-surface-raised border border-border overflow-hidden rounded-lg p-6">
                <div class="text-sm text-ink-muted">Transactions Today</div>
                <div class="text-2xl font-semibold text-ink mt-1">{{ $stats['todays_sales_count'] }}</div>
            </div>
            <div class="bg-surface-raised border border-border overflow-hidden rounded-lg p-6">
                <div class="text-sm text-ink-muted">Active Products</div>
                <div class="text-2xl font-semibold text-ink mt-1">{{ $stats['total_products'] }}</div>
            </div>
            <div class="bg-surface-raised border overflow-hidden rounded-lg p-6 {{ $stats['low_stock_count'] > 0 ? 'border-red-500/30' : 'border-border' }}">
                <div class="text-sm text-ink-muted">Low Stock Items</div>
                <div class="text-2xl font-semibold {{ $stats['low_stock_count'] > 0 ? 'text-red-400' : 'text-ink' }} mt-1">{{ $stats['low_stock_count'] }}</div>
            </div>
        </div>

        <div class="bg-surface-raised border border-border rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-ink">Sales Trend</h3>
                <div class="flex gap-1">
                    @foreach ([7 => '7d', 14 => '14d', 30 => '30d'] as $days => $label)
                        <a href="{{ route('dashboard', ['period' => $days]) }}" @class([
                            'px-3 py-1 rounded-md text-xs',
                            'bg-accent-500 text-zinc-950' => $periodDays === $days,
                            'bg-surface-hover text-ink-muted hover:bg-zinc-700' => $periodDays !== $days,
                        ])>{{ $label }}</a>
                    @endforeach
                </div>
            </div>
            <div class="h-64"><canvas id="sales-trend-chart"></canvas></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-surface-raised border border-border rounded-lg p-6">
                <h3 class="font-semibold text-ink mb-4">Category Mix <span class="text-ink-subtle font-normal text-sm">({{ $periodDays }}d)</span></h3>
                @if ($categoryMix->isEmpty())
                    <p class="text-sm text-ink-muted">No sales in this period.</p>
                @else
                    <div class="h-64"><canvas id="category-mix-chart"></canvas></div>
                @endif
            </div>

            <div class="bg-surface-raised border border-border rounded-lg p-6">
                <h3 class="font-semibold text-ink mb-4">This Period vs Previous</h3>
                <div class="grid grid-cols-3 gap-3 mb-4 text-sm">
                    <div>
                        <div class="text-ink-subtle text-xs">Net Sales</div>
                        <div class="text-ink font-semibold"><x-money :amount="$comparison['this_totals']['net_sales']" /></div>
                        <div class="text-xs {{ $comparison['this_totals']['net_sales'] >= $comparison['previous_totals']['net_sales'] ? 'text-emerald-400' : 'text-red-400' }}">
                            vs <x-money :amount="$comparison['previous_totals']['net_sales']" />
                        </div>
                    </div>
                    <div>
                        <div class="text-ink-subtle text-xs">Gross Margin</div>
                        <div class="text-ink font-semibold">{{ $comparison['this_totals']['margin'] }}%</div>
                        <div class="text-xs text-ink-muted">vs {{ $comparison['previous_totals']['margin'] }}%</div>
                    </div>
                    <div>
                        <div class="text-ink-subtle text-xs">Avg Basket</div>
                        <div class="text-ink font-semibold"><x-money :amount="$comparison['this_totals']['basket']" /></div>
                        <div class="text-xs text-ink-muted">vs <x-money :amount="$comparison['previous_totals']['basket']" /></div>
                    </div>
                </div>
                <div class="h-48"><canvas id="comparison-chart"></canvas></div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-surface-raised border border-border overflow-hidden rounded-lg">
                <div class="p-6">
                    <h3 class="font-semibold text-ink mb-4">Recent Transactions</h3>
                    @if ($recentSales->isEmpty())
                        <p class="text-sm text-ink-muted">No sales yet.</p>
                    @else
                        <table class="w-full text-sm text-left">
                            <thead class="text-ink-muted border-b border-border">
                                <tr>
                                    <th class="py-2">Invoice</th>
                                    <th class="py-2">Customer</th>
                                    <th class="py-2">Cashier</th>
                                    <th class="py-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentSales as $sale)
                                    <tr class="border-b border-border last:border-0">
                                        <td class="py-2">
                                            <a href="{{ route('sales.show', $sale) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $sale->invoice_number }}</a>
                                        </td>
                                        <td class="py-2 text-ink">{{ $sale->customer?->name ?? 'Walk-in' }}</td>
                                        <td class="py-2 text-ink">{{ $sale->user->name }}</td>
                                        <td class="py-2 text-right text-ink"><x-money :amount="$sale->total_amount" /></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <div class="bg-surface-raised border border-border overflow-hidden rounded-lg">
                <div class="p-6">
                    <h3 class="font-semibold text-ink mb-4">Low Stock</h3>
                    @if ($lowStockProducts->isEmpty())
                        <p class="text-sm text-ink-muted">All products are sufficiently stocked.</p>
                    @else
                        <table class="w-full text-sm text-left">
                            <thead class="text-ink-muted border-b border-border">
                                <tr>
                                    <th class="py-2">Product</th>
                                    <th class="py-2 text-right">Qty</th>
                                    <th class="py-2 text-right">Reorder Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lowStockProducts as $product)
                                    <tr class="border-b border-border last:border-0">
                                        <td class="py-2 text-ink">{{ $product->name }}</td>
                                        <td class="py-2 text-right text-red-400 font-medium">{{ $product->quantity }}</td>
                                        <td class="py-2 text-right text-ink">{{ $product->reorder_level }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="inline-block mt-4 text-sm text-accent-400 hover:text-accent-300 hover:underline">View all low stock products &rarr;</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @vite('resources/js/dashboard-charts.js')
</x-app-layout>
