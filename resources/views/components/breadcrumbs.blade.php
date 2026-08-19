@php
    $trail = $items ?? null;

    if (! $trail) {
        $routeName = request()->route()?->getName();
        $trail = [];

        if ($routeName) {
            $labels = config('breadcrumbs.segments', []);
            $actions = config('breadcrumbs.actions', []);
            $segments = explode('.', $routeName);

            if ($routeName !== 'dashboard') {
                $trail[] = ['label' => __('nav.dashboard'), 'url' => route('dashboard')];
            }

            $prefix = '';
            foreach ($segments as $i => $segment) {
                $isLast = $i === count($segments) - 1;
                $prefix = $prefix ? "{$prefix}.{$segment}" : $segment;

                $label = array_key_exists($segment, $actions)
                    ? $actions[$segment]
                    : ($labels[$segment] ?? Str::title(str_replace('-', ' ', $segment)));

                if ($label === null) {
                    continue;
                }

                // __() on a plain (non-dotted) string safely returns it unchanged
                // when no translation exists, so untranslated segments degrade gracefully.
                $label = __($label);

                if ($isLast) {
                    $trail[] = ['label' => $label, 'url' => null];
                } else {
                    $indexRoute = "{$prefix}.index";
                    $trail[] = ['label' => $label, 'url' => Route::has($indexRoute) ? route($indexRoute) : null];
                }
            }
        }

        if (empty($trail)) {
            $trail[] = ['label' => __('nav.dashboard'), 'url' => null];
        }
    }
@endphp

<nav aria-label="Breadcrumb" class="flex items-center gap-1.5 text-sm text-ink-subtle min-w-0 overflow-hidden">
    @foreach ($trail as $crumb)
        @if (! $loop->first)
            <svg class="h-3.5 w-3.5 shrink-0 rtl:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        @endif
        @if ($crumb['url'] && ! $loop->last)
            <a href="{{ $crumb['url'] }}" class="hover:text-ink truncate">{{ $crumb['label'] }}</a>
        @else
            <span class="text-ink font-medium truncate">{{ $crumb['label'] }}</span>
        @endif
    @endforeach
</nav>
