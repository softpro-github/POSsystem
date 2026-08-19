<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Settings</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <x-flash-messages />

            <div class="bg-surface-raised border border-border rounded-lg p-6 mt-4">
                <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="logo" value="Store Logo (shown on receipts)" />
                        @if ($logoPath)
                            <div class="flex items-center gap-3 mt-1 mb-2">
                                <img src="{{ Storage::url($logoPath) }}" alt="Store logo" class="h-16 w-16 object-contain border rounded-md p-1">
                                <label class="text-xs text-ink-muted inline-flex items-center gap-1">
                                    <input type="checkbox" name="remove_logo" value="1" class="rounded border-border-strong bg-surface-hover text-accent-500 focus:ring-accent-500/50">
                                    Remove logo
                                </label>
                            </div>
                        @endif
                        <input id="logo" name="logo" type="file" accept="image/*" class="mt-1 block w-full text-sm">
                        <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="store_name" value="Store Name" />
                        <x-text-input id="store_name" name="store_name" type="text" class="mt-1 block w-full" value="{{ old('store_name', $settings['store_name']) }}" />
                    </div>

                    <div>
                        <x-input-label for="store_address" value="Store Address" />
                        <x-text-input id="store_address" name="store_address" type="text" class="mt-1 block w-full" value="{{ old('store_address', $settings['store_address']) }}" />
                    </div>

                    <div>
                        <x-input-label for="store_phone" value="Store Phone" />
                        <x-text-input id="store_phone" name="store_phone" type="text" class="mt-1 block w-full" value="{{ old('store_phone', $settings['store_phone']) }}" />
                    </div>

                    <div>
                        <x-input-label for="currency_symbol" value="Currency Symbol" />
                        <x-text-input id="currency_symbol" name="currency_symbol" type="text" class="mt-1 block w-full" value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '₦') }}" />
                    </div>

                    <div>
                        <x-input-label value="Tax Rates" />
                        <p class="mt-1 text-sm text-ink-muted">Managed under <a href="{{ route('tax-groups.index') }}" class="text-accent-400 hover:underline">Tax Groups</a> — set the store-wide default there, plus any category/product-specific overrides.</p>
                    </div>

                    <div>
                        <x-input-label for="low_stock_threshold_default" value="Default Low Stock Threshold" />
                        <x-text-input id="low_stock_threshold_default" name="low_stock_threshold_default" type="number" min="0" class="mt-1 block w-full" value="{{ old('low_stock_threshold_default', $settings['low_stock_threshold_default']) }}" />
                    </div>

                    <div>
                        <x-input-label for="receipt_footer" value="Receipt Footer Text" />
                        <textarea id="receipt_footer" name="receipt_footer" rows="2" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm">{{ old('receipt_footer', $settings['receipt_footer']) }}</textarea>
                    </div>

                    <div class="border-t pt-4">
                        <h3 class="text-sm font-semibold text-ink-muted mb-2">Bank Transfer Details</h3>
                        <p class="text-xs text-ink-muted mb-3">Used to generate a scannable QR code at checkout when a customer pays by transfer.</p>

                        <div class="space-y-4">
                            <div>
                                <x-input-label for="bank_account_name" value="Account Name" />
                                <x-text-input id="bank_account_name" name="bank_account_name" type="text" class="mt-1 block w-full" value="{{ old('bank_account_name', $settings['bank_account_name']) }}" />
                            </div>
                            <div>
                                <x-input-label for="bank_name" value="Bank Name" />
                                <x-text-input id="bank_name" name="bank_name" type="text" class="mt-1 block w-full" value="{{ old('bank_name', $settings['bank_name']) }}" />
                            </div>
                            <div>
                                <x-input-label for="bank_account_number" value="Account Number" />
                                <x-text-input id="bank_account_number" name="bank_account_number" type="text" class="mt-1 block w-full" value="{{ old('bank_account_number', $settings['bank_account_number']) }}" />
                            </div>
                        </div>
                    </div>

                    <x-primary-button>Save Settings</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
