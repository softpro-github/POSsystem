<details class="relative">
    <summary class="cursor-pointer inline-flex items-center px-4 py-2 bg-surface-hover border border-border-strong text-ink-muted rounded-md text-sm hover:bg-zinc-700 list-none">Save this view</summary>
    <form method="POST" action="{{ route('saved-reports.store') }}" class="absolute right-0 z-10 mt-2 w-72 bg-surface-raised border border-border rounded-md shadow-lg p-3 space-y-2 text-left"
          x-data @submit="document.getElementById('save-view-filters-{{ $reportType }}').value = window.location.search.replace(/^\?/, '')">
        @csrf
        <input type="hidden" name="report_type" value="{{ $reportType }}">
        <input type="hidden" name="filters[qs]" id="save-view-filters-{{ $reportType }}">
        <input type="text" name="name" placeholder="Name this view" required class="w-full bg-surface-hover border-border-strong text-ink rounded-md text-sm">
        <select name="schedule_frequency" class="w-full bg-surface-hover border-border-strong text-ink rounded-md text-sm">
            <option value="">Don't email — just save</option>
            <option value="daily">Email daily</option>
            <option value="weekly">Email weekly</option>
            <option value="monthly">Email monthly</option>
        </select>
        <input type="text" name="recipients" placeholder="Recipient emails (comma-separated)" class="w-full bg-surface-hover border-border-strong text-ink rounded-md text-sm">
        <button type="submit" class="w-full bg-accent-500 text-zinc-950 text-sm rounded-md py-1 hover:bg-accent-400">Save</button>
    </form>
</details>
