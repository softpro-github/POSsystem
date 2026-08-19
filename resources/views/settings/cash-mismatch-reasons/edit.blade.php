<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Edit Cash Mismatch Reason</h2>
    </x-slot>

    <div class="max-w-lg mx-auto">
        <div class="bg-surface-raised border border-border rounded-lg p-6">
            <form method="POST" action="{{ route('cash-mismatch-reasons.update', $cashMismatchReason) }}" class="space-y-4">
                @csrf
                @method('PUT')
                @include('settings.cash-mismatch-reasons._form')
                <div class="flex items-center gap-3">
                    <x-primary-button>Save</x-primary-button>
                    <a href="{{ route('cash-mismatch-reasons.index') }}" class="text-sm text-ink-muted hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
