<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">System Health</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-surface-raised border border-border rounded-lg p-4">
                <div class="text-xs text-ink-muted">PHP Version</div>
                <div class="text-lg font-semibold text-ink">{{ $health['php_version'] }}</div>
            </div>
            <div class="bg-surface-raised border border-border rounded-lg p-4">
                <div class="text-xs text-ink-muted">Laravel Version</div>
                <div class="text-lg font-semibold text-ink">{{ $health['laravel_version'] }}</div>
            </div>
            <div class="bg-surface-raised border border-border rounded-lg p-4">
                <div class="text-xs text-ink-muted">Environment</div>
                <div class="text-lg font-semibold text-ink">{{ ucfirst($health['environment']) }}</div>
            </div>
            <div class="bg-surface-raised border border-border rounded-lg p-4">
                <div class="text-xs text-ink-muted">Debug Mode</div>
                <div class="text-lg font-semibold {{ $health['debug_mode'] ? 'text-amber-400' : 'text-emerald-400' }}">{{ $health['debug_mode'] ? 'On' : 'Off' }}</div>
            </div>
        </div>

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <div class="px-4 py-3 border-b border-border font-semibold text-ink">Database</div>
            <div class="p-4 text-sm space-y-1">
                <div class="flex justify-between"><span class="text-ink-muted">Connection</span><span class="{{ $health['database']['connected'] ? 'text-emerald-400' : 'text-red-400' }}">{{ $health['database']['connected'] ? 'Connected' : 'Disconnected' }}</span></div>
                <div class="flex justify-between"><span class="text-ink-muted">Driver</span><span class="text-ink">{{ $health['database']['driver'] }}</span></div>
                @if (!$health['database']['connected'])
                    <div class="text-red-400 text-xs mt-2">{{ $health['database']['error'] }}</div>
                @endif
            </div>
        </div>

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <div class="px-4 py-3 border-b border-border font-semibold text-ink">Queue</div>
            <div class="p-4 text-sm space-y-1">
                <div class="flex justify-between"><span class="text-ink-muted">Connection</span><span class="text-ink">{{ $health['queue_connection'] }}</span></div>
                <div class="flex justify-between"><span class="text-ink-muted">Pending Jobs</span><span class="text-ink">{{ $health['pending_jobs'] ?? 'Not configured' }}</span></div>
                <div class="flex justify-between"><span class="text-ink-muted">Failed Jobs</span><span class="{{ ($health['failed_jobs'] ?? 0) > 0 ? 'text-red-400' : 'text-ink' }}">{{ $health['failed_jobs'] ?? 'Not configured' }}</span></div>
            </div>
        </div>

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <div class="px-4 py-3 border-b border-border font-semibold text-ink">Storage</div>
            <div class="p-4 text-sm space-y-1">
                <div class="flex justify-between"><span class="text-ink-muted">Cache Driver</span><span class="text-ink">{{ $health['cache_driver'] }}</span></div>
                <div class="flex justify-between"><span class="text-ink-muted">Disk Free Space</span><span class="text-ink">{{ $health['disk_free_bytes'] ? number_format($health['disk_free_bytes'] / 1073741824, 1).' GB' : 'Unknown' }}</span></div>
                <div class="flex justify-between"><span class="text-ink-muted">Storage Writable</span><span class="{{ $health['storage_writable'] ? 'text-emerald-400' : 'text-red-400' }}">{{ $health['storage_writable'] ? 'Yes' : 'No' }}</span></div>
            </div>
        </div>

        <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
            <div class="px-4 py-3 border-b border-border font-semibold text-ink">Log File</div>
            <div class="p-4 text-sm space-y-1">
                @if ($health['log']['exists'])
                    <div class="flex justify-between"><span class="text-ink-muted">Size</span><span class="text-ink">{{ number_format($health['log']['size_bytes'] / 1024, 1) }} KB</span></div>
                    <div class="flex justify-between"><span class="text-ink-muted">Last Modified</span><span class="text-ink">{{ $health['log']['modified_at'] }}</span></div>
                @else
                    <p class="text-ink-muted">No log file found.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
