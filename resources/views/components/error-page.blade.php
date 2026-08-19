@props(['code', 'title', 'message', 'icon' => 'lock'])

@php
    $icons = [
        'lock' => '<rect x="5" y="11" width="14" height="9" rx="1.5"/><path d="M8 11V7a4 4 0 018 0v4"/>',
        'compass' => '<circle cx="12" cy="12" r="9"/><path d="M15 9l-2 6-6 2 2-6z"/>',
        'alert' => '<path d="M12 3l10 18H2z"/><path stroke-linecap="round" d="M12 10v4M12 17h.01"/>',
        'wrench' => '<path d="M14.7 6.3a4 4 0 00-5.4 5.4L3 18l3 3 6.3-6.3a4 4 0 005.4-5.4l-2.5 2.5-2-2z"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 7v5l3 3"/>',
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $code }} — {{ \App\Models\Setting::get('store_name', config('app.name')) }}</title>
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        @include('partials.theme-init')
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css'])
    </head>
    <body class="font-sans text-ink antialiased">
        <div class="min-h-screen flex flex-col justify-center items-center px-6 bg-surface">
            <div class="w-full max-w-sm text-center">
                <div class="mx-auto h-16 w-16 rounded-full bg-accent-500/10 flex items-center justify-center mb-6">
                    <svg class="h-8 w-8 text-accent-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        {!! $icons[$icon] ?? $icons['lock'] !!}
                    </svg>
                </div>

                <div class="text-xs font-semibold text-ink-subtle uppercase tracking-wider mb-2">Error {{ $code }}</div>
                <h1 class="text-xl font-semibold text-ink mb-2">{{ $title }}</h1>
                <p class="text-sm text-ink-muted mb-8">{{ $message }}</p>

                <div class="flex items-center justify-center gap-3">
                    <button type="button" onclick="history.back()" class="px-4 py-2 rounded-md text-sm text-ink-muted hover:text-ink hover:bg-surface-hover border border-border-strong">
                        Go Back
                    </button>
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="px-4 py-2 rounded-md text-sm font-medium bg-accent-500 text-zinc-950 hover:bg-accent-400">
                        {{ auth()->check() ? 'Go to Dashboard' : 'Go to Login' }}
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>
