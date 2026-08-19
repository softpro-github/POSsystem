<div id="notification-bell-root" x-data="{
        unreadCount: 0,
        notifications: [],
        loaded: false,
        async fetchCount() {
            // Accept: application/json marks this as an AJAX request to Laravel's
            // auth middleware — without it, a request from an expired session
            // (e.g. this polling loop firing on a PWA window left open past
            // SESSION_LIFETIME) gets treated as a normal page visit: redirected
            // to /login AND remembered as the intended post-login destination,
            // so the next real login lands on this raw JSON endpoint instead of
            // the dashboard. With the header, Laravel returns a clean 401 instead.
            const res = await fetch('{{ route('notifications.unread-count') }}', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            this.unreadCount = data.count;
        },
        async fetchRecent() {
            const res = await fetch('{{ route('notifications.recent') }}', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            this.notifications = data.notifications;
            this.loaded = true;
        },
        async markReadAndGo(item) {
            await fetch(`/notifications/${item.id}/read`, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            });
            if (item.url) window.location = item.url;
        },
        async markAllRead() {
            await fetch('{{ route('notifications.read-all') }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            });
            this.notifications = this.notifications.map((n) => ({ ...n, read: true }));
            this.unreadCount = 0;
        },
    }"
    x-init="fetchCount(); fetchRecent(); setInterval(() => fetchCount(), 45000)"
>
    <x-dropdown align="right" width="w-[26rem]">
        <x-slot name="trigger">
            <button type="button" aria-label="{{ __('nav.notifications') }}"
                    class="relative h-9 w-9 rounded-lg flex items-center justify-center text-ink-muted hover:text-ink hover:bg-surface-hover transition-colors">
                <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span x-show="unreadCount > 0" x-text="unreadCount > 9 ? '9+' : unreadCount"
                      class="absolute top-1 right-1 min-w-[16px] h-4 px-1 rounded-full bg-red-500 text-white text-[10px] leading-4 text-center"></span>
            </button>
        </x-slot>

        <x-slot name="content">
            <div class="flex items-center justify-between px-3 py-2 border-b border-border">
                <span class="text-sm font-semibold text-ink">{{ __('nav.notifications') }}</span>
                <button type="button" @click.stop="markAllRead()" x-show="unreadCount > 0" class="text-xs text-accent-400 hover:underline">Mark all read</button>
            </div>
            <div class="max-h-[28rem] overflow-y-auto">
                <template x-for="item in notifications" :key="item.id">
                    <a href="#" @click.prevent="markReadAndGo(item)"
                       class="block px-4 py-3 border-b border-border last:border-0 hover:bg-surface-hover"
                       :class="!item.read ? 'bg-accent-500/5' : ''">
                        <p class="text-sm text-ink leading-snug" x-text="item.message"></p>
                        <p class="text-xs text-ink-subtle mt-1" x-text="item.created_at"></p>
                    </a>
                </template>
                <p x-show="loaded && notifications.length === 0" class="px-4 py-8 text-sm text-ink-subtle text-center">No notifications yet.</p>
            </div>
            <a href="{{ route('notifications.index') }}" class="block px-3 py-2 text-center text-xs text-ink-subtle hover:text-ink border-t border-border">View all</a>
        </x-slot>
    </x-dropdown>
</div>
