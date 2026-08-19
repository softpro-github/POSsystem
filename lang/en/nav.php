<?php

// Layout-chrome-only translation source (sidebar, topbar, breadcrumbs).
// Feature-page content (POS, inventory, sales, reports, accounting forms/tables)
// is intentionally NOT covered here — out of scope for this push.
// This file is the source of truth for app/Console/Commands/GenerateTranslations.php,
// which machine-generates lang/{locale}/nav.php for every other locale. Never hand-edit
// a generated locale file — edit this one and re-run `php artisan i18n:generate`.
return [

    // Sidebar section headers
    'sales_section' => 'Sales',
    'customers_section' => 'Customers',
    'inventory_section' => 'Inventory',
    'service_section' => 'Service',
    'admin_section' => 'Admin',
    'accounting_section' => 'Accounting',

    // Sidebar links
    'dashboard' => 'Dashboard',
    'pos' => 'POS',
    'my_shift' => 'My Shift',
    'held_orders' => 'Held Orders',
    'sales_history' => 'Sales History',
    'customers' => 'Customers',
    'suppliers' => 'Suppliers',
    'products' => 'Products',
    'brands' => 'Brands',
    'units' => 'Units',
    'print_labels' => 'Print Labels',
    'purchases' => 'Purchases',
    'stock_reconciliation' => 'Stock Reconciliation',
    'stock_transfers' => 'Stock Transfers',
    'warranty' => 'Warranty',
    'repairs' => 'Repairs',
    'reports' => 'Reports',
    'saved_reports' => 'Saved Reports',
    'stores' => 'Stores',
    'terminals' => 'Terminals',
    'shift_history' => 'Shift History',
    'discounts' => 'Discounts',
    'users' => 'Users',
    'roles' => 'Roles',
    'system_health' => 'System Health',
    'expenses' => 'Expenses',
    'settings' => 'Settings',
    'return_reasons' => 'Return Reasons',
    'cash_mismatch_reasons' => 'Cash Mismatch Reasons',
    'adjustment_reasons' => 'Adjustment Reasons',
    'trial_balance' => 'Trial Balance',
    'general_ledger' => 'General Ledger',
    'profit_and_loss' => 'Profit & Loss',
    'balance_sheet' => 'Balance Sheet',
    'cash_flow' => 'Cash Flow',
    'journal' => 'Journal',
    'chart_of_accounts' => 'Chart of Accounts',
    'fiscal_periods' => 'Fiscal Periods',
    'opening_balances' => 'Opening Balances',
    'tax_groups' => 'Tax Groups',
    'tax_components' => 'Tax Components',
    'profile' => 'Profile',
    'log_out' => 'Log Out',

    // Topbar
    'search_placeholder' => 'Search...',
    'notifications' => 'Notifications',
    'print_queue' => 'Print Queue',
    'todays_summary' => "Today's Summary",
    'toggle_theme' => 'Toggle color theme',
    'theme_light' => 'Light',
    'theme_dark' => 'Dark',
    'theme_auto' => 'Auto',
    'language' => 'Language',

];
