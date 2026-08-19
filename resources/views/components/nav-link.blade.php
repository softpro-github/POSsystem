@props(['active'])

@php
$classes = ($active ?? false)
            ? 'flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium bg-accent-500/10 text-accent-400 border-l-2 border-accent-500'
            : 'flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium text-ink-muted border-l-2 border-transparent hover:bg-surface-hover hover:text-ink transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
