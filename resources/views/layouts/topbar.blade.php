<header class="sticky top-0 z-30 flex items-center gap-4 border-b border-border bg-surface-raised/80 backdrop-blur px-4 py-3 lg:px-8">
    <button @click="sidebarOpen = true" class="lg:hidden text-ink-muted hover:text-ink shrink-0">
        <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <div class="flex-1 min-w-0 space-y-1">
        <x-breadcrumbs :items="$breadcrumbs ?? null" />
        @isset($header)
            {{ $header }}
        @endisset
    </div>

    <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'command-palette' }))"
            class="hidden sm:flex items-center gap-2 h-9 px-3 rounded-lg text-sm text-ink-subtle hover:text-ink hover:bg-surface-hover transition-colors shrink-0">
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="7"/>
            <path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
        </svg>
        <span>{{ __('nav.search_placeholder') }}</span>
        <kbd class="text-xs text-ink-subtle border border-border-strong rounded px-1.5 py-0.5">⌘K</kbd>
    </button>

    <div class="flex items-center gap-1 shrink-0">
        <x-store-switcher />
        <x-language-switcher />

        <button type="button" onclick="window.setTheme(window.getThemeMode() === 'dark' ? 'light' : 'dark')"
                aria-label="Toggle color theme"
                class="h-9 w-9 rounded-lg flex items-center justify-center text-ink-muted hover:text-ink hover:bg-surface-hover transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="4"/>
                <path stroke-linecap="round" d="M12 2.5v2M12 19.5v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2.5 12h2M19.5 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
            </svg>
        </button>

        <button type="button" onclick="openTodaySummary()" aria-label="{{ __('nav.todays_summary') }}"
                class="h-9 w-9 rounded-lg flex items-center justify-center text-ink-muted hover:text-ink hover:bg-surface-hover transition-colors">
            <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="3" y="4" width="18" height="17" rx="2"/>
                <path stroke-linecap="round" d="M8 2v4M16 2v4M3 10h18"/>
                <path stroke-linecap="round" d="M8 14l2 2 4-4"/>
            </svg>
        </button>

        <x-notification-bell />
        <x-print-queue-panel />

        <div x-data="{ installable: false }" x-init="window.addEventListener('pwa-installable', (e) => installable = e.detail)" x-show="installable" x-cloak>
            <button type="button" onclick="window.installApp()" title="Install app"
                    class="h-9 w-9 rounded-lg flex items-center justify-center bg-accent-500 text-zinc-950 hover:bg-accent-400 transition-colors">
                <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v13m0 0l-4-4m4 4l4-4M5 21h14"/>
                </svg>
            </button>
        </div>

        @can('access pos')
            <a href="{{ route('pos.index') }}" title="{{ __('nav.pos') }}"
               class="h-9 w-9 rounded-lg flex items-center justify-center bg-accent-500 text-zinc-950 hover:bg-accent-400 transition-colors">
                <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <rect x="3" y="4" width="18" height="12" rx="1.5"/>
                    <path stroke-linecap="round" d="M8 20h8M12 16v4"/>
                </svg>
            </a>
        @endcan
    </div>
</header>

<script>
    async function openTodaySummary() {
        const container = document.getElementById('today-summary-content');
        container.innerHTML = '<p class="p-6 text-sm text-ink-subtle">Loading…</p>';
        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'today-summary' }));
        const res = await fetch('{{ route('dashboard.today-summary') }}', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        container.innerHTML = await res.text();
    }
</script>
