<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Purchase Order #{{ $purchaseOrder->id }}</h2>
            @can('manage purchase returns')
                @if ($purchaseOrder->status === 'received')
                    <a href="{{ route('purchase-orders.returns.create', $purchaseOrder) }}" class="text-sm text-accent-400 hover:text-accent-300 hover:underline">Return to Supplier</a>
                @endif
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash-messages />

            <div class="bg-surface-raised border border-border rounded-lg p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><div class="text-ink-muted">Supplier</div><div class="font-medium">{{ $purchaseOrder->supplier->name }}</div></div>
                <div><div class="text-ink-muted">Order Date</div><div class="font-medium">{{ $purchaseOrder->order_date->format('Y-m-d') }}</div></div>
                <div><div class="text-ink-muted">Status</div><div class="font-medium">{{ ucfirst($purchaseOrder->status) }}</div></div>
                <div><div class="text-ink-muted">Created By</div><div class="font-medium">{{ $purchaseOrder->user->name }}</div></div>
            </div>

            @if ($purchaseOrder->status === 'pending')
                <form action="{{ route('purchase-orders.receive', $purchaseOrder) }}" method="POST" class="bg-surface-raised border border-border rounded-lg p-6 space-y-4">
                    @csrf
                    <h3 class="font-semibold text-ink">Receive Stock</h3>
                    @error('items')
                        <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-md px-4 py-2">{{ $message }}</div>
                    @enderror

                    @foreach ($purchaseOrder->items as $item)
                        <div class="border rounded-md p-4">
                            <div class="flex justify-between items-center">
                                <div class="font-medium">{{ $item->product->name }}</div>
                                <div class="text-sm text-ink-muted">Ordered: {{ $item->quantity_ordered }}</div>
                            </div>
                            <div class="mt-2 flex items-center gap-3">
                                <label class="text-sm text-ink-muted">Quantity Received</label>
                                <input type="number" name="items[{{ $item->id }}][quantity_received]" min="0" max="{{ $item->quantity_ordered }}" value="{{ $item->quantity_ordered }}" class="w-24 text-sm bg-surface-hover border-border-strong text-ink rounded-md">
                            </div>
                            @if ($item->product->track_serial)
                                <div class="mt-2">
                                    <label class="text-sm text-ink-muted">Serial/IMEI numbers (one per line, must match quantity received)</label>
                                    <textarea name="items[{{ $item->id }}][serials]" rows="3" class="mt-1 w-full text-sm bg-surface-hover border-border-strong text-ink rounded-md" placeholder="e.g. 3549871234567891"></textarea>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <x-primary-button>Confirm Receipt</x-primary-button>
                </form>
            @endif

            <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Product</th>
                            <th class="py-3 px-4 text-right">Ordered</th>
                            <th class="py-3 px-4 text-right">Received</th>
                            <th class="py-3 px-4 text-right">Unit Cost</th>
                            <th class="py-3 px-4 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseOrder->items as $item)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4">{{ $item->product->name }}</td>
                                <td class="py-3 px-4 text-right">{{ $item->quantity_ordered }}</td>
                                <td class="py-3 px-4 text-right">{{ $item->quantity_received }}</td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$item->unit_cost" /></td>
                                <td class="py-3 px-4 text-right"><x-money :amount="$item->subtotal" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="py-3 px-4 text-right font-semibold">Total</td>
                            <td class="py-3 px-4 text-right font-semibold"><x-money :amount="$purchaseOrder->total_amount" /></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if ($purchaseOrder->returns->isNotEmpty())
                <div class="bg-surface-raised border border-border rounded-lg p-6">
                    <div class="font-medium text-ink mb-2">Returns to Supplier</div>
                    @foreach ($purchaseOrder->returns as $return)
                        <div class="border-b border-border last:border-0 py-2 text-sm">
                            <div class="flex justify-between">
                                <span>{{ $return->created_at->format('Y-m-d H:i') }} — {{ $return->returnReason?->name }}</span>
                                <x-money :amount="$return->total_amount" />
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
