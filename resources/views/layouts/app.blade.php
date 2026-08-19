<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ config('locales.'.app()->getLocale().'.dir', 'ltr') }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ \App\Models\Setting::get('store_name', config('app.name', 'Laravel')) }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/svg+xml" href="/favicon.svg">
        <link rel="icon" type="image/png" href="/favicon.png">
        <link rel="apple-touch-icon" href="/favicon.png">
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#09090b">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @include('partials.theme-init')

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-surface text-ink" x-data="{ sidebarOpen: false }">
        <div id="nav-loading-bar"></div>
        <div class="min-h-screen flex">
            @include('layouts.navigation')

            <div class="flex-1 flex flex-col lg:pl-64 min-w-0">
                @include('layouts.topbar')

                <!-- Page Content -->
                <main class="flex-1 p-4 lg:p-8">
                    {{ $slot }}
                </main>
            </div>
        </div>

        {{-- Deliberately a sibling of the header, not nested inside it — the header's
             backdrop-blur creates a CSS containing block for position:fixed descendants,
             which would trap this modal's "fixed inset-0" overlay inside the header's
             own bounds instead of covering the full viewport. --}}
        <x-command-palette />

        <x-modal name="today-summary" maxWidth="lg">
            <div class="flex items-center justify-between px-6 pt-6">
                <h3 class="text-lg font-semibold text-ink">{{ __('nav.todays_summary') }}</h3>
                <button @click="show = false" class="text-ink-subtle hover:text-ink">&times;</button>
            </div>
            <div id="today-summary-content"></div>
        </x-modal>
    </body>
</html>
