<form method="GET" class="flex flex-wrap items-center gap-3 bg-surface-raised border border-border rounded-lg p-4">
    <label class="text-sm text-ink-muted">From
        <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm ml-1">
    </label>
    <label class="text-sm text-ink-muted">To
        <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm ml-1">
    </label>
    <button type="submit" class="px-4 py-2 bg-surface-hover text-ink-muted rounded-md text-sm hover:bg-zinc-700">Apply</button>
</form>
