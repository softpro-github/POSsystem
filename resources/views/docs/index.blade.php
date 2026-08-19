<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Help & Documentation</h2>
    </x-slot>

    <div class="max-w-3xl mx-auto space-y-3">
        @foreach ($pages as $slug => [$file, $title])
            <a href="{{ route('docs.show', $slug) }}"
               class="flex items-center justify-between bg-surface-raised border border-border rounded-lg p-5 hover:border-accent-500 transition-colors">
                <div>
                    <div class="font-medium text-ink">{{ $title }}</div>
                    <div class="text-sm text-ink-subtle mt-0.5">
                        @switch($slug)
                            @case('setup') Installing and running the app, environment setup. @break
                            @case('user-guide') Roles, shifts, using the POS screen, printer/scanner hardware. @break
                            @case('architecture') Data model, services, and code conventions for developers. @break
                        @endswitch
                    </div>
                </div>
                <svg class="h-5 w-5 text-ink-subtle shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        @endforeach
    </div>
</x-app-layout>
