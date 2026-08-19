<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HeldOrderController extends Controller
{
    public function index(): View
    {
        $heldOrders = Sale::with(['customer', 'user', 'items.product'])
            ->where('status', 'held')
            ->latest()
            ->get();

        return view('pos.held', compact('heldOrders'));
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        if ($sale->status !== 'held') {
            return back()->with('error', 'Only held orders can be discarded.');
        }

        $sale->delete();

        return redirect()->route('pos.held')->with('success', 'Held order discarded.');
    }
}
