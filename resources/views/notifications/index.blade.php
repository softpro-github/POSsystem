<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <h2 class="font-semibold text-xl text-ink leading-tight">{{ __('nav.notifications') }}</h2>
            @if ($notifications->total() > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-sm text-accent-400 hover:underline">Mark all read</button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash-messages />

            <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
                @forelse ($notifications as $notification)
                    <div class="flex items-start justify-between gap-4 px-4 py-3 border-b border-border last:border-0 {{ $notification->read_at ? '' : 'bg-accent-500/5' }}">
                        <div class="min-w-0">
                            <p class="text-sm text-ink">{{ $notification->data['message'] ?? '' }}</p>
                            <p class="text-xs text-ink-subtle mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            @if (! empty($notification->data['url']))
                                <a href="{{ $notification->data['url'] }}" class="text-xs text-accent-400 hover:underline">View</a>
                            @endif
                            @unless ($notification->read_at)
                                <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-ink-subtle hover:text-ink">Mark read</button>
                                </form>
                            @endunless
                        </div>
                    </div>
                @empty
                    <p class="px-4 py-8 text-sm text-ink-subtle text-center">No notifications yet.</p>
                @endforelse
            </div>

            {{ $notifications->links() }}
        </div>
    </div>
</x-app-layout>
