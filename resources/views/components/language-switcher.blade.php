@php($locales = config('locales', []))

<x-dropdown align="right" width="w-56">
    <x-slot name="trigger">
        <button type="button" aria-label="{{ __('nav.language') }}" class="h-9 w-9 rounded-lg flex items-center justify-center text-ink-muted hover:text-ink hover:bg-surface-hover transition-colors">
            <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <circle cx="12" cy="12" r="9"/>
                <path stroke-linecap="round" d="M3 12h18M12 3c2.5 2.7 3.8 6 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-6-3.8-9s1.3-6.3 3.8-9z"/>
            </svg>
        </button>
    </x-slot>

    <x-slot name="content">
        <div class="max-h-72 overflow-y-auto py-1">
            @foreach ($locales as $code => $meta)
                <form method="POST" action="{{ route('locale.update') }}">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $code }}">
                    <button type="submit"
                            class="w-full flex items-center justify-between gap-2 px-3 py-2 text-sm hover:bg-surface-hover {{ app()->getLocale() === $code ? 'text-accent-400' : 'text-ink' }}">
                        <span class="truncate">{{ $meta['native_name'] }}</span>
                        @if (app()->getLocale() === $code)
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        @endif
                    </button>
                </form>
            @endforeach
        </div>
    </x-slot>
</x-dropdown>
