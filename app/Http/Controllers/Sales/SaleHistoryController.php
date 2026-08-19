<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SaleHistoryController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(Request $request): View
    {
        $sales = Sale::with(['customer', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where('invoice_number', 'like', '%'.$request->string('search').'%');
            })
            ->when($request->filled('from'), fn ($q) => $q->whereDate('sold_at', '>=', $request->date('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('sold_at', '<=', $request->date('to')))
            ->latest('sold_at')
            ->paginate(20)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function show(Sale $sale): View
    {
        $sale->load(['items.product', 'items.productSerial', 'payments', 'customer', 'user', 'returns.returnReason']);

        return view('sales.show', compact('sale'));
    }

    public function receipt(Sale $sale): View
    {
        $sale->load(['items.product', 'items.productSerial', 'payments', 'customer', 'user']);

        return view('sales.receipt', compact('sale'));
    }

    public function void(Request $request, Sale $sale): RedirectResponse
    {
        if ($sale->status !== 'completed') {
            return back()->with('error', 'Only completed sales can be voided.');
        }

        $store = $sale->store ?? current_store();
        if (! $store) {
            return back()->with('error', 'This sale has no store assigned and none is currently selected.');
        }

        $sale->load('items.product', 'items.productSerial');

        foreach ($sale->items as $item) {
            $this->stockService->returnStock(
                $item->product,
                $store,
                $item->quantity,
                $request->user(),
                $item,
                $item->productSerial,
                'Sale voided: '.$sale->invoice_number,
            );
        }

        $sale->update(['status' => 'voided']);

        return redirect()->route('sales.show', $sale)->with('success', 'Sale voided and stock restored.');
    }
}
