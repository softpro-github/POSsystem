<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">{{ $supplier->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash-messages />

            <div class="bg-surface-raised border border-border rounded-lg p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><div class="text-ink-muted">Contact Person</div><div class="font-medium">{{ $supplier->contact_person ?? '—' }}</div></div>
                <div><div class="text-ink-muted">Phone</div><div class="font-medium">{{ $supplier->phone ?? '—' }}</div></div>
                <div><div class="text-ink-muted">Email</div><div class="font-medium">{{ $supplier->email ?? '—' }}</div></div>
                <div><div class="text-ink-muted">Balance Owed</div><div class="font-medium {{ $supplier->balance > 0 ? 'text-amber-400' : '' }}"><x-money :amount="$supplier->balance" /></div></div>
            </div>

            @if ($supplier->balance > 0)
                <div class="bg-surface-raised border border-border rounded-lg p-6">
                    <h3 class="font-semibold text-ink mb-4">Record Payment</h3>
                    <form action="{{ route('suppliers.payments.store', $supplier) }}" method="POST" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <div>
                            <x-input-label for="amount" value="Amount" />
                            <input type="number" step="0.01" min="0.01" max="{{ $supplier->balance }}" name="amount" id="amount" value="{{ old('amount', $supplier->balance) }}" class="mt-1 w-32 text-sm bg-surface-hover border-border-strong text-ink rounded-md" required>
                        </div>
                        <div>
                            <x-input-label for="method" value="Method" />
                            <select name="method" id="method" class="mt-1 text-sm bg-surface-hover border-border-strong text-ink rounded-md">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="transfer">Transfer</option>
                            </select>
                        </div>
                        <div class="flex-1 min-w-[150px]">
                            <x-input-label for="note" value="Note (optional)" />
                            <input type="text" name="note" id="note" class="mt-1 w-full text-sm bg-surface-hover border-border-strong text-ink rounded-md">
                        </div>
                        <x-primary-button>Record Payment</x-primary-button>
                    </form>
                    <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                </div>
            @endif

            @if ($payments->isNotEmpty())
                <div class="bg-surface-raised border border-border rounded-lg p-6">
                    <h3 class="font-semibold text-ink mb-4">Payment History</h3>
                    <table class="w-full text-sm text-left">
                        <thead class="text-ink-muted border-b border-border">
                            <tr><th class="py-2">Date</th><th class="py-2">Method</th><th class="py-2">Note</th><th class="py-2 text-right">Amount</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr class="border-b border-border last:border-0">
                                    <td class="py-2">{{ $payment->paid_at->format('Y-m-d H:i') }}</td>
                                    <td class="py-2">{{ ucfirst($payment->method) }}</td>
                                    <td class="py-2">{{ $payment->note ?? '—' }}</td>
                                    <td class="py-2 text-right"><x-money :amount="$payment->amount" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="bg-surface-raised border border-border rounded-lg p-6">
                <h3 class="font-semibold text-ink mb-4">Purchase Orders</h3>
                @if ($purchaseOrders->isEmpty())
                    <p class="text-sm text-ink-muted">No purchase orders yet.</p>
                @else
                    <table class="w-full text-sm text-left">
                        <thead class="text-ink-muted border-b">
                            <tr><th class="py-2">Date</th><th class="py-2">Status</th><th class="py-2 text-right">Total</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($purchaseOrders as $po)
                                <tr class="border-b border-border last:border-0">
                                    <td class="py-2"><a href="{{ route('purchase-orders.show', $po) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $po->order_date->format('Y-m-d') }}</a></td>
                                    <td class="py-2">{{ ucfirst($po->status) }}</td>
                                    <td class="py-2 text-right"><x-money :amount="$po->total_amount" /></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $purchaseOrders->links() }}
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
