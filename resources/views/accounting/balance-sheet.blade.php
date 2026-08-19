<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">Accounting</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @include('accounting._tabs')

            <form method="GET" class="flex flex-wrap items-center gap-3 bg-surface-raised border border-border rounded-lg p-4">
                <label class="text-sm text-ink-muted">As of
                    <input type="date" name="as_of" value="{{ $asOf->format('Y-m-d') }}" class="bg-surface-hover border-border-strong text-ink rounded-md shadow-sm text-sm ml-1">
                </label>
                <button type="submit" class="px-4 py-2 bg-surface-hover text-ink-muted rounded-md text-sm hover:bg-zinc-700">Apply</button>
            </form>

            <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
                <div class="px-4 py-3 border-b border-border font-semibold text-ink">Assets</div>
                <table class="w-full text-sm text-left">
                    <tbody>
                        @forelse ($assets as $row)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-2 px-4">{{ $row['account']->name }}</td>
                                <td class="py-2 px-4 text-right"><x-money :amount="$row['balance']" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 px-4 text-center text-ink-muted">No asset balances.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-border font-semibold">
                            <td class="py-2 px-4">Total Assets</td>
                            <td class="py-2 px-4 text-right"><x-money :amount="$totalAssets" /></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
                <div class="px-4 py-3 border-b border-border font-semibold text-ink">Liabilities</div>
                <table class="w-full text-sm text-left">
                    <tbody>
                        @forelse ($liabilities as $row)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-2 px-4">{{ $row['account']->name }}</td>
                                <td class="py-2 px-4 text-right"><x-money :amount="$row['balance']" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="py-4 px-4 text-center text-ink-muted">No liability balances.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-border font-semibold">
                            <td class="py-2 px-4">Total Liabilities</td>
                            <td class="py-2 px-4 text-right"><x-money :amount="$totalLiabilities" /></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg overflow-hidden">
                <div class="px-4 py-3 border-b border-border font-semibold text-ink">Equity</div>
                <table class="w-full text-sm text-left">
                    <tbody>
                        @forelse ($equity as $row)
                            <tr class="border-b border-border last:border-0">
                                <td class="py-2 px-4">{{ $row['account']->name }}</td>
                                <td class="py-2 px-4 text-right"><x-money :amount="$row['balance']" /></td>
                            </tr>
                        @empty
                        @endforelse
                        <tr class="border-b border-border last:border-0">
                            <td class="py-2 px-4">Current Earnings (unclosed)</td>
                            <td class="py-2 px-4 text-right"><x-money :amount="$currentEarnings" /></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-border font-semibold">
                            <td class="py-2 px-4">Total Equity</td>
                            <td class="py-2 px-4 text-right"><x-money :amount="$totalEquity" /></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="bg-surface-raised border border-border rounded-lg p-4 flex justify-between items-center">
                <span class="font-semibold text-ink">Assets = Liabilities + Equity</span>
                @php $diff = round($totalAssets - ($totalLiabilities + $totalEquity), 2); @endphp
                <span class="text-sm {{ $diff === 0.0 ? 'text-emerald-400' : 'text-red-400' }}">
                    {{ $diff === 0.0 ? 'Balanced' : 'Out of balance by '.number_format($diff, 2) }}
                </span>
            </div>
        </div>
    </div>
</x-app-layout>
