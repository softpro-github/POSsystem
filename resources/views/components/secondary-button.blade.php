<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-surface-hover border border-border-strong rounded-md font-semibold text-xs text-ink uppercase tracking-widest shadow-sm hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-accent-500/50 focus:ring-offset-2 focus:ring-offset-surface-raised disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
