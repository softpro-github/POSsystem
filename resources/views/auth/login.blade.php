<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ config('locales.'.app()->getLocale().'.dir', 'ltr') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sign in — {{ \App\Models\Setting::get('store_name', config('app.name', 'Laravel')) }}</title>

    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/favicon.png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @include('partials.theme-init')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-surface text-ink transition-colors" style="font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;">
    <div id="nav-loading-bar"></div>
    <div class="fixed top-6 right-6 flex items-center gap-1">
        <x-language-switcher />
        <button type="button" onclick="window.toggleAuthTheme()" aria-label="Toggle color theme"
                class="h-9 w-9 rounded-lg flex items-center justify-center text-ink-muted hover:text-ink bg-surface-hover transition-colors">
        <svg id="auth-theme-icon-sun" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px] hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="12" r="4"/>
            <path stroke-linecap="round" d="M12 2.5v2M12 19.5v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M2.5 12h2M19.5 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
        </svg>
        <svg id="auth-theme-icon-moon" xmlns="http://www.w3.org/2000/svg" class="h-[18px] w-[18px] hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 1020.354 15.354z"/>
        </svg>
        </button>
    </div>

    <main class="min-h-screen flex flex-col items-center justify-center px-4 py-10">
        <div class="w-full max-w-[440px]">
            <x-auth-session-status class="mb-4 text-center" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}"
                  class="w-full bg-surface-raised border border-border rounded-2xl px-9 pt-10 pb-7 transition-colors"
                  style="box-shadow: var(--shadow-card);">
                @csrf

                <div class="flex flex-col items-center justify-center gap-2 mb-8 select-none">
                    <x-application-logo class="h-16 w-auto shrink-0" />
                    <div class="flex flex-col items-center leading-none">
                        <span class="text-lg font-extrabold tracking-wide text-[#f97316]">SOFTPRO</span>
                        <span class="text-2xl font-extrabold tracking-wide text-ink -mt-0.5">POS</span>
                    </div>
                </div>

                <h1 class="text-2xl font-semibold text-ink mb-1.5">Sign in</h1>
                <p class="text-[13.5px] text-ink-muted mb-7">Welcome back. Use your account email and password to continue.</p>

                <x-input-error :messages="$errors->get('email')" class="mb-4" />

                <div class="mb-4">
                    <label for="email" class="block text-[11px] font-medium uppercase tracking-wider text-ink-muted mb-1.5">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           placeholder="you@company.com"
                           class="w-full h-11 px-3 rounded-lg !bg-surface-raised !border-border-strong !text-ink text-sm placeholder:!text-ink-subtle focus:outline-none focus:!border-[#f97316]/60 focus:ring-2 focus:ring-[#f97316]/20 !shadow-none transition">
                </div>

                <div class="mb-3" x-data="{ show: false }">
                    <label for="password" class="block text-[11px] font-medium uppercase tracking-wider text-ink-muted mb-1.5">Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" id="password" name="password" required autocomplete="current-password"
                               class="w-full h-11 px-3 pr-10 rounded-lg !bg-surface-raised !border-border-strong !text-ink text-sm placeholder:!text-ink-subtle focus:outline-none focus:!border-[#f97316]/60 focus:ring-2 focus:ring-[#f97316]/20 !shadow-none transition">
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-ink-muted hover:text-ink">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <svg x-show="show" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                            </svg>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center gap-2 text-[12.5px] text-ink-muted cursor-pointer">
                        <input id="remember_me" type="checkbox" name="remember"
                               class="rounded !border-border-strong !bg-surface-raised !text-[#f97316] focus:ring-[#f97316]/40 focus:ring-offset-0">
                        Remember me
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-[12.5px] font-medium text-[#f97316] hover:text-[#fb923c]">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="w-full h-11 rounded-lg bg-[#f97316] hover:bg-[#ea580c] text-white text-sm font-semibold transition">
                    Sign in
                </button>

                {{--
                @if (app()->environment('local'))
                    <div class="mt-[18px] border border-[#26272d] rounded-[10px] p-3.5">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-[13.5px] font-semibold text-[#ececee]">Demo Credentials</span>
                            <span class="text-[11px] font-medium text-[#f97316] bg-[#f97316]/10 rounded-full px-2 py-0.5">Local Only</span>
                        </div>
                        <div class="space-y-2.5">
                            @foreach ([
                                ['label' => 'Admin', 'email' => 'admin@gadgetstore.test', 'password' => 'password'],
                                ['label' => 'Manager', 'email' => 'manager@gadgetstore.test', 'password' => 'password'],
                                ['label' => 'Cashier', 'email' => 'cashier@gadgetstore.test', 'password' => 'password'],
                            ] as $demo)
                                <div class="border border-[#26272d] rounded-lg p-3">
                                    <div class="flex items-center justify-between mb-1.5">
                                        <span class="text-xs font-semibold text-[#ececee]">{{ $demo['label'] }} Credentials</span>
                                        <button type="button"
                                                onclick="document.getElementById('email').value='{{ $demo['email'] }}';document.getElementById('password').value='{{ $demo['password'] }}';"
                                                class="text-xs font-medium text-[#f97316] border border-[#f97316] rounded-md px-2.5 py-1 hover:bg-[#f97316]/10 transition">
                                            Copy &amp; Fill
                                        </button>
                                    </div>
                                    <div class="text-xs text-[#8a8f98]">Email: <span class="font-mono text-[#f97316] bg-[#f97316]/10 rounded px-1.5 py-0.5">{{ $demo['email'] }}</span></div>
                                    <div class="text-xs text-[#8a8f98] mt-1">Password: <span class="font-mono text-[#f97316] bg-[#f97316]/10 rounded px-1.5 py-0.5">{{ $demo['password'] }}</span></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                --}}
            </form>

            <p class="text-center text-xs text-ink-subtle mt-6">&copy; {{ now()->year }} {{ \App\Models\Setting::get('store_name', config('app.name')) }}. All rights reserved.</p>
        </div>
    </main>

    <script>
        (function () {
            function applyIcon() {
                var theme = document.documentElement.getAttribute('data-theme');
                document.getElementById('auth-theme-icon-sun').classList.toggle('hidden', theme !== 'dark');
                document.getElementById('auth-theme-icon-moon').classList.toggle('hidden', theme !== 'light');
            }
            window.toggleAuthTheme = function () {
                var current = document.documentElement.getAttribute('data-theme');
                window.setTheme(current === 'dark' ? 'light' : 'dark');
                applyIcon();
            };
            window.addEventListener('theme-changed', applyIcon);
            applyIcon();
        })();
    </script>
</body>
</html>
