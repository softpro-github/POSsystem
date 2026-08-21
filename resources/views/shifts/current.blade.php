<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Current Shift</h2>
            <a href="{{ route('pos.index') }}" class="text-sm text-accent-400 hover:text-accent-300 hover:underline">Back to POS</a>
        </div>
    </x-slot>

    @php
        $cashSales = $shift->cashSalesTotal();
        $cashRefunds = $shift->cashRefundsTotal();
        $cashPayouts = $shift->cashSupplierPaymentsTotal();
        $expected = $shift->computedExpectedCash();
    @endphp

    <div class="max-w-3xl mx-auto space-y-6">
        <x-flash-messages />

        <div class="bg-surface-raised border border-border rounded-lg p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div><div class="text-ink-muted">Cashier</div><div class="font-medium">{{ $shift->user->name }}</div></div>
            <div><div class="text-ink-muted">Opened At</div><div class="font-medium">{{ $shift->opened_at->format('Y-m-d H:i') }}</div></div>
            <div><div class="text-ink-muted">Opening Float</div><div class="font-medium"><x-money :amount="$shift->opening_float" /></div></div>
            <div><div class="text-ink-muted">Expected Cash Now</div><div class="font-medium text-accent-400"><x-money :amount="$expected" /></div></div>
        </div>

        <div class="bg-surface-raised border border-border rounded-lg p-6">
            <h3 class="font-semibold text-ink mb-4">X Report — Live Snapshot</h3>
            <div class="text-sm space-y-1">
                <div class="flex justify-between"><span>Opening Float</span><x-money :amount="$shift->opening_float" /></div>
                <div class="flex justify-between"><span>+ Cash Sales</span><x-money :amount="$cashSales" /></div>
                <div class="flex justify-between"><span>− Cash Refunds</span><x-money :amount="$cashRefunds" /></div>
                <div class="flex justify-between"><span>− Supplier Payments (cash)</span><x-money :amount="$cashPayouts" /></div>
                <div class="flex justify-between font-semibold border-t border-border pt-2"><span>Expected Cash in Drawer</span><x-money :amount="$expected" /></div>
            </div>
        </div>

        @if ($suppliersOwed->isNotEmpty())
            <div class="bg-surface-raised border border-border rounded-lg p-6" x-data="{ supplierUrl: '' }">
                <h3 class="font-semibold text-ink mb-4">Pay Supplier from Drawer</h3>
                <form method="POST" :action="supplierUrl || '#'" class="flex flex-wrap items-end gap-3" @submit="if (!supplierUrl) $event.preventDefault()">
                    @csrf
                    <input type="hidden" name="method" value="cash">
                    <div>
                        <x-input-label value="Supplier" />
                        <select @change="supplierUrl = $event.target.value" class="mt-1 text-sm bg-surface-hover border-border-strong text-ink rounded-md">
                            <option value="">Select supplier</option>
                            @foreach ($suppliersOwed as $supplier)
                                <option value="{{ route('suppliers.payments.store', $supplier) }}">{{ $supplier->name }} (owed {{ number_format($supplier->balance, 2) }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="amount" value="Amount" />
                        <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="mt-1 w-32 text-sm bg-surface-hover border-border-strong text-ink rounded-md" required>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <x-input-label for="note" value="Note (optional)" />
                        <input type="text" name="note" id="note" class="mt-1 w-full text-sm bg-surface-hover border-border-strong text-ink rounded-md">
                    </div>
                    <x-secondary-button type="submit">Pay</x-secondary-button>
                </form>
            </div>
        @endif

        <div class="bg-surface-raised border border-border rounded-lg p-6">
            <h3 class="font-semibold text-ink mb-4">Close Shift</h3>
            @error('cash_mismatch_reason_id')
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-md px-4 py-2 mb-4">{{ $message }}</div>
            @enderror
            <form method="POST" action="{{ route('shifts.close') }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="closing_count" value="Cash Counted in Drawer" />
                    <x-text-input id="closing_count" name="closing_count" type="number" step="0.01" min="0" class="mt-1 block w-48" value="{{ old('closing_count') }}" required />
                    <p class="text-xs text-ink-subtle mt-1">A mismatch reason is required if this differs from the expected cash above by more than {{ number_format($tolerance, 2) }}.</p>
                </div>
                <div>
                    <x-input-label for="cash_mismatch_reason_id" value="Mismatch Reason (if applicable)" />
                    <select id="cash_mismatch_reason_id" name="cash_mismatch_reason_id" class="mt-1 block w-full sm:w-64 bg-surface-hover border-border-strong text-ink rounded-md shadow-sm">
                        <option value="">— None —</option>
                        @foreach ($cashMismatchReasons as $reason)
                            <option value="{{ $reason->id }}">{{ $reason->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="notes" value="Notes (optional)" />
                    <textarea id="notes" name="notes" rows="2" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm">{{ old('notes') }}</textarea>
                </div>
                <x-danger-button onclick="return confirm('Close this shift? This locks in the final Z report.');">Close Shift</x-danger-button>
            </form>
        </div>
    </div>
</x-app-layout>
