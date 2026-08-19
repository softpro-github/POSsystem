@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-accent-500 text-start text-base font-medium text-accent-400 bg-accent-500/10 focus:outline-none focus:text-accent-300 focus:bg-accent-500/20 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-ink-muted hover:text-ink hover:bg-surface-hover hover:border-border-strong focus:outline-none focus:text-ink focus:bg-surface-hover focus:border-border-strong transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
