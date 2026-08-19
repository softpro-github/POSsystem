<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\AdjustmentReason;
use App\Models\Product;
use App\Models\StockReconciliation;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StockReconciliationController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(): View
    {
        $reconciliations = StockReconciliation::with('store', 'user')->latest('completed_at')->paginate(20);

        return view('inventory.reconciliations.index', compact('reconciliations'));
    }

    public function create(): View
    {
        $store = current_store();
        if (! $store) {
            abort(400, 'No store assigned to your account.');
        }

        $products = Product::with(['productStores' => fn ($q) => $q->where('store_id', $store->id)])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $adjustmentReasons = AdjustmentReason::where('is_active', true)->orderBy('name')->get();

        return view('inventory.reconciliations.create', compact('products', 'adjustmentReasons'));
    }

    public function store(Request $request): RedirectResponse
    {
        $store = current_store();
        if (! $store) {
            return back()->with('error', 'No store assigned to your account.');
        }

        $validated = $request->validate([
            'adjustment_reason_id' => ['required', 'exists:adjustment_reasons,id'],
            'notes' => ['nullable', 'string', 'max:500'],
            'counts' => ['required', 'array'],
            'counts.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $reason = AdjustmentReason::findOrFail($validated['adjustment_reason_id']);
        $changedCount = 0;

        DB::transaction(function () use ($validated, $store, $request, $reason, &$changedCount) {
            $reconciliation = StockReconciliation::create([
                'store_id' => $store->id,
                'user_id' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
                'completed_at' => now(),
            ]);

            foreach ($validated['counts'] as $productId => $counted) {
                if ($counted === null || $counted === '') {
                    continue;
                }

                $product = Product::find($productId);
                if (! $product) {
                    continue;
                }

                $book = $product->stockAt($store)?->quantity ?? 0;
                $delta = (int) $counted - $book;

                if ($delta === 0) {
                    continue;
                }

                $this->stockService->adjust(
                    $product,
                    $store,
                    $delta,
                    $request->user(),
                    'Stock reconciliation #'.$reconciliation->id,
                    $reason,
                    $reconciliation,
                );

                $changedCount++;
            }

            if ($changedCount === 0) {
                throw ValidationException::withMessages(['counts' => 'No counted quantities differed from book stock — nothing to reconcile.']);
            }
        });

        return redirect()->route('reconciliations.index')->with('success', "Reconciliation complete — {$changedCount} product(s) adjusted.");
    }

    public function show(StockReconciliation $reconciliation): View
    {
        $reconciliation->load('store', 'user', 'stockMovements.product', 'stockMovements.adjustmentReason');

        return view('inventory.reconciliations.show', compact('reconciliation'));
    }
}
