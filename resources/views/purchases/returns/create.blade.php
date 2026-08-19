<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Return to Supplier — PO #{{ $purchaseOrder->id }}</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <div class="bg-surface-raised border border-border rounded-lg p-6">
            @error('items')
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-md px-4 py-2 mb-4">{{ $message }}</div>
            @enderror

            <form action="{{ route('purchase-orders.returns.store', $purchaseOrder) }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <x-input-label for="return_reason_id" value="Reason for Return" />
                    <select id="return_reason_id" name="return_reason_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>
                        <option value="">Select a reason</option>
                        @foreach ($returnReasons as $reasonOption)
                            <option value="{{ $reasonOption->id }}" @selected(old('return_reason_id') == $reasonOption->id)>{{ $reasonOption->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('return_reason_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="notes" value="Additional Notes (optional)" />
                    <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm">{{ old('notes') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>

                <table class="w-full text-sm text-left">
                    <thead class="text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-2">Product</th>
                            <th class="py-2 text-right">Received</th>
                            <th class="py-2 text-right">Already Returned</th>
                            <th class="py-2 text-right">Return Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchaseOrder->items as $item)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-2">{{ $item->product->name }}</td>
                                <td class="py-2 text-right">{{ $item->quantity_received }}</td>
                                <td class="py-2 text-right">{{ $item->quantity_returned }}</td>
                                <td class="py-2 text-right">
                                    @if ($item->returnableQuantity() > 0)
                                        <input type="number" name="items[{{ $item->id }}][quantity]" min="0" max="{{ $item->returnableQuantity() }}" value="0" class="w-20 text-sm bg-surface-hover border-border-strong text-ink rounded-md text-right">
                                    @else
                                        <span class="text-ink-subtle">Fully returned</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="flex items-center gap-3">
                    <x-primary-button>Process Return</x-primary-button>
                    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="text-sm text-ink-muted hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
