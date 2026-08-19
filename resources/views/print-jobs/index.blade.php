<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">{{ __('nav.print_queue') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-messages />

            <p class="text-sm text-ink-subtle">
                "Print dialog opened/closed" reflects that the browser's print dialog was invoked — there is no
                way for the app to know whether a page actually printed successfully or was cancelled.
            </p>

            <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted">
                        <tr>
                            <th class="px-4 py-2">Type</th>
                            <th class="px-4 py-2">Reference</th>
                            <th class="px-4 py-2">Requested By</th>
                            <th class="px-4 py-2">Status</th>
                            <th class="px-4 py-2">When</th>
                            <th class="px-4 py-2">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        @forelse ($printJobs as $job)
                            <tr>
                                <td class="px-4 py-2 text-ink">{{ ucfirst($job->type) }}</td>
                                <td class="px-4 py-2 text-ink">{{ $job->reference }}</td>
                                <td class="px-4 py-2 text-ink-muted">{{ $job->requestedBy->name }}</td>
                                <td class="px-4 py-2 text-ink-subtle">{{ $job->closed_at ? 'Print dialog closed' : 'Status unknown' }}</td>
                                <td class="px-4 py-2 text-ink-subtle">{{ $job->created_at->diffForHumans() }}</td>
                                <td class="px-4 py-2">
                                    @if ($job->type === 'receipt' && $job->sale_id)
                                        <a href="{{ route('sales.receipt', $job->sale_id) }}" target="_blank" class="text-accent-400 hover:underline">Reprint</a>
                                    @elseif ($job->type === 'label' && $job->payload)
                                        <a href="{{ route('labels.print') }}?{{ http_build_query(['items' => $job->payload]) }}" target="_blank" class="text-accent-400 hover:underline">Reprint</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center text-ink-subtle">No print jobs yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $printJobs->links() }}
        </div>
    </div>
</x-app-layout>
