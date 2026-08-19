@can('manage stores')
    @php($stores = \App\Models\Store::where('is_active', true)->orderBy('name')->get(['id', 'name']))
    <script type="application/json" id="store-switcher-data">@json($stores)</script>

    <div x-data="{
            query: '',
            currentId: {{ current_store()?->id ?? 'null' }},
            stores: JSON.parse(document.getElementById('store-switcher-data').textContent),
            get filtered() {
                return this.stores.filter((s) => s.name.toLowerCase().includes(this.query.toLowerCase()));
            },
        }">
        <x-dropdown align="right" width="w-72">
            <x-slot name="trigger">
                <button type="button" class="h-9 px-3 rounded-lg flex items-center gap-2 text-sm text-ink hover:bg-surface-hover transition-colors">
                    <svg class="h-4 w-4 shrink-0 text-ink-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V9l7-6 7 6v12M9 21v-6h6v6" />
                    </svg>
                    <span class="max-w-[9rem] truncate">{{ current_store()?->name ?? 'Select store' }}</span>
                    <svg class="h-3.5 w-3.5 shrink-0 text-ink-subtle" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="p-2 border-b border-border" @click.stop>
                    <input type="text" x-model="query" placeholder="Search stores..."
                           class="w-full h-8 px-2 text-sm bg-surface-hover border-border-strong text-ink rounded-md focus:ring-accent-500/50 focus:border-accent-500">
                </div>
                <div class="max-h-64 overflow-y-auto py-1">
                    <template x-for="store in filtered" :key="store.id">
                        <form method="POST" action="{{ route('store.switch') }}" @click.stop>
                            @csrf
                            <input type="hidden" name="store_id" :value="store.id">
                            <button type="submit"
                                    class="w-full flex items-center justify-between gap-2 px-3 py-2 text-sm hover:bg-surface-hover"
                                    :class="store.id === currentId ? 'text-accent-400' : 'text-ink'">
                                <span x-text="store.name" class="truncate"></span>
                                <svg x-show="store.id === currentId" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </form>
                    </template>
                    <p x-show="filtered.length === 0" class="px-3 py-2 text-sm text-ink-subtle">No stores match.</p>
                </div>
            </x-slot>
        </x-dropdown>
    </div>
@endcan
