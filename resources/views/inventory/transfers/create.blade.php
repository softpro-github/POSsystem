<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">New Stock Transfer</h2>
    </x-slot>

    <script>
        function transferForm(products) {
            return {
                products,
                items: [],
                addItem() {
                    this.items.push({ product_id: '', quantity: 1 });
                },
                removeItem(index) { this.items.splice(index, 1); },
                submit() {
                    document.getElementById('items_json').value = JSON.stringify(this.items);
                    return this.items.length > 0;
                },
            };
        }
    </script>

    <div class="max-w-3xl mx-auto">
        <div class="bg-surface-raised border border-border rounded-lg p-6" x-data='transferForm(@json($products))' x-init="addItem()">
            <form method="POST" action="{{ route('transfers.store') }}" @submit="if (!submit()) $event.preventDefault()" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="from_store_id" value="From Store" />
                        <select id="from_store_id" name="from_store_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>
                            <option value="">Select store</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" @selected(old('from_store_id') == $store->id)>{{ $store->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('from_store_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="to_store_id" value="To Store" />
                        <select id="to_store_id" name="to_store_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>
                            <option value="">Select store</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" @selected(old('to_store_id') == $store->id)>{{ $store->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('to_store_id')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="notes" value="Notes (optional)" />
                    <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm">{{ old('notes') }}</textarea>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <x-input-label value="Items" class="!mb-0" />
                        <button type="button" @click="addItem()" class="text-sm text-accent-400 hover:text-accent-300 hover:underline">+ Add line item</button>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex items-center gap-2">
                                <select x-model="item.product_id" class="flex-1 text-sm bg-surface-hover border-border-strong text-ink rounded-md">
                                    <option value="">Select product</option>
                                    <template x-for="p in products" :key="p.id">
                                        <option :value="p.id" x-text="p.sku ? p.name + ' (' + p.sku + ')' : p.name"></option>
                                    </template>
                                </select>
                                <input type="number" min="1" x-model.number="item.quantity" placeholder="Qty" class="w-24 text-sm bg-surface-hover border-border-strong text-ink rounded-md">
                                <button type="button" @click="removeItem(index)" class="text-red-400 text-sm">&times;</button>
                            </div>
                        </template>
                    </div>
                    <x-input-error :messages="$errors->get('items')" class="mt-2" />
                </div>

                <input type="hidden" name="items_json" id="items_json">

                <div class="flex items-center gap-3">
                    <x-primary-button>Create Transfer</x-primary-button>
                    <a href="{{ route('transfers.index') }}" class="text-sm text-ink-muted hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
