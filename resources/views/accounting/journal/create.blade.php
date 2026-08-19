<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">New Manual Journal Entry</h2>
    </x-slot>

    <script>
        function journalForm() {
            return {
                lines: [
                    { account: '', debit: 0, credit: 0 },
                    { account: '', debit: 0, credit: 0 },
                ],
                addLine() {
                    this.lines.push({ account: '', debit: 0, credit: 0 });
                },
                removeLine(index) {
                    this.lines.splice(index, 1);
                },
                get totalDebit() {
                    return this.lines.reduce((sum, l) => sum + (Number(l.debit) || 0), 0);
                },
                get totalCredit() {
                    return this.lines.reduce((sum, l) => sum + (Number(l.credit) || 0), 0);
                },
                get isBalanced() {
                    return Math.abs(this.totalDebit - this.totalCredit) < 0.01 && this.totalDebit > 0;
                },
                money(v) { return Number(v || 0).toFixed(2); },
            };
        }
    </script>

    <div class="max-w-3xl mx-auto">
        <div class="bg-surface-raised border border-border rounded-lg p-6" x-data="journalForm()">
            <form method="POST" action="{{ route('accounting.journal.store') }}" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="entry_date" value="Date" />
                        <x-text-input id="entry_date" name="entry_date" type="date" class="mt-1 block w-full" value="{{ old('entry_date', now()->format('Y-m-d')) }}" required />
                        <x-input-error :messages="$errors->get('entry_date')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="description" value="Description" />
                        <x-text-input id="description" name="description" type="text" class="mt-1 block w-full" value="{{ old('description') }}" required />
                        <x-input-error :messages="$errors->get('description')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <x-input-label value="Lines" class="!mb-0" />
                        <button type="button" @click="addLine()" class="text-sm text-accent-400 hover:text-accent-300 hover:underline">+ Add line</button>
                    </div>

                    <div class="space-y-2">
                        <template x-for="(line, index) in lines" :key="index">
                            <div class="flex items-center gap-2">
                                <select :name="'lines['+index+'][account]'" x-model="line.account" class="flex-1 text-sm bg-surface-hover border-border-strong text-ink rounded-md">
                                    <option value="">Select account</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->code }}">{{ $acc->code }} — {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                                <input type="number" min="0" step="0.01" :name="'lines['+index+'][debit]'" x-model.number="line.debit" placeholder="Debit" class="w-28 text-sm bg-surface-hover border-border-strong text-ink rounded-md">
                                <input type="number" min="0" step="0.01" :name="'lines['+index+'][credit]'" x-model.number="line.credit" placeholder="Credit" class="w-28 text-sm bg-surface-hover border-border-strong text-ink rounded-md">
                                <button type="button" @click="removeLine(index)" class="text-red-400 text-sm">&times;</button>
                            </div>
                        </template>
                    </div>
                    <x-input-error :messages="$errors->get('lines')" class="mt-2" />
                </div>

                <div class="flex justify-between items-center text-sm">
                    <span class="text-ink-muted">Debit: <span class="text-ink font-medium" x-text="money(totalDebit)"></span> &nbsp; Credit: <span class="text-ink font-medium" x-text="money(totalCredit)"></span></span>
                    <span :class="isBalanced ? 'text-emerald-400' : 'text-red-400'" x-text="isBalanced ? 'Balanced' : 'Not balanced'"></span>
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Post Entry</x-primary-button>
                    <a href="{{ route('accounting.journal.index') }}" class="text-sm text-ink-muted hover:underline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
