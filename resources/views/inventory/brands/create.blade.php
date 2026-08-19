<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Add Brand</h2>
    </x-slot>

    <div class="max-w-lg mx-auto">
        <div class="bg-surface-raised border border-border rounded-lg p-6">
            <form method="POST" action="{{ route('brands.store') }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name') }}" required autofocus />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div class="flex items-center gap-3">
                    <x-primary-button>Save</x-primary-button>
                    <a href="{{ route('brands.index') }}" class="text-sm text-ink-muted hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
