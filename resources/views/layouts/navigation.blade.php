<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-40 bg-black/60 lg:hidden"></div>

<aside
    class="fixed inset-y-0 left-0 z-50 w-64 bg-surface-raised border-r border-border flex flex-col transition-transform duration-200 ease-in-out lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
>
    <div class="flex items-center gap-2 px-5 h-16 border-b border-border shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 min-w-0">
            <x-application-logo class="h-9 w-auto shrink-0" />
            <span class="font-semibold text-ink truncate">{{ \App\Models\Setting::get('store_name', config('app.name')) }}</span>
        </a>
        <button @click="sidebarOpen = false" class="ms-auto lg:hidden text-ink-subtle hover:text-ink">&times;</button>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-6">
        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            <x-nav-icon name="dashboard" />
            {{ __('nav.dashboard') }}
        </x-nav-link>

        @canany(['access pos', 'view sales', 'manage shifts'])
            <div>
                <p class="px-3 mb-2 text-xs font-semibold text-ink-subtle uppercase tracking-wider">{{ __('nav.sales_section') }}</p>
                <div class="space-y-1">
                    @can('access pos')
                        <x-nav-link :href="route('pos.index')" :active="request()->routeIs('pos.index')"><x-nav-icon name="pos" />{{ __('nav.pos') }}</x-nav-link>
                        <x-nav-link :href="route('shifts.current')" :active="request()->routeIs('shifts.current') || request()->routeIs('shifts.open-form')"><x-nav-icon name="clock" />{{ __('nav.my_shift') }}</x-nav-link>
                        <x-nav-link :href="route('pos.held')" :active="request()->routeIs('pos.held')"><x-nav-icon name="held_orders" />{{ __('nav.held_orders') }}</x-nav-link>
                    @endcan
                    @can('view sales')
                        <x-nav-link :href="route('sales.index')" :active="request()->routeIs('sales.*')"><x-nav-icon name="sales_history" />{{ __('nav.sales_history') }}</x-nav-link>
                    @endcan
                    @canany(['access pos', 'manage shifts'])
                        <x-nav-link :href="route('shifts.index')" :active="request()->routeIs('shifts.index') || request()->routeIs('shifts.show')"><x-nav-icon name="history" />{{ __('nav.shift_history') }}</x-nav-link>
                    @endcanany
                </div>
            </div>
        @endcanany

        @canany(['view customers', 'manage suppliers'])
            <div>
                <p class="px-3 mb-2 text-xs font-semibold text-ink-subtle uppercase tracking-wider">{{ __('nav.customers_section') }}</p>
                <div class="space-y-1">
                    @can('view customers')
                        <x-nav-link :href="route('customers.index')" :active="request()->routeIs('customers.*')"><x-nav-icon name="customers" />{{ __('nav.customers') }}</x-nav-link>
                    @endcan
                    @can('manage suppliers')
                        <x-nav-link :href="route('suppliers.index')" :active="request()->routeIs('suppliers.*')"><x-nav-icon name="suppliers" />{{ __('nav.suppliers') }}</x-nav-link>
                    @endcan
                </div>
            </div>
        @endcanany

        @canany(['manage products', 'manage purchase orders', 'manage stock'])
            <div>
                <p class="px-3 mb-2 text-xs font-semibold text-ink-subtle uppercase tracking-wider">{{ __('nav.inventory_section') }}</p>
                <div class="space-y-1">
                    @can('manage products')
                        <x-nav-link :href="route('products.index')" :active="request()->routeIs('products.*') || request()->routeIs('categories.*')"><x-nav-icon name="products" />{{ __('nav.products') }}</x-nav-link>
                        <x-nav-link :href="route('brands.index')" :active="request()->routeIs('brands.*')"><x-nav-icon name="brands" />{{ __('nav.brands') }}</x-nav-link>
                        <x-nav-link :href="route('units.index')" :active="request()->routeIs('units.*')"><x-nav-icon name="units" />{{ __('nav.units') }}</x-nav-link>
                        <x-nav-link :href="route('labels.create')" :active="request()->routeIs('labels.*')"><x-nav-icon name="print_labels" />{{ __('nav.print_labels') }}</x-nav-link>
                    @endcan
                    @can('manage purchase orders')
                        <x-nav-link :href="route('purchase-orders.index')" :active="request()->routeIs('purchase-orders.*')"><x-nav-icon name="purchases" />{{ __('nav.purchases') }}</x-nav-link>
                    @endcan
                    @can('manage stock')
                        <x-nav-link :href="route('reconciliations.index')" :active="request()->routeIs('reconciliations.*')"><x-nav-icon name="stock_reconciliation" />{{ __('nav.stock_reconciliation') }}</x-nav-link>
                        <x-nav-link :href="route('transfers.index')" :active="request()->routeIs('transfers.*')"><x-nav-icon name="stock_transfers" />{{ __('nav.stock_transfers') }}</x-nav-link>
                    @endcan
                </div>
            </div>
        @endcanany

        @canany(['manage warranties', 'manage repairs'])
            <div>
                <p class="px-3 mb-2 text-xs font-semibold text-ink-subtle uppercase tracking-wider">{{ __('nav.service_section') }}</p>
                <div class="space-y-1">
                    @can('manage warranties')
                        <x-nav-link :href="route('warranties.index')" :active="request()->routeIs('warranties.*')"><x-nav-icon name="warranty" />{{ __('nav.warranty') }}</x-nav-link>
                    @endcan
                    @can('manage repairs')
                        <x-nav-link :href="route('repair-tickets.index')" :active="request()->routeIs('repair-tickets.*')"><x-nav-icon name="repairs" />{{ __('nav.repairs') }}</x-nav-link>
                    @endcan
                </div>
            </div>
        @endcanany

        @canany(['view reports', 'manage discount rules', 'manage users', 'manage roles', 'manage settings', 'manage shifts', 'manage stores', 'manage expenses', 'view system health'])
            <div>
                <p class="px-3 mb-2 text-xs font-semibold text-ink-subtle uppercase tracking-wider">{{ __('nav.admin_section') }}</p>
                <div class="space-y-1">
                    @can('view reports')
                        <x-nav-link :href="route('reports.sales')" :active="request()->routeIs('reports.*') || request()->routeIs('pos.sync-log')"><x-nav-icon name="reports" />{{ __('nav.reports') }}</x-nav-link>
                    @endcan
                    @can('manage stores')
                        <x-nav-link :href="route('stores.index')" :active="request()->routeIs('stores.*')"><x-nav-icon name="stores" />{{ __('nav.stores') }}</x-nav-link>
                        <x-nav-link :href="route('terminals.index')" :active="request()->routeIs('terminals.*')"><x-nav-icon name="terminals" />{{ __('nav.terminals') }}</x-nav-link>
                    @endcan
                    @can('manage discount rules')
                        <x-nav-link :href="route('discount-rules.index')" :active="request()->routeIs('discount-rules.*')"><x-nav-icon name="discounts" />{{ __('nav.discounts') }}</x-nav-link>
                    @endcan
                    @can('manage users')
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')"><x-nav-icon name="users" />{{ __('nav.users') }}</x-nav-link>
                    @endcan
                    @can('manage roles')
                        <x-nav-link :href="route('roles.index')" :active="request()->routeIs('roles.*')"><x-nav-icon name="roles" />{{ __('nav.roles') }}</x-nav-link>
                    @endcan
                    @can('view system health')
                        <x-nav-link :href="route('system-health.index')" :active="request()->routeIs('system-health.*')"><x-nav-icon name="system_health" />{{ __('nav.system_health') }}</x-nav-link>
                    @endcan
                    @can('manage expenses')
                        <x-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*') || request()->routeIs('expense-categories.*')"><x-nav-icon name="expenses" />{{ __('nav.expenses') }}</x-nav-link>
                    @endcan
                    @can('manage settings')
                        <x-nav-link :href="route('settings.edit')" :active="request()->routeIs('settings.*')"><x-nav-icon name="settings" />{{ __('nav.settings') }}</x-nav-link>
                        <x-nav-link :href="route('return-reasons.index')" :active="request()->routeIs('return-reasons.*')"><x-nav-icon name="return_reasons" />{{ __('nav.return_reasons') }}</x-nav-link>
                        <x-nav-link :href="route('cash-mismatch-reasons.index')" :active="request()->routeIs('cash-mismatch-reasons.*')"><x-nav-icon name="cash_mismatch_reasons" />{{ __('nav.cash_mismatch_reasons') }}</x-nav-link>
                        <x-nav-link :href="route('adjustment-reasons.index')" :active="request()->routeIs('adjustment-reasons.*')"><x-nav-icon name="adjustment_reasons" />{{ __('nav.adjustment_reasons') }}</x-nav-link>
                    @endcan
                </div>
            </div>
        @endcanany

        @canany(['view accounting', 'manage chart of accounts', 'manage tax settings'])
            <div>
                <p class="px-3 mb-2 text-xs font-semibold text-ink-subtle uppercase tracking-wider">{{ __('nav.accounting_section') }}</p>
                <div class="space-y-1">
                    @can('view accounting')
                        <x-nav-link :href="route('accounting.trial-balance')" :active="request()->routeIs('accounting.trial-balance')"><x-nav-icon name="scale" />{{ __('nav.trial_balance') }}</x-nav-link>
                        <x-nav-link :href="route('accounting.general-ledger')" :active="request()->routeIs('accounting.general-ledger')"><x-nav-icon name="ledger" />{{ __('nav.general_ledger') }}</x-nav-link>
                        <x-nav-link :href="route('accounting.profit-and-loss')" :active="request()->routeIs('accounting.profit-and-loss')"><x-nav-icon name="trending_up" />{{ __('nav.profit_and_loss') }}</x-nav-link>
                        <x-nav-link :href="route('accounting.balance-sheet')" :active="request()->routeIs('accounting.balance-sheet')"><x-nav-icon name="table" />{{ __('nav.balance_sheet') }}</x-nav-link>
                        <x-nav-link :href="route('accounting.cash-flow')" :active="request()->routeIs('accounting.cash-flow')"><x-nav-icon name="cash_flow" />{{ __('nav.cash_flow') }}</x-nav-link>
                        <x-nav-link :href="route('accounting.journal.index')" :active="request()->routeIs('accounting.journal.*')"><x-nav-icon name="journal" />{{ __('nav.journal') }}</x-nav-link>
                    @endcan
                    @can('manage chart of accounts')
                        <x-nav-link :href="route('accounting.accounts.index')" :active="request()->routeIs('accounting.accounts.*')"><x-nav-icon name="chart_of_accounts" />{{ __('nav.chart_of_accounts') }}</x-nav-link>
                        <x-nav-link :href="route('accounting.fiscal-periods.index')" :active="request()->routeIs('accounting.fiscal-periods.*')"><x-nav-icon name="calendar" />{{ __('nav.fiscal_periods') }}</x-nav-link>
                        <x-nav-link :href="route('accounting.opening-balances.edit')" :active="request()->routeIs('accounting.opening-balances.*')"><x-nav-icon name="wallet" />{{ __('nav.opening_balances') }}</x-nav-link>
                    @endcan
                    @can('manage tax settings')
                        <x-nav-link :href="route('tax-groups.index')" :active="request()->routeIs('tax-groups.*')"><x-nav-icon name="tax_groups" />{{ __('nav.tax_groups') }}</x-nav-link>
                        <x-nav-link :href="route('tax-components.index')" :active="request()->routeIs('tax-components.*')"><x-nav-icon name="calculator" />{{ __('nav.tax_components') }}</x-nav-link>
                    @endcan
                </div>
            </div>
        @endcanany
    </nav>

    <div class="border-t border-border p-3 shrink-0">
        <x-dropdown align="top" width="56">
            <x-slot name="trigger">
                <button class="w-full flex items-center gap-2 px-2 py-2 rounded-md text-sm text-ink-muted hover:bg-surface-hover">
                    <div class="h-8 w-8 shrink-0 rounded-full bg-surface-hover flex items-center justify-center text-xs font-semibold text-accent-400">
                        {{ Str::upper(Str::substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 text-start truncate">{{ Auth::user()->name }}</div>
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('docs.index')">
                    Help & Documentation
                </x-dropdown-link>

                <x-dropdown-link :href="route('profile.edit')">
                    {{ __('nav.profile') }}
                </x-dropdown-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        if (window.caches) { caches.delete('pos-page'); }
                                        this.closest('form').submit();">
                        {{ __('nav.log_out') }}
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</aside>
