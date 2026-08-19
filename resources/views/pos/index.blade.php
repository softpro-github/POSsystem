<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-ink leading-tight">Point of Sale</h2>
            <div class="flex items-center gap-4">
                <a href="{{ route('pos.held') }}" class="text-sm text-accent-400 hover:text-accent-300 hover:underline">Held Orders ({{ $heldCount }})</a>
            </div>
        </div>
    </x-slot>

    @php
        $resumeData = $resumeSale ? [
            'id' => $resumeSale->id,
            'customer_id' => $resumeSale->customer_id,
            'discount_amount' => (float) $resumeSale->discount_amount,
            'tax_amount' => (float) $resumeSale->tax_amount,
            'items' => $resumeSale->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'product_serial_id' => $i->product_serial_id,
                'quantity' => $i->quantity,
                'unit_price' => (float) $i->unit_price,
                'discount_amount' => (float) $i->discount_amount,
            ])->values(),
        ] : null;
    @endphp

    <script>
        function posCart(products, categories, quickPicks, customers, resumeData, discountRules, bankDetails, syncUrl) {
            return {
                products, categories, quickPicks, customers, discountRules, bankDetails, syncUrl,
                search: '', error: '', appliedRuleId: '', categoryFilter: 'All', barcodeInput: '',
                layout: localStorage.getItem('gadgetstore-pos-layout') || 'beam',
                isOffline: !navigator.onLine, queuedCount: 0, offlineReceipt: null, qrDataUrl: null, saleComplete: null,
                resumeSaleId: resumeData ? resumeData.id : null,
                customerId: resumeData ? (resumeData.customer_id ?? '') : '',
                discountAmount: resumeData ? resumeData.discount_amount : 0,
                payments: [{ method: 'cash', amount: 0, reference_no: '', autoAmount: true }],
                cart: resumeData ? resumeData.items.map(i => {
                    const product = products.find(p => p.id === i.product_id);
                    if (!product) return null;
                    return product.track_serial ? {
                        product_id: i.product_id, name: product.name, unit_price: i.unit_price,
                        quantity: 1, discount_amount: i.discount_amount, track_serial: true,
                        serials: product.serials, product_serial_id: i.product_serial_id,
                        tax_rate: product.tax_rate, category_id: product.category_id,
                    } : {
                        product_id: i.product_id, name: product.name, unit_price: i.unit_price,
                        quantity: i.quantity, discount_amount: i.discount_amount, track_serial: false,
                        max_quantity: product.quantity,
                        tax_rate: product.tax_rate, category_id: product.category_id,
                    };
                }).filter(Boolean) : [],

                async init() {
                    this.refreshQueuedCount();
                    this.trySyncQueue();
                    window.addEventListener('online', () => { this.isOffline = false; this.trySyncQueue(); });
                    window.addEventListener('offline', () => { this.isOffline = true; });
                    setInterval(() => this.trySyncQueue(), 20000);

                    // Keep the payment amount pre-filled with the total due so the
                    // cashier isn't forced to retype it — only while there's a single
                    // payment row that the cashier hasn't manually edited.
                    this.$watch('total', (value) => {
                        if (this.payments.length === 1 && this.payments[0].autoAmount !== false) {
                            this.payments[0].amount = value;
                        }
                    });

                    window.addEventListener('keydown', (e) => {
                        if (e.key === 'F2') { e.preventDefault(); document.getElementById('pos-customer-select')?.focus(); }
                        else if (e.key === 'F3') { e.preventDefault(); document.getElementById('pos-discount-input')?.focus(); }
                        else if (e.key === 'F4') { e.preventDefault(); document.getElementById('pos-hold-submit')?.click(); }
                        else if (e.key === 'F8') { e.preventDefault(); document.getElementById('pos-barcode-input')?.focus(); }
                        else if (e.key === 'F9') { e.preventDefault(); this.completeSale(); }
                    });
                },

                setLayout(mode) {
                    this.layout = mode;
                    localStorage.setItem('gadgetstore-pos-layout', mode);
                },

                scanBarcode() {
                    const term = this.barcodeInput.trim().toLowerCase();
                    if (!term) return;
                    const match = this.products.find(p => (p.barcode && p.barcode.toLowerCase() === term) || p.sku.toLowerCase() === term);
                    if (match) {
                        this.addProduct(match);
                    } else {
                        this.error = `No product found for "${this.barcodeInput}".`;
                    }
                    this.barcodeInput = '';
                },

                async refreshQueuedCount() {
                    const queued = await window.PosOfflineQueue.getQueuedSales();
                    this.queuedCount = queued.length;
                },

                async trySyncQueue() {
                    if (!navigator.onLine) return;
                    const queued = await window.PosOfflineQueue.getQueuedSales();
                    for (const queuedSale of queued) {
                        try {
                            const res = await fetch(this.syncUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify(queuedSale),
                            });
                            if (res.ok) {
                                await window.PosOfflineQueue.removeQueuedSale(queuedSale.client_uuid);
                            }
                        } catch (e) {
                            break;
                        }
                    }
                    this.refreshQueuedCount();
                },

                get filteredProducts() {
                    let list = this.products;

                    if (this.categoryFilter !== 'All') {
                        list = list.filter(p => p.category_name === this.categoryFilter);
                    }

                    if (this.search) {
                        const term = this.search.toLowerCase();
                        list = list.filter(p =>
                            p.name.toLowerCase().includes(term) ||
                            p.sku.toLowerCase().includes(term) ||
                            (p.barcode && p.barcode.toLowerCase().includes(term))
                        );
                    }

                    return list.slice(0, 60);
                },

                get usedSerialIds() {
                    return this.cart.filter(i => i.track_serial).map(i => i.product_serial_id);
                },

                addProduct(product) {
                    this.error = '';
                    if (product.track_serial) {
                        const available = product.serials.find(s => !this.usedSerialIds.includes(s.id));
                        if (!available) {
                            this.error = `No available serial/IMEI for ${product.name}.`;
                            return;
                        }
                        this.cart.push({
                            product_id: product.id, name: product.name, unit_price: product.selling_price,
                            quantity: 1, discount_amount: 0, track_serial: true,
                            serials: product.serials, product_serial_id: available.id,
                            tax_rate: product.tax_rate, category_id: product.category_id,
                        });
                    } else {
                        const existing = this.cart.find(i => i.product_id === product.id && !i.track_serial);
                        if (existing) {
                            if (existing.quantity < product.quantity) {
                                existing.quantity++;
                            } else {
                                this.error = `Not enough stock for ${product.name}.`;
                            }
                        } else {
                            if (product.quantity < 1) {
                                this.error = `${product.name} is out of stock.`;
                                return;
                            }
                            this.cart.push({
                                product_id: product.id, name: product.name, unit_price: product.selling_price,
                                quantity: 1, discount_amount: 0, track_serial: false, max_quantity: product.quantity,
                                tax_rate: product.tax_rate, category_id: product.category_id,
                            });
                        }
                    }
                },

                applyDiscountRule(ruleId) {
                    const rule = this.discountRules.find(r => r.id == ruleId);
                    this.appliedRuleId = ruleId;
                    if (!rule) return;

                    const round2 = (n) => Math.round(n * 100) / 100;

                    if (rule.scope === 'all') {
                        this.discountAmount = rule.type === 'percentage'
                            ? round2(this.subtotal * rule.value / 100)
                            : rule.value;
                        return;
                    }

                    this.cart.forEach(item => {
                        const matches = rule.scope === 'category'
                            ? item.category_id === rule.scope_id
                            : item.product_id === rule.scope_id;

                        if (matches && item.quantity >= rule.min_quantity) {
                            const base = item.unit_price * item.quantity;
                            item.discount_amount = rule.type === 'percentage' ? round2(base * rule.value / 100) : rule.value;
                        }
                    });
                },

                removeItem(index) { this.cart.splice(index, 1); },

                lineSubtotal(item) { return (item.unit_price * item.quantity) - (Number(item.discount_amount) || 0); },

                get subtotal() { return this.cart.reduce((sum, item) => sum + this.lineSubtotal(item), 0); },

                get taxAmount() {
                    return this.cart.reduce((sum, item) => sum + (this.lineSubtotal(item) * (Number(item.tax_rate) || 0) / 100), 0);
                },

                get total() { return Math.max(0, this.subtotal - Number(this.discountAmount || 0) + Number(this.taxAmount || 0)); },

                get totalPaid() { return this.payments.reduce((sum, p) => sum + Number(p.amount || 0), 0); },

                get changeDue() { return Math.max(0, this.totalPaid - this.total); },

                addPayment() {
                    const remaining = Math.max(0, this.total - this.totalPaid);
                    this.payments.push({ method: 'cash', amount: remaining, reference_no: '', autoAmount: false });
                },

                removePayment(index) { if (this.payments.length > 1) this.payments.splice(index, 1); },

                markPaymentAmountManual(index) { this.payments[index].autoAmount = false; },

                money(amount) { return Number(amount || 0).toFixed(2); },

                viewReceipt() {
                    // Same-window navigation, not window.open() — a new browsing context
                    // escapes the installed PWA's standalone window into a regular
                    // browser tab. Navigating in-place stays inside the app shell.
                    window.location.href = this.saleComplete.receipt_url;
                },
                printReceipt() {
                    // Print via a hidden iframe rather than window.open() — the receipt
                    // page's own script calls window.print() on autoprint=1, which prints
                    // just that iframe's content with no visible tab/redirect at all.
                    document.getElementById('receipt-print-frame').src = this.saleComplete.receipt_url + '?autoprint=1';
                },
                newSale() { this.saleComplete = null; },

                async showTransferQr(payment) {
                    const amount = Number(payment.amount || this.total).toFixed(2);
                    const text = `Pay NGN ${amount} to ${this.bankDetails.account_name}\n${this.bankDetails.bank_name}\nAcct No: ${this.bankDetails.account_number}`;
                    this.qrDataUrl = await window.QRCode.toDataURL(text, { width: 200, margin: 1 });
                },

                generateUuid() {
                    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
                    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
                        const r = Math.random() * 16 | 0;
                        return (c === 'x' ? r : (r & 0x3 | 0x8)).toString(16);
                    });
                },

                resetCart() {
                    this.cart = [];
                    this.discountAmount = 0;
                    this.customerId = '';
                    this.appliedRuleId = '';
                    this.payments = [{ method: 'cash', amount: 0, reference_no: '' }];
                    this.resumeSaleId = null;
                },

                async completeSale() {
                    this.error = '';
                    if (this.cart.length === 0) { this.error = 'Cart is empty.'; return; }
                    if (this.totalPaid < this.total) { this.error = 'Amount paid is less than the total due.'; return; }

                    const clientUuid = this.generateUuid();
                    const payload = {
                        client_uuid: clientUuid,
                        client_sold_at: new Date().toISOString(),
                        was_queued: false,
                        resume_sale_id: this.resumeSaleId,
                        customer_id: this.customerId || null,
                        discount_amount: this.discountAmount,
                        tax_amount: this.taxAmount,
                        items_json: JSON.stringify(this.cart.map(i => ({
                            product_id: i.product_id,
                            product_serial_id: i.product_serial_id || null,
                            quantity: i.quantity,
                            discount_amount: i.discount_amount || 0,
                        }))),
                        payments_json: JSON.stringify(this.payments),
                    };

                    try {
                        const res = await fetch(this.syncUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify(payload),
                        });

                        if (!res.ok) {
                            const data = await res.json().catch(() => ({}));
                            this.error = data.message || 'Could not complete the sale.';
                            return;
                        }

                        const data = await res.json();
                        this.saleComplete = {
                            invoice_number: data.invoice_number,
                            receipt_url: data.receipt_url,
                            total: this.total,
                            change: this.changeDue,
                        };
                        this.resetCart();
                    } catch (e) {
                        payload.was_queued = true;
                        await window.PosOfflineQueue.queueSale(payload);
                        await this.refreshQueuedCount();
                        this.offlineReceipt = {
                            reference: 'OFFLINE-' + clientUuid.slice(0, 8).toUpperCase(),
                            items: this.cart.map(i => ({ name: i.name, quantity: i.quantity, subtotal: this.lineSubtotal(i) })),
                            total: this.total, paid: this.totalPaid, change: this.changeDue,
                        };
                        this.resetCart();
                    }
                },

                submitHold() {
                    this.error = '';
                    if (this.cart.length === 0) { this.error = 'Cart is empty.'; return false; }

                    document.getElementById('items_json').value = JSON.stringify(this.cart.map(i => ({
                        product_id: i.product_id,
                        product_serial_id: i.product_serial_id || null,
                        quantity: i.quantity,
                        discount_amount: i.discount_amount || 0,
                    })));
                    return true;
                },
            };
        }
    </script>

    @if ($errors->any())
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-md px-4 py-3 mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <div id="pos-app" x-data='posCart(@json($products), @json($categories), @json($quickPicks), @json($customers), @json($resumeData), @json($discountRules), @json($bankDetails), "{{ route('pos.sync') }}")'>

        <div x-show="isOffline" class="bg-amber-500/10 border border-amber-500/30 text-amber-400 text-sm px-4 py-2 rounded-md mb-4">
            You're offline. Sales will keep working and sync automatically once the connection returns.
            <span x-show="queuedCount > 0" x-text="'(' + queuedCount + ' waiting to sync)'"></span>
        </div>
        <div x-show="!isOffline && queuedCount > 0" class="bg-sky-500/10 border border-sky-500/30 text-sky-400 text-sm px-4 py-2 rounded-md mb-4">
            Syncing <span x-text="queuedCount"></span> offline sale(s)...
        </div>

        <!-- Toolbar: search, barcode scan, layout toggle -->
        <div class="flex flex-wrap items-center gap-2 mb-4">
            <input type="text" x-model="search" placeholder="Search products by name / SKU..."
                   class="flex-1 min-w-[220px] bg-surface-raised border-border-strong text-ink placeholder-ink-subtle rounded-md shadow-sm">
            <input id="pos-barcode-input" type="text" x-model="barcodeInput" @keydown.enter.prevent="scanBarcode()"
                   placeholder="Scan barcode... (F8)"
                   class="w-48 bg-surface-raised border-border-strong text-ink placeholder-ink-subtle rounded-md shadow-sm">

            <div class="flex items-center bg-surface-raised border border-border-strong rounded-md p-0.5">
                <button type="button" @click="setLayout('beam')" title="Beam — cart on the right"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded" :class="layout === 'beam' ? 'bg-accent-500 text-zinc-950' : 'text-ink-muted hover:text-ink'">
                    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="1.5"/><path d="M15 4v16"/></svg>
                    Beam
                </button>
                <button type="button" @click="setLayout('lane')" title="Lane — cart on the left"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded" :class="layout === 'lane' ? 'bg-accent-500 text-zinc-950' : 'text-ink-muted hover:text-ink'">
                    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="16" rx="1.5"/><path d="M9 4v16"/></svg>
                    Lane
                </button>
                <button type="button" @click="setLayout('counter')" title="Counter — sidebar + dense list"
                        class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded" :class="layout === 'counter' ? 'bg-accent-500 text-zinc-950' : 'text-ink-muted hover:text-ink'">
                    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h10"/></svg>
                    Counter
                </button>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6 items-start">

            <!-- Category sidebar: Counter mode only -->
            <div x-show="layout === 'counter'" class="w-full lg:w-48 lg:shrink-0 lg:order-1" style="display:none">
                <div class="bg-surface-raised border border-border rounded-lg p-2 lg:sticky lg:top-4">
                    <p class="px-2 py-1 text-xs font-semibold text-ink-subtle uppercase tracking-wider">Category</p>
                    <button type="button" @click="categoryFilter = 'All'"
                            class="w-full flex items-center justify-between text-left px-2 py-1.5 rounded text-sm"
                            :class="categoryFilter === 'All' ? 'bg-accent-500/10 text-accent-400' : 'text-ink-muted hover:bg-surface-hover'">
                        <span>All</span><span x-text="products.length" class="text-xs"></span>
                    </button>
                    <template x-for="cat in categories" :key="cat.name">
                        <button type="button" @click="categoryFilter = cat.name"
                                class="w-full flex items-center justify-between text-left px-2 py-1.5 rounded text-sm"
                                :class="categoryFilter === cat.name ? 'bg-accent-500/10 text-accent-400' : 'text-ink-muted hover:bg-surface-hover'">
                            <span x-text="cat.name"></span><span x-text="cat.count" class="text-xs"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Product picker -->
            <div class="w-full flex-1 min-w-0" :class="layout === 'lane' ? 'lg:order-2' : 'lg:order-1'">
                <!-- Category pills: Beam/Lane only -->
                <div x-show="layout !== 'counter'" class="flex items-center gap-2 overflow-x-auto pb-2 mb-3" style="display:none">
                    <button type="button" @click="categoryFilter = 'All'"
                            class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border"
                            :class="categoryFilter === 'All' ? 'bg-accent-500 text-zinc-950 border-accent-500' : 'border-border-strong text-ink-muted hover:text-ink'">
                        All <span x-text="products.length"></span>
                    </button>
                    <template x-for="cat in categories" :key="cat.name">
                        <button type="button" @click="categoryFilter = cat.name"
                                class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium border"
                                :class="categoryFilter === cat.name ? 'bg-accent-500 text-zinc-950 border-accent-500' : 'border-border-strong text-ink-muted hover:text-ink'">
                            <span x-text="cat.name"></span> <span x-text="cat.count"></span>
                        </button>
                    </template>
                </div>

                <!-- Quick picks -->
                <div x-show="quickPicks.length > 0" class="mb-3" style="display:none">
                    <p class="text-xs font-semibold text-ink-subtle uppercase tracking-wider mb-1.5">Quick Picks</p>
                    <div class="flex items-center gap-2 overflow-x-auto pb-1">
                        <template x-for="product in quickPicks" :key="'qp-' + product.id">
                            <button type="button" @click="addProduct(product)"
                                    class="shrink-0 flex items-center gap-2 px-3 py-1.5 rounded-full border border-border-strong hover:border-accent-500 bg-surface-raised text-xs">
                                <span class="text-ink" x-text="product.name"></span>
                                <span class="text-accent-400 font-medium" x-text="'₦' + money(product.selling_price)"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Beam/Lane: image card grid -->
                <div x-show="layout !== 'counter'" class="grid grid-cols-2 sm:grid-cols-3 gap-3 max-h-[36rem] overflow-y-auto bg-surface-raised border border-border rounded-lg p-4" style="display:none">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button type="button" @click="addProduct(product)"
                                class="text-left border border-border rounded-lg overflow-hidden hover:border-accent-500 hover:bg-surface-hover bg-surface">
                            <div class="aspect-square bg-surface-hover flex items-center justify-center overflow-hidden">
                                <img x-show="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover">
                                <svg x-show="!product.image_url" class="h-8 w-8 text-ink-subtle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3l8 4.5v9L12 21l-8-4.5v-9z"/><path stroke-linecap="round" d="M4 7.5L12 12l8-4.5M12 12v9"/></svg>
                            </div>
                            <div class="p-2.5">
                                <div class="font-medium text-ink text-sm truncate" x-text="product.name"></div>
                                <div class="text-xs mt-0.5" :class="product.quantity <= 0 ? 'text-red-400' : 'text-ink-subtle'" x-text="product.quantity + ' ' + (product.unit || 'pc') + ' in stock'"></div>
                                <div class="text-sm font-semibold text-ink mt-1" x-text="'₦' + money(product.selling_price)"></div>
                            </div>
                        </button>
                    </template>
                    <p x-show="filteredProducts.length === 0" class="col-span-full text-sm text-ink-subtle text-center py-8">No products match.</p>
                </div>

                <!-- Counter: dense list -->
                <div x-show="layout === 'counter'" class="max-h-[36rem] overflow-y-auto bg-surface-raised border border-border rounded-lg divide-y divide-border" style="display:none">
                    <template x-for="product in filteredProducts" :key="'list-' + product.id">
                        <button type="button" @click="addProduct(product)" class="w-full flex items-center gap-3 px-3 py-2.5 hover:bg-surface-hover text-left">
                            <div class="h-9 w-9 rounded-md bg-surface-hover shrink-0 overflow-hidden flex items-center justify-center">
                                <img x-show="product.image_url" :src="product.image_url" :alt="product.name" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-sm text-ink truncate" x-text="product.name"></div>
                                <div class="text-xs text-ink-subtle" x-text="product.sku + ' · ' + product.quantity + ' ' + (product.unit || 'pc') + ' in stock'"></div>
                            </div>
                            <div class="text-sm font-semibold text-ink shrink-0" x-text="'₦' + money(product.selling_price)"></div>
                        </button>
                    </template>
                    <p x-show="filteredProducts.length === 0" class="text-sm text-ink-subtle text-center py-8">No products match.</p>
                </div>
            </div>

        <!-- Cart -->
        <div class="w-full lg:w-96 lg:shrink-0 bg-surface-raised border border-border rounded-lg p-4 flex flex-col" :class="layout === 'lane' ? 'lg:order-1' : 'lg:order-2'">
            <template x-if="error">
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs rounded-md px-3 py-2 mb-3" x-text="error"></div>
            </template>

            <div class="mb-3">
                <label class="flex items-center gap-1.5 text-xs text-ink-muted">
                    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path stroke-linecap="round" d="M5 20c0-3.6 3.1-6.5 7-6.5s7 2.9 7 6.5"/></svg>
                    Customer (optional)
                </label>
                <select id="pos-customer-select" x-model="customerId" class="w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm mt-1">
                    <option value="">Walk-in Customer</option>
                    <template x-for="c in customers" :key="c.id">
                        <option :value="c.id" x-text="c.name + (c.phone ? ' (' + c.phone + ')' : '')"></option>
                    </template>
                </select>
            </div>

            <div class="flex-1 overflow-y-auto space-y-2 max-h-64">
                <template x-for="(item, index) in cart" :key="index">
                    <div class="border border-border rounded-md p-2 text-sm">
                        <div class="flex justify-between items-start">
                            <div class="font-medium text-ink" x-text="item.name"></div>
                            <button type="button" @click="removeItem(index)" class="text-red-400 text-xs">Remove</button>
                        </div>
                        <template x-if="item.track_serial">
                            <select x-model.number="item.product_serial_id" class="w-full text-xs bg-surface-hover border-border-strong text-ink rounded mt-1">
                                <template x-for="s in item.serials" :key="s.id">
                                    <option :value="s.id" x-text="s.imei_serial"></option>
                                </template>
                            </select>
                        </template>
                        <div class="flex items-center gap-2 mt-1">
                            <template x-if="!item.track_serial">
                                <input type="number" min="1" :max="item.max_quantity" x-model.number="item.quantity" class="w-16 text-xs bg-surface-hover border-border-strong text-ink rounded">
                            </template>
                            <template x-if="item.track_serial">
                                <span class="text-xs text-ink-muted">Qty: 1</span>
                            </template>
                            <span class="text-xs text-ink-subtle">&times; ₦<span x-text="money(item.unit_price)"></span></span>
                            <input type="number" min="0" x-model.number="item.discount_amount" placeholder="discount" class="w-20 text-xs bg-surface-hover border-border-strong text-ink rounded ml-auto">
                        </div>
                        <div class="text-right text-xs font-semibold text-ink mt-1">₦<span x-text="money(lineSubtotal(item))"></span></div>
                    </div>
                </template>
                <div x-show="cart.length === 0" class="text-center py-6">
                    <svg class="h-8 w-8 mx-auto text-ink-subtle mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 12.2a2 2 0 002 1.8h8.2a2 2 0 002-1.7L21 8H6"/></svg>
                    <p class="text-sm text-ink-subtle">Cart is empty</p>
                </div>
            </div>

            <div class="border-t border-border mt-3 pt-3 space-y-2 text-sm text-ink">
                <div class="flex justify-between"><span>Subtotal</span><span>₦<span x-text="money(subtotal)"></span></span></div>

                <template x-if="discountRules.length > 0">
                    <select :value="appliedRuleId" @change="applyDiscountRule($event.target.value)" class="w-full text-xs bg-surface-hover border-border-strong text-ink rounded">
                        <option value="">Apply a discount rule...</option>
                        <template x-for="rule in discountRules" :key="rule.id">
                            <option :value="rule.id" x-text="rule.name + ' (' + (rule.type === 'percentage' ? rule.value + '%' : '₦' + rule.value) + ')'"></option>
                        </template>
                    </select>
                </template>

                <div class="flex justify-between items-center">
                    <span class="flex items-center gap-1.5">
                        <svg class="h-3.5 w-3.5 shrink-0 text-ink-subtle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12l-8 8-9-9V4h7z"/><circle cx="7.5" cy="7.5" r="1.3"/></svg>
                        Discount (F3)
                    </span>
                    <input id="pos-discount-input" type="number" min="0" x-model.number="discountAmount" class="w-24 text-xs bg-surface-hover border-border-strong text-ink rounded text-right">
                </div>
                <div class="flex justify-between items-center text-ink-muted">
                    <span>Tax (auto)</span>
                    <span>₦<span x-text="money(taxAmount)"></span></span>
                </div>
                <div class="flex justify-between font-semibold text-base border-t border-border pt-2"><span>Total</span><span>₦<span x-text="money(total)"></span></span></div>
            </div>

            <div class="border-t border-border mt-3 pt-3 space-y-2">
                <div class="text-xs text-ink-muted font-medium">Payments</div>
                <template x-for="(payment, index) in payments" :key="index">
                    <div class="flex items-center gap-1">
                        <select x-model="payment.method" class="text-xs bg-surface-hover border-border-strong text-ink rounded w-24">
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="transfer">Transfer</option>
                        </select>
                        <input type="number" min="0" step="0.01" x-model.number="payment.amount" @input="markPaymentAmountManual(index)" placeholder="amount" class="text-xs bg-surface-hover border-border-strong text-ink rounded w-24">
                        <input type="text" x-model="payment.reference_no" placeholder="ref#" class="text-xs bg-surface-hover border-border-strong text-ink rounded flex-1">
                        <button type="button" x-show="payment.method === 'transfer'" @click="showTransferQr(payment)" class="text-accent-400 text-xs">QR</button>
                        <button type="button" @click="removePayment(index)" class="text-red-400 text-xs">&times;</button>
                    </div>
                </template>
                <button type="button" @click="addPayment()" class="text-xs text-accent-400 hover:text-accent-300 hover:underline">+ Add payment method</button>

                <div class="flex justify-between text-sm pt-2 text-ink">
                    <span>Paid</span><span>₦<span x-text="money(totalPaid)"></span></span>
                </div>
                <div class="flex justify-between text-sm font-semibold text-emerald-400">
                    <span>Change</span><span>₦<span x-text="money(changeDue)"></span></span>
                </div>
            </div>

            <form method="POST" action="{{ route('pos.hold') }}" @submit="if (!submitHold()) $event.preventDefault()" class="mt-3 space-y-2">
                @csrf
                <input type="hidden" name="resume_sale_id" :value="resumeSaleId">
                <input type="hidden" name="customer_id" :value="customerId">
                <input type="hidden" name="discount_amount" :value="discountAmount">
                <input type="hidden" name="tax_amount" :value="taxAmount">
                <input type="hidden" name="items_json" id="items_json">
                <button id="pos-hold-submit" type="submit" :disabled="isOffline" :class="isOffline ? 'opacity-50 cursor-not-allowed' : ''"
                        class="w-full flex items-center justify-center gap-2 bg-surface-hover text-ink rounded-md py-2 font-medium hover:bg-zinc-700">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="4" width="18" height="5" rx="1"/><path stroke-linecap="round" d="M5 9v9a2 2 0 002 2h10a2 2 0 002-2V9M10 13h4"/></svg>
                    Hold Order (F4)
                </button>
            </form>

            <button type="button" @click="completeSale()"
                    class="w-full flex items-center justify-center gap-2 bg-accent-500 text-zinc-950 rounded-md py-2 font-medium hover:bg-accent-400 mt-2">
                <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                Complete Sale (F9)
            </button>
        </div>
        </div>

        <!-- Sale complete modal -->
        <template x-if="saleComplete">
            <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
                <div class="bg-surface-raised border border-border rounded-lg p-8 max-w-sm w-full text-center">
                    <div class="mx-auto h-14 w-14 rounded-full bg-emerald-500/10 flex items-center justify-center mb-4">
                        <svg class="h-7 w-7 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div class="text-lg font-semibold text-ink">Sale complete</div>
                    <div class="text-xs text-ink-subtle mt-1" x-text="saleComplete.invoice_number"></div>
                    <div class="text-3xl font-bold text-ink mt-3">₦<span x-text="money(saleComplete.total)"></span></div>
                    <div class="text-sm text-emerald-400 mt-1" x-show="saleComplete.change > 0">Change: ₦<span x-text="money(saleComplete.change)"></span></div>

                    <div class="flex items-center justify-center gap-2 mt-6">
                        <button type="button" @click="viewReceipt()" class="flex items-center gap-1.5 px-3 py-2 rounded-md text-sm text-ink-muted hover:text-ink hover:bg-surface-hover">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h9l3 3v15H6z"/><path stroke-linecap="round" d="M9 8h6M9 12h6M9 16h4"/></svg>
                            View receipt
                        </button>
                        <button type="button" @click="printReceipt()" class="flex items-center gap-1.5 px-3 py-2 rounded-md text-sm text-ink-muted hover:text-ink hover:bg-surface-hover">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 9V4h12v5M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v7H6v-7z"/></svg>
                            Print receipt
                        </button>
                        <button type="button" @click="newSale()" class="flex items-center gap-1.5 px-4 py-2 rounded-md text-sm font-medium bg-accent-500 text-zinc-950 hover:bg-accent-400">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                            New sale
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <!-- QR modal -->
        <template x-if="qrDataUrl">
            <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="qrDataUrl = null">
                <div class="bg-surface-raised border border-border rounded-lg p-6 max-w-xs w-full text-center">
                    <div class="font-semibold text-ink mb-3">Scan to Pay by Transfer</div>
                    <img :src="qrDataUrl" alt="Transfer QR code" class="mx-auto">
                    <div class="text-xs text-ink-muted mt-3" x-text="bankDetails.account_name + ' — ' + bankDetails.bank_name"></div>
                    <div class="text-xs text-ink-muted" x-text="'Acct: ' + bankDetails.account_number"></div>
                    <button type="button" @click="qrDataUrl = null" class="mt-4 w-full bg-surface-hover text-ink rounded-md py-2 text-sm hover:bg-zinc-700">Close</button>
                </div>
            </div>
        </template>

        <!-- Offline sale confirmation -->
        <template x-if="offlineReceipt">
            <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.self="offlineReceipt = null">
                <div class="bg-surface-raised border border-border rounded-lg p-6 max-w-sm w-full">
                    <div class="text-center mb-4">
                        <div class="text-lg font-semibold text-amber-400">Saved Offline</div>
                        <div class="text-xs text-ink-muted mt-1">No internet connection — this sale will sync automatically once you're back online.</div>
                    </div>
                    <div class="text-sm text-ink space-y-1 mb-4">
                        <div class="text-xs text-ink-subtle">Reference: <span x-text="offlineReceipt.reference"></span></div>
                        <template x-for="item in offlineReceipt.items" :key="item.name">
                            <div class="flex justify-between"><span x-text="item.quantity + '× ' + item.name"></span><span x-text="'₦' + money(item.subtotal)"></span></div>
                        </template>
                        <div class="flex justify-between font-semibold border-t border-border pt-1"><span>Total</span><span x-text="'₦' + money(offlineReceipt.total)"></span></div>
                        <div class="flex justify-between"><span>Paid</span><span x-text="'₦' + money(offlineReceipt.paid)"></span></div>
                        <div class="flex justify-between text-emerald-400"><span>Change</span><span x-text="'₦' + money(offlineReceipt.change)"></span></div>
                    </div>
                    <button type="button" @click="offlineReceipt = null" class="w-full bg-surface-hover text-ink rounded-md py-2 text-sm hover:bg-zinc-700">Start Next Sale</button>
                </div>
            </div>
        </template>

        <!-- Hidden print target for "Print receipt" — avoids opening a visible tab -->
        <iframe id="receipt-print-frame" style="display:none" title="Receipt print"></iframe>
    </div>
</x-app-layout>
