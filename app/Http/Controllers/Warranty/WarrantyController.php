<?php

namespace App\Http\Controllers\Warranty;

use App\Http\Controllers\Controller;
use App\Models\SaleItem;
use App\Models\Warranty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WarrantyController extends Controller
{
    public function index(Request $request): View
    {
        $warranties = Warranty::with(['product', 'customer'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('warranty.index', compact('warranties'));
    }

    public function create(): View
    {
        $eligibleItems = SaleItem::with(['product', 'sale.customer'])
            ->whereDoesntHave('warranty')
            ->whereHas('sale', fn ($q) => $q->where('status', 'completed')->whereNotNull('customer_id'))
            ->latest('id')
            ->take(50)
            ->get();

        return view('warranty.create', compact('eligibleItems'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sale_item_id' => ['required', 'exists:sale_items,id'],
            'warranty_period_months' => ['required', 'integer', 'min:1', 'max:120'],
        ]);

        $saleItem = SaleItem::with('sale')->findOrFail($validated['sale_item_id']);

        if (! $saleItem->sale->customer_id) {
            return back()->with('error', 'This sale has no customer attached; warranty requires a registered customer.');
        }

        $startDate = $saleItem->sale->sold_at?->toDateString() ?? now()->toDateString();
        $endDate = now()->parse($startDate)->addMonths((int) $validated['warranty_period_months'])->toDateString();

        $warranty = Warranty::create([
            'sale_item_id' => $saleItem->id,
            'product_id' => $saleItem->product_id,
            'customer_id' => $saleItem->sale->customer_id,
            'warranty_period_months' => $validated['warranty_period_months'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => 'active',
        ]);

        return redirect()->route('warranties.show', $warranty)->with('success', 'Warranty registered.');
    }

    public function show(Warranty $warranty): View
    {
        $warranty->load(['product', 'customer', 'saleItem.sale', 'claims.handledBy']);

        return view('warranty.show', compact('warranty'));
    }
}
