@php
    $tabs = [
        'reports.sales' => 'Sales',
        'reports.inventory' => 'Inventory',
        'reports.profit' => 'Profit',
        'reports.warranty' => 'Warranty',
        'reports.repair' => 'Repair',
        'pos.sync-log' => 'Offline Sync Log',
    ];
@endphp
<div class="flex flex-wrap items-center justify-between gap-2">
    <div class="flex flex-wrap gap-2">
        @foreach ($tabs as $route => $label)
            <a href="{{ route($route) }}" @class([
                'px-4 py-2 rounded-md text-sm',
                'bg-accent-500 text-zinc-950' => request()->routeIs($route),
                'bg-surface-raised border border-border text-ink-muted hover:bg-surface-hover' => !request()->routeIs($route),
            ])>{{ $label }}</a>
        @endforeach
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('saved-reports.index') }}" @class([
            'px-4 py-2 rounded-md text-sm',
            'bg-accent-500 text-zinc-950' => request()->routeIs('saved-reports.*'),
            'bg-surface-raised border border-border text-ink-muted hover:bg-surface-hover' => !request()->routeIs('saved-reports.*'),
        ])>Saved Reports</a>
        @unless (request()->routeIs('saved-reports.*') || request()->routeIs('pos.sync-log'))
            @include('reports._save_view', ['reportType' => request()->route()?->getName() ?? 'reports.sales'])
        @endunless
    </div>
</div>
