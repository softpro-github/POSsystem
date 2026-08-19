<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Return Items — {{ $sale->invoice_number }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-surface-raised border border-border rounded-lg p-6">
                @error('items')
                    <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-md px-4 py-2 mb-4">{{ $message }}</div>
                @enderror

                <form action="{{ route('sales.returns.store', $sale) }}" method="POST" class="space-y-4">
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
                        <x-input-label for="reason" value="Additional Notes (optional)" />
                        <textarea id="reason" name="reason" rows="2" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm">{{ old('reason') }}</textarea>
                        <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                    </div>

                    <table class="w-full text-sm text-left">
                        <thead class="text-ink-muted border-b">
                            <tr>
                                <th class="py-2">Product</th>
                                <th class="py-2">Serial/IMEI</th>
                                <th class="py-2 text-right">Sold Qty</th>
                                <th class="py-2 text-right">Already Returned</th>
                                <th class="py-2 text-right">Return Qty</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sale->items as $item)
                                <tr class="border-b border-border last:border-0">
                                    <td class="py-2">{{ $item->product->name }}</td>
                                    <td class="py-2">{{ $item->productSerial?->imei_serial ?? '—' }}</td>
                                    <td class="py-2 text-right">{{ $item->quantity }}</td>
                                    <td class="py-2 text-right">{{ $item->returned_quantity }}</td>
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
                        <a href="{{ route('sales.show', $sale) }}" class="text-sm text-ink-muted hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
