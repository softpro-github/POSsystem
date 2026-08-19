@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-surface-hover border-border-strong text-ink placeholder-ink-subtle focus:border-accent-500 focus:ring-accent-500/50 rounded-md shadow-sm disabled:opacity-50 disabled:cursor-not-allowed']) }}>
