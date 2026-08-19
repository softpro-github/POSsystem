<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">New Purchase Order</h2>
    </x-slot>

    <script>
        function poForm(products) {
            return {
                products,
                items: [],
                addItem() {
                    this.items.push({ product_id: '', quantity_ordered: 1, unit_cost: 0 });
                },
                removeItem(index) { this.items.splice(index, 1); },
                onProductChange(item) {
                    const product = this.products.find(p => p.id == item.product_id);
                    if (product) item.unit_cost = product.cost_price;
                },
                get total() {
                    return this.items.reduce((sum, i) => sum + (Number(i.quantity_ordered) || 0) * (Number(i.unit_cost) || 0), 0);
                },
                money(v) { return Number(v || 0).toFixed(2); },
                submit() {
                    document.getElementById('items_json').value = JSON.stringify(this.items);
                    return this.items.length > 0;
                },
            };
        }
    </script>

    <div class="max-w-3xl mx-auto">
        <div class="bg-surface-raised border border-border rounded-lg p-6" x-data='poForm(@json($products))' x-init="addItem()">
            <form method="POST" action="{{ route('purchase-orders.store') }}" @submit="if (!submit()) $event.preventDefault()" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="supplier_id" value="Supplier" />
                        <select id="supplier_id" name="supplier_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>
                            <option value="">Select supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('supplier_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="order_date" value="Order Date" />
                        <x-text-input id="order_date" name="order_date" type="date" class="mt-1 block w-full" value="{{ old('order_date', now()->format('Y-m-d')) }}" required />
                        <x-input-error :messages="$errors->get('order_date')" class="mt-2" />
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
                                <select x-model="item.product_id" @change="onProductChange(item)" class="flex-1 text-sm bg-surface-hover border-border-strong text-ink rounded-md">
                                    <option value="">Select product</option>
                                    <template x-for="p in products" :key="p.id">
                                        <option :value="p.id" x-text="p.name + ' (' + p.sku + ')'"></option>
                                    </template>
                                </select>
                                <input type="number" min="1" x-model.number="item.quantity_ordered" placeholder="Qty" class="w-24 text-sm bg-surface-hover border-border-strong text-ink rounded-md">
                                <input type="number" min="0" step="0.01" x-model.number="item.unit_cost" placeholder="Unit Cost" class="w-28 text-sm bg-surface-hover border-border-strong text-ink rounded-md">
                                <button type="button" @click="removeItem(index)" class="text-red-400 text-sm">&times;</button>
                            </div>
                        </template>
                    </div>
                    <x-input-error :messages="$errors->get('items')" class="mt-2" />
                </div>

                <div class="text-right font-semibold text-ink">
                    Total: ₦<span x-text="money(total)"></span>
                </div>

                <input type="hidden" name="items_json" id="items_json">

                <div class="flex items-center gap-3">
                    <x-primary-button>Create Purchase Order</x-primary-button>
                    <a href="{{ route('purchase-orders.index') }}" class="text-sm text-ink-muted hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
