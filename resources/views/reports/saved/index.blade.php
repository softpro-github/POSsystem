<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Reports</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('reports._tabs')
            <x-flash-messages />

            <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Name</th>
                            <th class="py-3 px-4">Report</th>
                            <th class="py-3 px-4">Schedule</th>
                            <th class="py-3 px-4">Recipients</th>
                            <th class="py-3 px-4">Last Run</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($savedReports as $saved)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4">{{ $saved->name }}</td>
                                <td class="py-3 px-4 text-ink-muted">
                                    @if (\Illuminate\Support\Facades\Route::has($saved->report_type))
                                        <a href="{{ route($saved->report_type) }}?{{ $saved->filters['qs'] ?? '' }}" class="text-accent-400 hover:text-accent-300 hover:underline">Open</a>
                                    @else
                                        {{ $saved->report_type }}
                                    @endif
                                </td>
                                <td class="py-3 px-4">{{ $saved->schedule_frequency ? ucfirst($saved->schedule_frequency) : '—' }}</td>
                                <td class="py-3 px-4 text-ink-muted">{{ $saved->recipients ?? '—' }}</td>
                                <td class="py-3 px-4 text-ink-muted">{{ $saved->last_run_at?->format('Y-m-d H:i') ?? 'Never' }}</td>
                                <td class="py-3 px-4 text-right">
                                    <form action="{{ route('saved-reports.destroy', $saved) }}" method="POST" class="inline" onsubmit="return confirm('Delete this saved report?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-6 px-4 text-center text-ink-muted">No saved reports yet — use "Save this view" on any report page.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $savedReports->links() }}
        </div>
    </div>
</x-app-layout>
