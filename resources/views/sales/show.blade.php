<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-ink leading-tight">Sale {{ $sale->invoice_number }}</h2>
            <div class="flex gap-3">
                <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="text-sm text-accent-400 hover:text-accent-300 hover:underline self-center">Print Receipt</a>
                @can('void sales')
                    @if ($sale->status === 'completed')
                        <a href="{{ route('sales.returns.create', $sale) }}" class="text-sm text-accent-400 hover:text-accent-300 hover:underline self-center">Return Items</a>
                        <form action="{{ route('sales.void', $sale) }}" method="POST" onsubmit="return confirm('Void this sale and restore stock?');">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm hover:bg-red-700">Void Sale</button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash-messages />

            <div class="bg-surface-raised border border-border rounded-lg p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><div class="text-ink-muted">Date</div><div class="font-medium">{{ $sale->sold_at?->format('Y-m-d H:i') }}</div></div>
                <div><div class="text-ink-muted">Customer</div><div class="font-medium">{{ $sale->customer?->name ?? 'Walk-in' }}</div></div>
                <div><div class="text-ink-muted">Cashier</div><div class="font-medium">{{ $sale->user->name }}</div></div>
                <div><div class="text-ink-muted">Status</div><div class="font-medium">{{ ucfirst($sale->status) }}</div></div>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Product</th>
                            <th class="py-3 px-4">Serial/IMEI</th>
                            <th class="py-3 px-4 text-right">Qty</th>
                            <th class="py-3 px-4 text-right">Unit Price</th>
                            <th class="py-3 px-4 text-right">Discount</th>
                            <th class="py-3 px-4 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sale->items as $item)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4">{{ $item->product->name }}</td>
                                <td class="py-3 px-4">{{ $item->productSerial?->imei_serial ?? '—' }}</td>
                                <td class="py-3 px-4 text-right">{{ $item->quantity }}</td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$item->unit_price" /></td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$item->discount_amount" /></td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$item->subtotal" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="bg-surface-raised border border-border rounded-lg p-6 text-sm space-y-1">
                    <div class="flex justify-between"><span>Subtotal</span><x-money :amount="$sale->subtotal" /></div>
                    <div class="flex justify-between"><span>Discount</span><x-money :amount="$sale->discount_amount" /></div>
                    <div class="flex justify-between"><span>Tax</span><x-money :amount="$sale->tax_amount" /></div>
                    <div class="flex justify-between font-semibold border-t pt-2"><span>Total</span><x-money :amount="$sale->total_amount" /></div>
                    <div class="flex justify-between"><span>Paid</span><x-money :amount="$sale->amount_paid" /></div>
                    <div class="flex justify-between"><span>Change</span><x-money :amount="$sale->change_due" /></div>
                    @if ($sale->refunded_amount > 0)
                        <div class="flex justify-between text-red-400"><span>Refunded</span><x-money :amount="$sale->refunded_amount" /></div>
                    @endif
                </div>

                <div class="bg-surface-raised border border-border rounded-lg p-6 text-sm">
                    <div class="font-medium text-ink mb-2">Payments</div>
                    @foreach ($sale->payments as $payment)
                        <div class="flex justify-between border-b border-border last:border-0 py-1">
                            <span>{{ ucfirst($payment->method) }} @if($payment->reference_no) ({{ $payment->reference_no }}) @endif</span>
                            <x-money :amount="$payment->amount" />
                        </div>
                    @endforeach
                </div>
            </div>

            @if ($sale->returns->isNotEmpty())
                <div class="bg-surface-raised border border-border rounded-lg p-6">
                    <div class="font-medium text-ink mb-2">Return History</div>
                    @foreach ($sale->returns as $return)
                        <div class="border-b border-border last:border-0 py-2 text-sm">
                            <div class="flex justify-between">
                                <span>{{ $return->created_at->format('Y-m-d H:i') }} — {{ $return->returnReason?->name }}{{ $return->reason ? ' ('.$return->reason.')' : '' }}</span>
                                <x-money :amount="$return->total_refunded" />
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
