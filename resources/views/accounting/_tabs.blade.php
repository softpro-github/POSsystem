@php
    $tabs = [
        'accounting.trial-balance' => 'Trial Balance',
        'accounting.general-ledger' => 'General Ledger',
        'accounting.profit-and-loss' => 'Profit & Loss',
        'accounting.balance-sheet' => 'Balance Sheet',
        'accounting.cash-flow' => 'Cash Flow',
        'accounting.journal.index' => 'Journal',
    ];
@endphp
<div class="flex flex-wrap gap-2">
    @foreach ($tabs as $route => $label)
        <a href="{{ route($route) }}" @class([
            'px-4 py-2 rounded-md text-sm',
            'bg-accent-500 text-zinc-950' => request()->routeIs($route),
            'bg-surface-raised border border-border text-ink-muted hover:bg-surface-hover' => !request()->routeIs($route),
        ])>{{ $label }}</a>
    @endforeach
    @can('manage chart of accounts')
        <a href="{{ route('accounting.accounts.index') }}" @class([
            'px-4 py-2 rounded-md text-sm',
            'bg-accent-500 text-zinc-950' => request()->routeIs('accounting.accounts.*'),
            'bg-surface-raised border border-border text-ink-muted hover:bg-surface-hover' => !request()->routeIs('accounting.accounts.*'),
        ])>Chart of Accounts</a>
        <a href="{{ route('accounting.fiscal-periods.index') }}" @class([
            'px-4 py-2 rounded-md text-sm',
            'bg-accent-500 text-zinc-950' => request()->routeIs('accounting.fiscal-periods.*'),
            'bg-surface-raised border border-border text-ink-muted hover:bg-surface-hover' => !request()->routeIs('accounting.fiscal-periods.*'),
        ])>Fiscal Periods</a>
        <a href="{{ route('accounting.opening-balances.edit') }}" @class([
            'px-4 py-2 rounded-md text-sm',
            'bg-accent-500 text-zinc-950' => request()->routeIs('accounting.opening-balances.*'),
            'bg-surface-raised border border-border text-ink-muted hover:bg-surface-hover' => !request()->routeIs('accounting.opening-balances.*'),
        ])>Opening Balances</a>
    @endcan
</div>
