<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Warranty — {{ $warranty->product->name }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash-messages />

            <div class="bg-surface-raised border border-border rounded-lg p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><div class="text-ink-muted">Customer</div><div class="font-medium">{{ $warranty->customer->name }}</div></div>
                <div><div class="text-ink-muted">Sale</div><div class="font-medium">{{ $warranty->saleItem->sale->invoice_number }}</div></div>
                <div><div class="text-ink-muted">Period</div><div class="font-medium">{{ $warranty->start_date->format('Y-m-d') }} to {{ $warranty->end_date->format('Y-m-d') }}</div></div>
                <div><div class="text-ink-muted">Status</div><div class="font-medium">{{ ucfirst($warranty->status) }}</div></div>
            </div>

            @if ($warranty->status === 'active')
                <div class="bg-surface-raised border border-border rounded-lg p-6">
                    <h3 class="font-semibold text-ink mb-3">File a Claim</h3>
                    <form action="{{ route('warranties.claims.store', $warranty) }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <x-input-label for="claim_date" value="Claim Date" />
                            <x-text-input id="claim_date" name="claim_date" type="date" class="mt-1 block w-full" value="{{ now()->format('Y-m-d') }}" required />
                        </div>
                        <div>
                            <x-input-label for="issue_description" value="Issue Description" />
                            <textarea id="issue_description" name="issue_description" rows="3" class="mt-1 block w-full bg-surface-hover border-border-strong text-ink rounded-md shadow-sm" required></textarea>
                        </div>
                        <x-primary-button>File Claim</x-primary-button>
                    </form>
                </div>
            @endif

            <div class="bg-surface-raised border border-border rounded-lg p-6">
                <h3 class="font-semibold text-ink mb-3">Claims</h3>
                @forelse ($warranty->claims as $claim)
                    <div class="border-b border-border last:border-0 py-3 text-sm">
                        <div class="flex justify-between">
                            <span class="font-medium">{{ $claim->claim_date->format('Y-m-d') }}</span>
                            <span @class([
                                'px-2 py-0.5 rounded-full text-xs',
                                'bg-amber-500/10 text-amber-400' => $claim->status === 'open',
                                'bg-sky-500/10 text-sky-400' => $claim->status === 'in_progress',
                                'bg-emerald-500/10 text-emerald-400' => $claim->status === 'resolved',
                                'bg-red-500/10 text-red-400' => $claim->status === 'rejected',
                            ])>{{ ucfirst(str_replace('_', ' ', $claim->status)) }}</span>
                        </div>
                        <p class="text-ink-muted mt-1">{{ $claim->issue_description }}</p>
                        @if ($claim->resolution)
                            <p class="text-ink-muted mt-1"><strong>Resolution:</strong> {{ $claim->resolution }}</p>
                        @endif

                        <form action="{{ route('warranty-claims.update', $claim) }}" method="POST" class="mt-2 flex items-center gap-2">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="text-xs bg-surface-hover border-border-strong text-ink rounded-md">
                                @foreach (['open', 'in_progress', 'resolved', 'rejected'] as $status)
                                    <option value="{{ $status }}" @selected($claim->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                                @endforeach
                            </select>
                            <input type="text" name="resolution" placeholder="Resolution notes" value="{{ $claim->resolution }}" class="text-xs bg-surface-hover border-border-strong text-ink rounded-md flex-1">
                            <button type="submit" class="text-xs bg-accent-500 text-zinc-950 rounded-md px-3 py-1 hover:bg-accent-400">Update</button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-ink-muted">No claims filed.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
