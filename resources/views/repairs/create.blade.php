<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">New Repair Ticket</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-surface-raised border border-border rounded-lg p-6">
                <form method="POST" action="{{ route('repair-tickets.store') }}" class="space-y-4">
                    @csrf
                    @include('repairs._form', ['repairTicket' => null])
                    <div class="flex items-center gap-3">
                        <x-primary-button>Create Ticket</x-primary-button>
                        <a href="{{ route('repair-tickets.index') }}" class="text-sm text-ink-muted hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
