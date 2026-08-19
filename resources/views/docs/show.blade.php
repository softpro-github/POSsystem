<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2 text-sm">
            <a href="{{ route('docs.index') }}" class="text-ink-subtle hover:text-ink">Help</a>
            <span class="text-ink-subtle">/</span>
            <h2 class="font-semibold text-xl text-ink leading-tight">{{ $title }}</h2>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto flex gap-8 items-start">
        <nav class="w-48 shrink-0 hidden lg:block sticky top-20 space-y-1">
            @foreach ($pages as $slug => [$file, $pageTitle])
                <a href="{{ route('docs.show', $slug) }}"
                   class="block px-3 py-2 rounded-md text-sm {{ $slug === $currentPage ? 'bg-accent-500/10 text-accent-400 font-medium' : 'text-ink-muted hover:bg-surface-hover hover:text-ink' }}">
                    {{ $pageTitle }}
                </a>
            @endforeach
        </nav>

        <div class="flex-1 min-w-0 bg-surface-raised border border-border rounded-lg p-8">
            <div class="docs-content">
                {!! $html !!}
            </div>
        </div>
    </div>
</x-app-layout>
