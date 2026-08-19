<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Edit Discount Rule</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-surface-raised border border-border rounded-lg p-6">
                <form method="POST" action="{{ route('discount-rules.update', $discountRule) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    @include('discount-rules._form')
                    <div class="flex items-center gap-3">
                        <x-primary-button>Save Rule</x-primary-button>
                        <a href="{{ route('discount-rules.index') }}" class="text-sm text-ink-muted hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
