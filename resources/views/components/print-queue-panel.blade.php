@php
    $recentJobs = \App\Models\PrintJob::with('requestedBy')
        ->when(current_store(), fn ($q) => $q->where('store_id', current_store()->id))
        ->latest()
        ->limit(5)
        ->get();
@endphp

<x-dropdown align="right" width="w-80">
    <x-slot name="trigger">
        <button type="button" aria-label="{{ __('nav.print_queue') }}"
                class="h-9 w-9 rounded-lg flex items-center justify-center text-ink-muted hover:text-ink hover:bg-surface-hover transition-colors">
            <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V4h12v5M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v7H6v-7z"/>
            </svg>
        </button>
    </x-slot>

    <x-slot name="content">
        <div class="px-3 py-2 border-b border-border">
            <span class="text-sm font-semibold text-ink">{{ __('nav.print_queue') }}</span>
        </div>
        <div class="max-h-80 overflow-y-auto">
            @forelse ($recentJobs as $job)
                <div class="px-3 py-2 border-b border-border last:border-0">
                    <p class="text-sm text-ink">{{ ucfirst($job->type) }}: {{ $job->reference }}</p>
                    <p class="text-xs text-ink-subtle mt-0.5">
                        {{ $job->closed_at ? 'Print dialog closed' : 'Status unknown' }}
                        · {{ $job->created_at->diffForHumans() }}
                    </p>
                </div>
            @empty
                <p class="px-3 py-6 text-sm text-ink-subtle text-center">No recent print jobs.</p>
            @endforelse
        </div>
        <a href="{{ route('print-jobs.index') }}" class="block px-3 py-2 text-center text-xs text-ink-subtle hover:text-ink border-t border-border">View all</a>
    </x-slot>
</x-dropdown>
