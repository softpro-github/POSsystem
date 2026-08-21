<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">
                Shift #{{ $shift->id }} — {{ $shift->user->name }}
            </h2>
            <div class="flex flex-wrap items-center gap-4">
                @if ($shift->status === 'closed')
                    <button type="button" onclick="window.print()" class="text-sm text-accent-400 hover:underline">Print Z-Report</button>
                @endif
                @if ($shift->status === 'open' && auth()->id() === $shift->user_id)
                    <a href="{{ route('shifts.current') }}" class="text-sm text-accent-400 hover:underline">Close shift</a>
                @endif
            </div>
        </div>
    </x-slot>

    @php
        $cashSales = $shift->cashSalesTotal();
        $cashRefunds = $shift->cashRefundsTotal();
        $cashPayouts = $shift->cashSupplierPaymentsTotal();
        $summary = $shift->salesSummary();
        $paymentsByMethod = $shift->paymentsByMethod();
    @endphp

    <div class="max-w-3xl mx-auto space-y-6">
        <div class="bg-surface-raised border border-border rounded-lg p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div><div class="text-ink-muted">Cashier</div><div class="font-medium">{{ $shift->user->name }}</div></div>
            <div><div class="text-ink-muted">Store / Terminal</div><div class="font-medium">{{ $shift->store?->name ?? '—' }}{{ $shift->terminal ? ' — '.$shift->terminal->name : '' }}</div></div>
            <div><div class="text-ink-muted">Opened</div><div class="font-medium">{{ $shift->opened_at->format('Y-m-d H:i') }}</div></div>
            <div><div class="text-ink-muted">Closed</div><div class="font-medium">{{ $shift->closed_at?->format('Y-m-d H:i') ?? '—' }}</div></div>
            <div>
                <div class="text-ink-muted">Status</div>
                <div class="font-medium">{{ $shift->status === 'open' ? 'X-Report (live)' : 'Z-Report (closed)' }}</div>
            </div>
        </div>

        <div class="bg-surface-raised border border-border rounded-lg p-6">
            <h3 class="font-semibold text-ink mb-4">Sales Summary</h3>
            <div class="text-sm space-y-1">
                <div class="flex justify-between"><span>Sales count</span><span>{{ $summary['count'] }}</span></div>
                <div class="flex justify-between"><span>Sales total</span><x-money :amount="$summary['total']" /></div>
                <div class="flex justify-between"><span>Tax</span><x-money :amount="$summary['tax']" /></div>
                <div class="flex justify-between"><span>Discounts</span><x-money :amount="$summary['discounts']" /></div>
                <div class="flex justify-between"><span>Refunds count</span><span>{{ $summary['refunds_count'] }}</span></div>
                <div class="flex justify-between"><span>Refunds total</span><x-money :amount="$summary['refunds_total']" /></div>
            </div>
        </div>

        <div class="bg-surface-raised border border-border rounded-lg p-6">
            <h3 class="font-semibold text-ink mb-4">Payments Received</h3>
            <div class="text-sm space-y-1">
                @foreach ($paymentsByMethod as $method => $amount)
                    <div class="flex justify-between"><span>{{ ucfirst($method) }}</span><x-money :amount="$amount" /></div>
                @endforeach
            </div>
        </div>

        <div class="bg-surface-raised border border-border rounded-lg p-6">
            <h3 class="font-semibold text-ink mb-4">Cash Drawer</h3>
            <div class="text-sm space-y-1">
                <div class="flex justify-between"><span>Opening Float</span><x-money :amount="$shift->opening_float" /></div>
                <div class="flex justify-between"><span>+ Cash Sales</span><x-money :amount="$cashSales" /></div>
                <div class="flex justify-between"><span>− Cash Refunds</span><x-money :amount="$cashRefunds" /></div>
                <div class="flex justify-between"><span>− Supplier Payments (cash)</span><x-money :amount="$cashPayouts" /></div>
                <div class="flex justify-between font-semibold border-t border-border pt-2"><span>Expected Cash</span><x-money :amount="$shift->expected_cash ?? $shift->computedExpectedCash()" /></div>
                @if ($shift->status === 'closed')
                    <div class="flex justify-between"><span>Counted</span><x-money :amount="$shift->closing_count" /></div>
                    <div class="flex justify-between font-semibold {{ $shift->variance != 0 ? 'text-amber-400' : 'text-emerald-400' }}"><span>Variance</span><x-money :amount="$shift->variance" /></div>
                    @if ($shift->cashMismatchReason)
                        <div class="flex justify-between text-ink-muted"><span>Reason</span><span>{{ $shift->cashMismatchReason->name }}</span></div>
                    @endif
                    @if ($shift->notes)
                        <div class="text-ink-muted mt-2">Notes: {{ $shift->notes }}</div>
                    @endif
                @endif
            </div>
        </div>

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Invoice</th>
                        <th class="py-3 px-4">Time</th>
                        <th class="py-3 px-4">Payments</th>
                        <th class="py-3 px-4 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shift->sales as $sale)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-4"><a href="{{ route('sales.show', $sale) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $sale->invoice_number }}</a></td>
                            <td class="py-3 px-4">{{ $sale->sold_at?->format('H:i') }}</td>
                            <td class="py-3 px-4">{{ $sale->payments->pluck('method')->map(fn ($m) => ucfirst($m))->unique()->implode(', ') }}</td>
                            <td class="py-3 px-4 text-right"><x-money :amount="$sale->total_amount" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="py-6 px-4 text-center text-ink-muted">No sales during this shift.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
