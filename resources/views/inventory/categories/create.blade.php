<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Add Category</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-surface-raised border border-border rounded-lg p-6">
                <form method="POST" action="{{ route('categories.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="name" value="Name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name') }}" required autofocus />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="parent_id" value="Parent Category (optional)" />
                        <select id="parent_id" name="parent_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm">
                            <option value="">— None —</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('parent_id') == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('parent_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="tax_group_id" value="Tax Group (optional, overrides store default)" />
                        <select id="tax_group_id" name="tax_group_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm">
                            <option value="">— Use store default —</option>
                            @foreach ($taxGroups as $group)
                                <option value="{{ $group->id }}" @selected(old('tax_group_id') == $group->id)>{{ $group->name }} ({{ $group->totalRate() }}%)</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('tax_group_id')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-3">
                        <x-primary-button>Save</x-primary-button>
                        <a href="{{ route('categories.index') }}" class="text-sm text-ink-muted hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
