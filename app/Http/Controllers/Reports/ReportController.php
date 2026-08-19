<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\RepairTicket;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warranty;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function sales(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);

        $sales = Sale::with(['customer', 'user'])
            ->where('status', 'completed')
            ->whereBetween('sold_at', [$from->startOfDay(), $to->endOfDay()])
            ->latest('sold_at')
            ->get();

        $summary = [
            'count' => $sales->count(),
            'subtotal' => $sales->sum('subtotal'),
            'discount_amount' => $sales->sum('discount_amount'),
            'tax_amount' => $sales->sum('tax_amount'),
            'total_amount' => $sales->sum('total_amount'),
        ];

        return view('reports.sales', compact('sales', 'summary', 'from', 'to'));
    }

    public function inventory(): View
    {
        $store = current_store();
        $products = Product::with([
                'category',
                'productStores' => fn ($q) => $q->when($store, fn ($q) => $q->where('store_id', $store->id)),
            ])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $summary = [
            'total_products' => $products->count(),
            'total_units' => $products->sum('quantity'),
            'total_cost_value' => $products->sum(fn ($p) => $p->quantity * $p->cost_price),
            'total_retail_value' => $products->sum(fn ($p) => $p->quantity * $p->selling_price),
            'low_stock_count' => $products->filter(fn ($p) => $p->quantity <= $p->reorder_level)->count(),
        ];

        return view('reports.inventory', compact('products', 'summary'));
    }

    public function profit(Request $request): View
    {
        [$from, $to] = $this->dateRange($request);

        $items = SaleItem::with('product')
            ->whereHas('sale', function ($q) use ($from, $to) {
                $q->where('status', 'completed')->whereBetween('sold_at', [$from->startOfDay(), $to->endOfDay()]);
            })
            ->get();

        $rows = $items->groupBy('product_id')->map(function ($group) {
            $product = $group->first()->product;
            $quantity = $group->sum('quantity');
            $revenue = $group->sum('subtotal');
            $cost = $quantity * $product->cost_price;

            return [
                'product' => $product,
                'quantity' => $quantity,
                'revenue' => $revenue,
                'cost' => $cost,
                'profit' => $revenue - $cost,
            ];
        })->values();

        $summary = [
            'revenue' => $rows->sum('revenue'),
            'cost' => $rows->sum('cost'),
            'profit' => $rows->sum('profit'),
        ];

        return view('reports.profit', compact('rows', 'summary', 'from', 'to'));
    }

    public function warranty(): View
    {
        $warranties = Warranty::with(['product', 'customer'])->latest()->get();

        $summary = [
            'active' => $warranties->where('status', 'active')->count(),
            'expired' => $warranties->where('status', 'expired')->count(),
            'voided' => $warranties->where('status', 'voided')->count(),
            'expiring_soon' => $warranties->where('status', 'active')->filter(fn ($w) => $w->end_date->isBetween(now(), now()->addDays(30)))->count(),
        ];

        return view('reports.warranty', compact('warranties', 'summary'));
    }

    public function repair(): View
    {
        $tickets = RepairTicket::with(['customer', 'technician'])->latest()->get();

        $summary = $tickets->groupBy('status')->map->count();

        return view('reports.repair', compact('tickets', 'summary'));
    }

    private function dateRange(Request $request): array
    {
        $from = $request->filled('from') ? now()->parse($request->string('from')) : now()->startOfMonth();
        $to = $request->filled('to') ? now()->parse($request->string('to')) : now();

        return [$from, $to];
    }
}
