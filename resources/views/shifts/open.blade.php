<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Start Shift</h2>
    </x-slot>

    <div class="max-w-md mx-auto">
        <div class="bg-surface-raised border border-border rounded-lg p-6">
            <p class="text-sm text-ink-muted mb-4">Count the cash already in the till before your first sale — this is your opening float, the change you'll need for early customers.</p>

            <form method="POST" action="{{ route('shifts.open') }}" class="space-y-4">
                @csrf
                <div>
                    <x-input-label for="opening_float" value="Opening Float" />
                    <x-text-input id="opening_float" name="opening_float" type="number" step="0.01" min="0" class="mt-1 block w-full" value="{{ old('opening_float', 0) }}" required autofocus />
                    <x-input-error :messages="$errors->get('opening_float')" class="mt-2" />
                </div>

                @if ($terminals->isNotEmpty())
                    <div>
                        <x-input-label for="terminal_id" value="Terminal / Register" />
                        <select id="terminal_id" name="terminal_id" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required>
                            <option value="">Select terminal</option>
                            @foreach ($terminals as $terminal)
                                <option value="{{ $terminal->id }}" @selected(old('terminal_id') == $terminal->id)>{{ $terminal->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('terminal_id')" class="mt-2" />
                    </div>
                @endif

                <x-primary-button>Start Shift</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
