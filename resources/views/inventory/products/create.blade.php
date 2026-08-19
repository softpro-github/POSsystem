<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Add Product</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-surface-raised border border-border rounded-lg p-6">
                <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @include('inventory.products._form', ['product' => null])
                    <div class="flex items-center gap-3">
                        <x-primary-button>Save Product</x-primary-button>
                        <a href="{{ route('products.index') }}" class="text-sm text-ink-muted hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
