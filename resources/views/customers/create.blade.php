<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Add Customer</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-surface-raised border border-border rounded-lg p-6">
                <form method="POST" action="{{ route('customers.store') }}" class="space-y-4">
                    @csrf
                    @include('customers._form', ['customer' => null])
                    <div class="flex items-center gap-3">
                        <x-primary-button>Save Customer</x-primary-button>
                        <a href="{{ route('customers.index') }}" class="text-sm text-ink-muted hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
