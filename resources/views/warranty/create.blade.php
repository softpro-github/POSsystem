<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Register Warranty</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-surface-raised border border-border rounded-lg p-6">
                <form method="POST" action="{{ route('warranties.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="sale_item_id" value="Sale Item" />
                        <select id="sale_item_id" name="sale_item_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>
                            <option value="">Select a sold item</option>
                            @foreach ($eligibleItems as $item)
                                <option value="{{ $item->id }}" @selected(old('sale_item_id') == $item->id)>
                                    {{ $item->sale->invoice_number }} — {{ $item->product->name }} — {{ $item->sale->customer->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('sale_item_id')" class="mt-2" />
                        <p class="text-xs text-ink-muted mt-1">Only completed sales with a registered customer and no existing warranty are listed.</p>
                    </div>

                    <div>
                        <x-input-label for="warranty_period_months" value="Warranty Period (months)" />
                        <x-text-input id="warranty_period_months" name="warranty_period_months" type="number" min="1" max="120" class="mt-1 block w-full" value="{{ old('warranty_period_months', 12) }}" required />
                        <x-input-error :messages="$errors->get('warranty_period_months')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button>Register Warranty</x-primary-button>
                        <a href="{{ route('warranties.index') }}" class="text-sm text-ink-muted hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
