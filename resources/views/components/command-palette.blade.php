<x-modal name="command-palette" maxWidth="lg" focusable>
    <div x-data="{
            query: '',
            results: { products: [], customers: [], suppliers: [], sales: [] },
            activeIndex: 0,
            loading: false,
            get groups() {
                return [
                    { key: 'products', label: 'Products', items: this.results.products },
                    { key: 'customers', label: 'Customers', items: this.results.customers },
                    { key: 'suppliers', label: 'Suppliers', items: this.results.suppliers },
                    { key: 'sales', label: 'Sales', items: this.results.sales },
                ].filter((g) => g.items.length > 0);
            },
            get flat() {
                return this.groups.flatMap((g) => g.items);
            },
            async search() {
                this.activeIndex = 0;
                if (this.query.trim().length < 2) {
                    this.results = { products: [], customers: [], suppliers: [], sales: [] };
                    return;
                }
                this.loading = true;
                try {
                    const res = await fetch('{{ route('search.index') }}?q=' + encodeURIComponent(this.query), { headers: { 'Accept': 'application/json' } });
                    this.results = await res.json();
                } finally {
                    this.loading = false;
                }
            },
            go(index) {
                const item = this.flat[index];
                if (item) window.location = item.url;
            },
            move(delta) {
                if (this.flat.length === 0) return;
                this.activeIndex = (this.activeIndex + delta + this.flat.length) % this.flat.length;
            },
        }"
        @open-modal.window="if ($event.detail === 'command-palette') { query = ''; results = { products: [], customers: [], suppliers: [], sales: [] }; activeIndex = 0; }"
    >
        <div class="border-b border-border">
            <input
                id="command-palette-input"
                type="text"
                x-model="query"
                x-on:input.debounce.250ms="search()"
                x-on:keydown.down.prevent="move(1)"
                x-on:keydown.up.prevent="move(-1)"
                x-on:keydown.enter.prevent="go(activeIndex)"
                placeholder="{{ __('nav.search_placeholder') }}"
                autocomplete="off"
                class="w-full h-14 px-4 bg-transparent border-0 text-ink placeholder-ink-subtle text-base focus:ring-0"
            >
        </div>

        <div class="max-h-96 overflow-y-auto py-2">
            <template x-for="(group, gIndex) in groups" :key="group.key">
                <div class="mb-1">
                    <p class="px-4 py-1 text-xs font-semibold text-ink-subtle uppercase tracking-wider" x-text="group.label"></p>
                    <template x-for="(item, i) in group.items" :key="item.id">
                        <a :href="item.url"
                           class="flex items-center justify-between gap-3 px-4 py-2 text-sm"
                           :class="flat.indexOf(item) === activeIndex ? 'bg-surface-hover text-ink' : 'text-ink-muted'"
                           x-on:mouseenter="activeIndex = flat.indexOf(item)">
                            <span class="truncate" x-text="item.title"></span>
                            <span class="text-xs text-ink-subtle shrink-0" x-text="item.subtitle"></span>
                        </a>
                    </template>
                </div>
            </template>

            <p x-show="!loading && query.trim().length >= 2 && flat.length === 0" class="px-4 py-6 text-sm text-ink-subtle text-center">
                No results for "<span x-text="query"></span>".
            </p>
            <p x-show="query.trim().length < 2" class="px-4 py-6 text-sm text-ink-subtle text-center">
                Type at least 2 characters to search products, customers, suppliers and sales.
            </p>
        </div>
    </div>
</x-modal>
