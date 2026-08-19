<?php

namespace App\Http\Controllers\Suppliers;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\Supplier;
use App\Services\JournalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierPaymentController extends Controller
{
    public function __construct(private JournalService $journalService) {}

    public function store(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.max($supplier->balance, 0.01)],
            'method' => ['required', 'in:cash,card,transfer'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $shift = Shift::where('user_id', $request->user()->id)->where('status', 'open')->first();

        DB::transaction(function () use ($validated, $supplier, $request, $shift) {
            $payment = $supplier->payments()->create([
                'user_id' => $request->user()->id,
                'amount' => $validated['amount'],
                'method' => $validated['method'],
                'paid_at' => now(),
                'shift_id' => $validated['method'] === 'cash' ? $shift?->id : null,
                'note' => $validated['note'] ?? null,
            ]);

            $supplier->decrement('balance', $validated['amount']);

            $cashAccount = $validated['method'] === 'cash' ? '1000' : '1010';

            $this->journalService->postEntry(
                lines: [
                    ['account' => '2000', 'debit' => $validated['amount'], 'credit' => 0],
                    ['account' => $cashAccount, 'debit' => 0, 'credit' => $validated['amount']],
                ],
                description: 'Payment to supplier: '.$supplier->name,
                reference: $payment,
            );
        });

        return redirect()->route('suppliers.show', $supplier)->with('success', 'Payment recorded.');
    }
}
