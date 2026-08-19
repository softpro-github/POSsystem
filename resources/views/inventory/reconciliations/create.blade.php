<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Stock Reconciliation</h2>
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-4">
        <x-flash-messages />
        <p class="text-sm text-ink-muted">Count what's physically on the shelf and enter it below — only products with a different count than the book quantity will be adjusted.</p>

        @error('counts')
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-md px-4 py-2">{{ $message }}</div>
        @enderror

        <form method="POST" action="{{ route('reconciliations.store') }}" class="space-y-4">
            @csrf

            <div class="bg-surface-raised border border-border rounded-lg p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="adjustment_reason_id" value="Reason" />
                    <select id="adjustment_reason_id" name="adjustment_reason_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>
                        <option value="">Select reason</option>
                        @foreach ($adjustmentReasons as $reason)
                            <option value="{{ $reason->id }}">{{ $reason->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('adjustment_reason_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="notes" value="Notes (optional)" />
                    <x-text-input id="notes" name="notes" type="text" class="mt-1 block w-full" />
                </div>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Product</th>
                            <th class="py-3 px-4 text-right">Book Qty</th>
                            <th class="py-3 px-4 text-right">Counted Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-2 px-4 text-ink">{{ $product->name }} <span class="text-ink-subtle text-xs">({{ $product->sku }})</span></td>
                                <td class="py-2 px-4 text-right text-ink-muted">{{ $product->quantity }}</td>
                                <td class="py-2 px-4 text-right">
                                    <input type="number" min="0" name="counts[{{ $product->id }}]" placeholder="{{ $product->quantity }}" class="w-24 text-sm bg-surface-hover border-border-strong text-ink rounded-md text-right">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex items-center gap-3">
                <x-primary-button>Complete Reconciliation</x-primary-button>
                <a href="{{ route('reconciliations.index') }}" class="text-sm text-ink-muted hover:underline">Cancel</a>
            </div>
        </form>
    </div>
</x-app-layout>
