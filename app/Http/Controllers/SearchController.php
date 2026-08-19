<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $empty = ['products' => [], 'customers' => [], 'suppliers' => [], 'sales' => []];

        if (mb_strlen($q) < 2) {
            return response()->json($empty);
        }

        $like = '%'.$q.'%';
        $user = $request->user();
        $results = $empty;

        if ($user->canAny(['manage products', 'access pos'])) {
            $results['products'] = Product::query()
                ->where(fn ($query) => $query->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhere('barcode', 'like', $like))
                ->limit(5)
                ->get(['id', 'name', 'sku'])
                ->map(fn ($p) => ['id' => $p->id, 'title' => $p->name, 'subtitle' => $p->sku, 'url' => route('products.edit', $p)])
                ->values();
        }

        if ($user->can('view customers')) {
            $results['customers'] = Customer::query()
                ->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('phone', 'like', $like))
                ->limit(5)
                ->get(['id', 'name', 'phone'])
                ->map(fn ($c) => ['id' => $c->id, 'title' => $c->name, 'subtitle' => $c->phone, 'url' => route('customers.show', $c)])
                ->values();
        }

        if ($user->can('manage suppliers')) {
            $results['suppliers'] = Supplier::query()
                ->where(fn ($query) => $query->where('name', 'like', $like)->orWhere('phone', 'like', $like))
                ->limit(5)
                ->get(['id', 'name', 'phone'])
                ->map(fn ($s) => ['id' => $s->id, 'title' => $s->name, 'subtitle' => $s->phone, 'url' => route('suppliers.show', $s)])
                ->values();
        }

        if ($user->can('view sales')) {
            $sales = Sale::query()->where('invoice_number', 'like', $like);

            if ($store = current_store()) {
                $sales->where('store_id', $store->id);
            }

            $results['sales'] = $sales->limit(5)
                ->get(['id', 'invoice_number', 'total_amount'])
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'title' => $s->invoice_number,
                    'subtitle' => '₦'.number_format((float) $s->total_amount, 2),
                    'url' => route('sales.show', $s),
                ])
                ->values();
        }

        return response()->json($results);
    }
}
