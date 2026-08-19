<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SaleReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $store = current_store();
        $storeId = $store?->id;

        $stats = $this->todaysStats($storeId);

        $recentSales = Sale::with(['customer', 'user'])
            ->where('status', 'completed')
            ->where('store_id', $storeId)
            ->latest('sold_at')
            ->take(10)
            ->get();

        $lowStockProducts = Product::lowStock()
            ->where('is_active', true)
            ->get()
            ->sortBy(fn ($product) => $product->quantity)
            ->take(5)
            ->values();

        $periodDays = (int) $request->input('period', 14);
        $periodDays = in_array($periodDays, [7, 14, 30]) ? $periodDays : 14;

        $periodStart = now()->copy()->subDays($periodDays - 1)->startOfDay();
        $prevPeriodStart = $periodStart->copy()->subDays($periodDays)->startOfDay();
        $prevPeriodEnd = $periodStart->copy()->subSecond();

        $salesTrend = $this->dailySeries($periodDays, $periodStart, fn ($from, $to) => (float) Sale::where('status', 'completed')
            ->where('store_id', $storeId)
            ->whereBetween('sold_at', [$from, $to])
            ->sum('total_amount'));

        $categoryMix = SaleItem::selectRaw('COALESCE(categories.name, "Uncategorized") as category, SUM(sale_items.subtotal) as total')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('sales.status', 'completed')
            ->where('sales.store_id', $storeId)
            ->where('sales.sold_at', '>=', $periodStart)
            ->groupBy('category')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => ['category' => $row->category, 'total' => (float) $row->total]);

        $thisPeriod = $this->periodStats($storeId, $periodStart, now());
        $prevPeriod = $this->periodStats($storeId, $prevPeriodStart, $prevPeriodEnd);

        $comparison = [
            'this' => $this->dailySeriesByDayIndex($storeId, $periodDays, $periodStart, now()),
            'previous' => $this->dailySeriesByDayIndex($storeId, $periodDays, $prevPeriodStart, $prevPeriodEnd),
            'this_totals' => $thisPeriod,
            'previous_totals' => $prevPeriod,
        ];

        $kpiSparklines = [
            'transactions' => $this->dailySeries(7, now()->copy()->subDays(6)->startOfDay(), fn ($from, $to) => Sale::where('status', 'completed')
                ->where('store_id', $storeId)->whereBetween('sold_at', [$from, $to])->count()),
            'new_customers' => $this->dailySeries(7, now()->copy()->subDays(6)->startOfDay(), fn ($from, $to) => Customer::whereBetween('created_at', [$from, $to])->count()),
            'refunds' => $this->dailySeries(7, now()->copy()->subDays(6)->startOfDay(), fn ($from, $to) => (float) SaleReturn::whereHas('sale', fn ($q) => $q->where('store_id', $storeId))
                ->whereBetween('created_at', [$from, $to])->sum('total_refunded')),
        ];

        return view('dashboard', compact(
            'stats', 'recentSales', 'lowStockProducts',
            'salesTrend', 'categoryMix', 'comparison', 'kpiSparklines', 'periodDays'
        ));
    }

    public function todaySummary(Request $request)
    {
        $storeId = current_store()?->id;

        $stats = $this->todaysStats($storeId);

        $recentSales = Sale::with(['customer', 'user'])
            ->where('status', 'completed')
            ->where('store_id', $storeId)
            ->latest('sold_at')
            ->take(5)
            ->get();

        return view('dashboard._today_summary', compact('stats', 'recentSales'));
    }

    private function todaysStats(?int $storeId): array
    {
        $todaysSales = Sale::whereDate('sold_at', today())->where('status', 'completed')->where('store_id', $storeId);

        return [
            'todays_sales_total' => (clone $todaysSales)->sum('total_amount'),
            'todays_sales_count' => (clone $todaysSales)->count(),
            'total_products' => Product::where('is_active', true)->count(),
            'low_stock_count' => Product::lowStock()->where('is_active', true)->count(),
        ];
    }

    /**
     * @return array<int, array{date: string, value: float}>
     */
    private function dailySeries(int $days, Carbon $start, \Closure $valueForDay): array
    {
        $series = [];

        for ($i = 0; $i < $days; $i++) {
            $dayStart = $start->copy()->addDays($i)->startOfDay();
            $dayEnd = $dayStart->copy()->endOfDay();

            $series[] = [
                'date' => $dayStart->toDateString(),
                'value' => $valueForDay($dayStart, $dayEnd),
            ];
        }

        return $series;
    }

    /**
     * Same shape as dailySeries() but keyed by day-index (1..N) rather than
     * calendar date, so "this period" and "previous period" can be overlaid
     * on one chart aligned by day-of-period instead of by date.
     */
    private function dailySeriesByDayIndex(?int $storeId, int $days, Carbon $start, Carbon $end): array
    {
        $series = [];

        for ($i = 0; $i < $days; $i++) {
            $dayStart = $start->copy()->addDays($i)->startOfDay();
            if ($dayStart->gt($end)) {
                break;
            }
            $dayEnd = $dayStart->copy()->endOfDay();

            $total = (float) Sale::where('status', 'completed')
                ->where('store_id', $storeId)
                ->whereBetween('sold_at', [$dayStart, $dayEnd])
                ->sum('total_amount');

            $series[] = ['day' => $i + 1, 'value' => $total];
        }

        return $series;
    }

    private function periodStats(?int $storeId, Carbon $from, Carbon $to): array
    {
        $sales = Sale::where('status', 'completed')->where('store_id', $storeId)->whereBetween('sold_at', [$from, $to])->get();
        $items = SaleItem::with('product')->whereIn('sale_id', $sales->pluck('id'))->get();

        $netSales = (float) $sales->sum('total_amount');
        $cost = (float) $items->sum(fn ($item) => $item->quantity * (float) ($item->product?->cost_price ?? 0));
        $margin = $netSales > 0 ? round((($netSales - $cost) / $netSales) * 100, 1) : 0.0;
        $basket = $sales->count() > 0 ? round($netSales / $sales->count(), 2) : 0.0;

        return [
            'net_sales' => $netSales,
            'margin' => $margin,
            'basket' => $basket,
            'count' => $sales->count(),
        ];
    }
}
