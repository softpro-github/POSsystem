<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Edit Repair Ticket {{ $repairTicket->ticket_number }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-surface-raised border border-border rounded-lg p-6">
                <form method="POST" action="{{ route('repair-tickets.update', $repairTicket) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    @include('repairs._form')
                    <div class="flex items-center gap-3">
                        <x-primary-button>Save Changes</x-primary-button>
                        <a href="{{ route('repair-tickets.show', $repairTicket) }}" class="text-sm text-ink-muted hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
