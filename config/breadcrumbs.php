<?php

// Route-name-segment -> label map consumed by resources/views/components/breadcrumbs.blade.php
// to auto-derive a trail from the current route name, e.g. 'products.edit' -> Dashboard > Products > Edit.
// 'segments' names a section/resource; 'actions' names a route action/verb (or null to omit a crumb for it).
return [

    // Values are translation keys where lang/en/nav.php has a matching entry
    // (resolved via __() in the breadcrumbs component, which safely returns a
    // plain string unchanged if it isn't a real dotted key) — otherwise a
    // plain English literal, left untranslated (a documented baseline gap;
    // the auto-derivation covers layout-chrome navigation, not every action verb).
    'segments' => [
        'dashboard' => 'nav.dashboard',
        'pos' => 'nav.pos',
        'shifts' => 'Shifts',
        'sales' => 'nav.sales_history',
        'customers' => 'nav.customers',
        'suppliers' => 'nav.suppliers',
        'products' => 'nav.products',
        'categories' => 'Categories',
        'brands' => 'nav.brands',
        'units' => 'nav.units',
        'labels' => 'nav.print_labels',
        'purchase-orders' => 'nav.purchases',
        'reconciliations' => 'nav.stock_reconciliation',
        'transfers' => 'nav.stock_transfers',
        'warranties' => 'nav.warranty',
        'repair-tickets' => 'nav.repairs',
        'reports' => 'nav.reports',
        'saved-reports' => 'nav.saved_reports',
        'stores' => 'nav.stores',
        'terminals' => 'nav.terminals',
        'discount-rules' => 'nav.discounts',
        'users' => 'nav.users',
        'roles' => 'nav.roles',
        'system-health' => 'nav.system_health',
        'expenses' => 'nav.expenses',
        'expense-categories' => 'Expense Categories',
        'settings' => 'nav.settings',
        'return-reasons' => 'nav.return_reasons',
        'cash-mismatch-reasons' => 'nav.cash_mismatch_reasons',
        'adjustment-reasons' => 'nav.adjustment_reasons',
        'accounting' => 'nav.accounting_section',
        'accounts' => 'nav.chart_of_accounts',
        'fiscal-periods' => 'nav.fiscal_periods',
        'opening-balances' => 'nav.opening_balances',
        'journal' => 'nav.journal',
        'trial-balance' => 'nav.trial_balance',
        'general-ledger' => 'nav.general_ledger',
        'profit-and-loss' => 'nav.profit_and_loss',
        'balance-sheet' => 'nav.balance_sheet',
        'cash-flow' => 'nav.cash_flow',
        'tax-groups' => 'nav.tax_groups',
        'tax-components' => 'nav.tax_components',
        'profile' => 'nav.profile',
    ],

    'actions' => [
        'index' => null,
        'create' => 'Add New',
        'edit' => 'Edit',
        'show' => 'Details',
        'held' => 'nav.held_orders',
        'current' => 'nav.my_shift',
        'open-form' => 'Start Shift',
        'sync-log' => 'Sync Log',
    ],

];
