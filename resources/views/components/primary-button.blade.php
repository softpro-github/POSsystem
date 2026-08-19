<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-accent-500 border border-transparent rounded-md font-semibold text-xs text-zinc-950 uppercase tracking-widest hover:bg-accent-400 focus:bg-accent-400 active:bg-accent-600 focus:outline-none focus:ring-2 focus:ring-accent-500/50 focus:ring-offset-2 focus:ring-offset-surface-raised disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
