@props(['name'])

@php
    $icons = [
        'dashboard' => '<rect x="3" y="3" width="8" height="8" rx="1.5"/><rect x="13" y="3" width="8" height="8" rx="1.5"/><rect x="3" y="13" width="8" height="8" rx="1.5"/><rect x="13" y="13" width="8" height="8" rx="1.5"/>',
        'pos' => '<circle cx="9" cy="20" r="1"/><circle cx="18" cy="20" r="1"/><path d="M3 4h2l2.4 12.2a2 2 0 002 1.8h8.2a2 2 0 002-1.7L21 8H6"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/>',
        'held_orders' => '<circle cx="12" cy="12" r="9"/><path d="M10 9v6M14 9v6"/>',
        'sales_history' => '<path d="M6 3h9l3 3v15H6z"/><path d="M9 8h6M9 12h6M9 16h4"/>',
        'customers' => '<circle cx="9" cy="8" r="3"/><path d="M3 20c0-3.3 2.7-6 6-6s6 2.7 6 6"/><circle cx="17" cy="9" r="2.5"/><path d="M15.5 14.2c2.5.4 4.5 2.5 4.5 5.8"/>',
        'suppliers' => '<rect x="1" y="7" width="13" height="10" rx="1"/><path d="M14 10h4l3 3v4h-7z"/><circle cx="6" cy="19" r="1.6"/><circle cx="17.5" cy="19" r="1.6"/>',
        'products' => '<path d="M12 3l8 4.5v9L12 21l-8-4.5v-9z"/><path d="M4 7.5L12 12l8-4.5M12 12v9"/>',
        'brands' => '<path d="M12 3h6a2 2 0 012 2v6l-9 9-8-8z"/><circle cx="15.5" cy="7.5" r="1.3"/>',
        'units' => '<rect x="3" y="8" width="18" height="8" rx="1"/><path d="M7 8v3M11 8v3M15 8v3"/>',
        'print_labels' => '<path d="M6 9V4h12v5M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v7H6v-7z"/>',
        'purchases' => '<path d="M6 8h12l1 12H5z"/><path d="M9 8V6a3 3 0 016 0v2"/>',
        'stock_reconciliation' => '<rect x="6" y="4" width="12" height="16" rx="2"/><path d="M9 4V3a1 1 0 011-1h4a1 1 0 011 1v1"/><path d="M9 13l2 2 4-4"/>',
        'stock_transfers' => '<path d="M7 7h13l-3-3M20 17H7l3 3"/>',
        'warranty' => '<path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"/><path d="M9 12l2 2 4-4"/>',
        'repairs' => '<path d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 005.4-5.4l-2.5 2.5-2-2z"/>',
        'reports' => '<path d="M4 20V10M10 20V4M16 20v-7M4 20h16"/>',
        'stores' => '<path d="M3 9l1-5h16l1 5M4 9v11h16V9"/><path d="M4 9a2 2 0 004 0M8 9a2 2 0 004 0M12 9a2 2 0 004 0M16 9a2 2 0 004 0"/>',
        'terminals' => '<rect x="3" y="4" width="18" height="12" rx="1.5"/><path d="M8 20h8M12 16v4"/>',
        'history' => '<path d="M3 12a9 9 0 109-9"/><path d="M3 4v5h5"/><path d="M12 8v4l3 2"/>',
        'discounts' => '<path d="M20 12l-8 8-9-9V4h7z"/><circle cx="7.5" cy="7.5" r="1.3"/><path d="M9 15l6-6"/>',
        'users' => '<circle cx="9" cy="7" r="3"/><path d="M2 20c0-3.3 3-6 7-6s7 2.7 7 6"/><path d="M16 5a3 3 0 010 6M18 14c2.3.6 4 2.7 4 6"/>',
        'roles' => '<circle cx="8" cy="8" r="4"/><path d="M11 11l9 9M17 15l3-3M19 17l2-2"/>',
        'system_health' => '<path d="M3 12h4l2-6 4 12 2-6h6"/>',
        'expenses' => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/><path d="M6 9v.01M18 15v.01"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M2 12h3M19 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1"/>',
        'return_reasons' => '<path d="M9 14l-5-5 5-5"/><path d="M4 9h10a6 6 0 010 12h-1"/>',
        'cash_mismatch_reasons' => '<path d="M12 3l10 18H2z"/><path d="M12 10v4M12 17h.01"/>',
        'adjustment_reasons' => '<path d="M4 6h10M4 12h6M4 18h13"/><circle cx="16" cy="6" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="18" r="2"/>',
        'scale' => '<path d="M12 3v18M5 7l-3 6a3 3 0 006 0zM19 7l-3 6a3 3 0 006 0zM5 7h14M8 21h8"/>',
        'ledger' => '<path d="M12 6c-2-1.5-5-2-8-1v13c3-1 6-.5 8 1 2-1.5 5-2 8-1V5c-3-1-6-.5-8 1z"/><path d="M12 6v13"/>',
        'trending_up' => '<path d="M4 16l5-6 4 3 6-8"/><path d="M14 5h5v5"/>',
        'table' => '<rect x="4" y="4" width="16" height="16" rx="1"/><path d="M4 10h16M10 4v16"/>',
        'cash_flow' => '<path d="M4 8h16M4 8l3-3M4 8l3 3M20 16H4M20 16l-3-3M20 16l-3 3"/>',
        'journal' => '<path d="M15 4l5 5-11 11H4v-5z"/>',
        'chart_of_accounts' => '<rect x="3" y="4" width="4" height="4" rx="1"/><rect x="3" y="10" width="4" height="4" rx="1"/><rect x="3" y="16" width="4" height="4" rx="1"/><path d="M10 6h11M10 12h11M10 18h11"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'wallet' => '<path d="M3 7a2 2 0 012-2h13a2 2 0 012 2v2H5a2 2 0 01-2-2z"/><path d="M3 9h18v9a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><circle cx="16" cy="14" r="1.5"/>',
        'tax_groups' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2z"/><path d="M9 9l6 6M9.5 9h.01M14.5 15h.01"/>',
        'calculator' => '<rect x="5" y="3" width="14" height="18" rx="2"/><path d="M8 7h8M8 11h.01M12 11h.01M16 11h.01M8 15h.01M12 15h.01M16 15h.01M8 19h.01M12 19h.01"/>',
    ];

    $path = $icons[$name] ?? $icons['dashboard'];
@endphp

<svg {{ $attributes->merge(['class' => 'h-[18px] w-[18px] shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
    {!! $path !!}
</svg>
