<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Accounting</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('accounting._tabs')
            <x-flash-messages />

            <div class="bg-surface-raised border border-border rounded-lg p-4">
                <div class="text-sm text-ink-muted mb-3">Close a full year — rolls net income/expense into Retained Earnings and locks all 12 months.</div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($years as $year)
                        <form method="POST" action="{{ route('accounting.fiscal-periods.close-year') }}" onsubmit="return confirm('Close and lock the entire {{ $year }} year? This posts a closing entry and cannot be easily undone.');" class="inline">
                            @csrf
                            <input type="hidden" name="year" value="{{ $year }}">
                            <button type="submit" class="px-3 py-1.5 bg-surface-hover text-ink-muted rounded-md text-sm hover:bg-zinc-700">Close {{ $year }}</button>
                        </form>
                    @endforeach
                </div>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-surface-hover text-ink-muted border-b border-border">
                        <tr>
                            <th class="py-3 px-4">Period</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($months as $m)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-3 px-4">{{ $m['label'] }}</td>
                                <td class="py-3 px-4">
                                    @if ($m['period']?->is_locked)
                                        <span class="px-2 py-0.5 rounded text-xs bg-amber-500/10 text-amber-400">Locked</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-xs bg-emerald-500/10 text-emerald-400">Open</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <form method="POST" action="{{ route('accounting.fiscal-periods.toggle') }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="year" value="{{ $m['year'] }}">
                                        <input type="hidden" name="month" value="{{ $m['month'] }}">
                                        <button type="submit" class="text-accent-400 hover:text-accent-300 hover:underline text-sm">
                                            {{ $m['period']?->is_locked ? 'Unlock' : 'Lock' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
