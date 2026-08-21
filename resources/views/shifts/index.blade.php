<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">Shift History</h2>
            @if ($myOpenShift)
                <a href="{{ route('shifts.show', $myOpenShift) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-accent-500 text-zinc-950 rounded-md text-sm hover:bg-accent-400">
                    <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M6 3h9l3 3v15H6z"/><path stroke-linecap="round" d="M9 8h6M9 12h6M9 16h4"/></svg>
                    View active shift
                </a>
            @endif
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-4">
        <p class="text-sm text-ink-subtle">Open a shift to start ringing up sales. Close it to reconcile the cash drawer.</p>

        <div class="grid grid-cols-3 gap-4">
            <div class="bg-surface-raised border border-border rounded-lg p-4">
                <div class="text-xs text-ink-subtle uppercase tracking-wider">Total Shifts</div>
                <div class="text-2xl font-semibold text-ink mt-1">{{ $summaryCounts['total'] }}</div>
            </div>
            <div class="bg-surface-raised border border-border rounded-lg p-4">
                <div class="text-xs text-ink-subtle uppercase tracking-wider">Open</div>
                <div class="text-2xl font-semibold text-sky-400 mt-1">{{ $summaryCounts['open'] }}</div>
            </div>
            <div class="bg-surface-raised border border-border rounded-lg p-4">
                <div class="text-xs text-ink-subtle uppercase tracking-wider">With Variance</div>
                <div class="text-2xl font-semibold text-amber-400 mt-1">{{ $summaryCounts['with_variance'] }}</div>
            </div>
        </div>

        <form method="GET" class="flex items-center gap-2">
            <div class="relative flex-1 max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-ink-subtle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by cashier..."
                       class="w-full pl-9 bg-surface-hover border-border-strong text-ink placeholder-ink-subtle rounded-md shadow-sm text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-surface-hover text-ink-muted rounded-md text-sm hover:bg-zinc-700">Search</button>
            @if (request()->hasAny(['search']))
                <a href="{{ route('shifts.index') }}" class="text-sm text-ink-muted hover:underline">Clear</a>
            @endif
        </form>

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-surface-hover text-ink-muted border-b border-border">
                    <tr>
                        <th class="py-3 px-4">Shift</th>
                        <th class="py-3 px-4">Cashier</th>
                        <th class="py-3 px-4">Opened</th>
                        <th class="py-3 px-4">Closed</th>
                        <th class="py-3 px-4 text-right">Sales</th>
                        <th class="py-3 px-4 text-right">Variance</th>
                        <th class="py-3 px-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($shifts as $shift)
                        <tr class="border-b border-border last:border-0">
                            <td class="py-3 px-4 text-ink-muted">#{{ $shift->id }}</td>
                            <td class="py-3 px-4"><a href="{{ route('shifts.show', $shift) }}" class="text-accent-400 hover:text-accent-300 hover:underline">{{ $shift->user->name }}</a></td>
                            <td class="py-3 px-4">{{ $shift->opened_at->format('Y-m-d H:i') }}</td>
                            <td class="py-3 px-4">{{ $shift->closed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="py-3 px-4 text-right">{{ $shift->status === 'closed' ? '₦'.number_format((float) $shift->sales()->where('status', 'completed')->sum('total_amount'), 2) : '—' }}</td>
                            <td class="py-3 px-4 text-right">{{ $shift->variance !== null ? number_format($shift->variance, 2) : '—' }}</td>
                            <td class="py-3 px-4">
                                @if ($shift->status === 'open')
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-sky-500/10 text-sky-400">Open</span>
                                @elseif ($shift->variance != 0)
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-amber-500/10 text-amber-400">Closed with variance</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-emerald-500/10 text-emerald-400">Closed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-6 px-4 text-center text-ink-muted">No shifts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $shifts->links() }}
    </div>
</x-app-layout>
