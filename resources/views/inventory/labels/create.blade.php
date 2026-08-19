<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Print Labels</h2>
    </x-slot>

    <script>
        function labelPicker(products) {
            return {
                products,
                search: '',
                items: [],

                get filteredProducts() {
                    if (!this.search) return this.products.slice(0, 24);
                    const term = this.search.toLowerCase();
                    return this.products.filter(p =>
                        p.name.toLowerCase().includes(term) ||
                        p.sku.toLowerCase().includes(term) ||
                        (p.barcode && p.barcode.toLowerCase().includes(term))
                    ).slice(0, 24);
                },

                addProduct(product) {
                    const existing = this.items.find(i => i.product_id === product.id);
                    if (existing) {
                        existing.quantity++;
                    } else {
                        this.items.push({ product_id: product.id, name: product.name, quantity: 1 });
                    }
                },

                removeItem(index) { this.items.splice(index, 1); },

                async loadFromPurchaseOrder(poId) {
                    if (!poId) return;
                    const res = await fetch(`/inventory/labels/from-purchase-order/${poId}`, { headers: { 'Accept': 'application/json' } });
                    const poItems = await res.json();
                    poItems.forEach(poItem => {
                        const existing = this.items.find(i => i.product_id === poItem.product_id);
                        if (existing) {
                            existing.quantity += poItem.quantity;
                        } else {
                            this.items.push({ product_id: poItem.product_id, name: poItem.name, quantity: poItem.quantity });
                        }
                    });
                },

                submit() {
                    return this.items.length > 0;
                },
            };
        }
    </script>

    <div x-data='labelPicker(@json($products))' class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-surface-raised border border-border rounded-lg p-4">
            <input type="text" x-model="search" placeholder="Search by name, SKU or barcode..."
                   class="w-full bg-surface-hover border-border-strong text-ink placeholder-ink-subtle rounded-md shadow-sm mb-4">

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[32rem] overflow-y-auto">
                <template x-for="product in filteredProducts" :key="product.id">
                    <button type="button" @click="addProduct(product)"
                            class="text-left border border-border rounded-lg p-3 hover:border-accent-500 hover:bg-surface-hover">
                        <div class="font-medium text-ink text-sm" x-text="product.name"></div>
                        <div class="text-xs text-ink-muted" x-text="product.sku"></div>
                    </button>
                </template>
            </div>
        </div>

        <div class="bg-surface-raised border border-border rounded-lg p-4 flex flex-col">
            <div class="mb-3">
                <label class="text-xs text-ink-muted">Pull quantities from a recent purchase order</label>
                <select @change="loadFromPurchaseOrder($event.target.value); $event.target.value = ''" class="w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm mt-1">
                    <option value="">Select a purchase order...</option>
                    @foreach ($recentPurchaseOrders as $po)
                        <option value="{{ $po->id }}">PO #{{ $po->id }} — {{ $po->received_date?->format('Y-m-d') }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 overflow-y-auto space-y-2 max-h-96">
                <template x-for="(item, index) in items" :key="index">
                    <div class="border border-border rounded-md p-2 text-sm flex items-center gap-2">
                        <div class="flex-1 text-ink" x-text="item.name"></div>
                        <input type="number" min="1" x-model.number="item.quantity" class="w-16 text-xs bg-surface-hover border-border-strong text-ink rounded">
                        <button type="button" @click="removeItem(index)" class="text-red-400 text-xs">Remove</button>
                    </div>
                </template>
                <p x-show="items.length === 0" class="text-sm text-ink-subtle text-center py-4">No items selected</p>
            </div>

            <form method="GET" action="{{ route('labels.print') }}" @submit="if (!submit()) $event.preventDefault()" class="mt-3" x-data>
                <template x-for="item in items" :key="item.product_id">
                    <div>
                        <input type="hidden" :name="'items[' + item.product_id + '][product_id]'" :value="item.product_id">
                        <input type="hidden" :name="'items[' + item.product_id + '][quantity]'" :value="item.quantity">
                    </div>
                </template>
                <button type="submit" class="w-full bg-accent-500 text-zinc-950 rounded-md py-2 font-medium hover:bg-accent-400">
                    Print Labels
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
