<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Store;
use App\Models\StockTransfer;
use App\Models\User;
use App\Notifications\StockTransferAwaitingReceipt;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(): View
    {
        $transfers = StockTransfer::with('fromStore', 'toStore', 'user')->latest()->paginate(20);

        return view('inventory.transfers.index', compact('transfers'));
    }

    public function create(): View
    {
        $stores = Store::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku']);

        return view('inventory.transfers.create', compact('stores', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'items' => json_decode($request->input('items_json', '[]'), true) ?? [],
        ]);

        $validated = $request->validate([
            'from_store_id' => ['required', 'exists:stores,id'],
            'to_store_id' => ['required', 'different:from_store_id', 'exists:stores,id'],
            'notes' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        $transfer = DB::transaction(function () use ($validated, $request) {
            $transfer = StockTransfer::create([
                'from_store_id' => $validated['from_store_id'],
                'to_store_id' => $validated['to_store_id'],
                'user_id' => $request->user()->id,
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return $transfer;
        });

        Notification::send(
            User::where('store_id', $transfer->to_store_id)->permission('manage stock')->get(),
            new StockTransferAwaitingReceipt($transfer),
        );

        return redirect()->route('transfers.show', $transfer)->with('success', 'Transfer created — stock moves once the destination confirms receipt.');
    }

    public function show(StockTransfer $transfer): View
    {
        $transfer->load('fromStore', 'toStore', 'user', 'items.product');

        return view('inventory.transfers.show', compact('transfer'));
    }

    public function receive(Request $request, StockTransfer $transfer): RedirectResponse
    {
        if ($transfer->status !== 'pending') {
            return back()->with('error', 'This transfer has already been received.');
        }

        $transfer->load('items.product', 'fromStore', 'toStore');

        DB::transaction(function () use ($transfer, $request) {
            foreach ($transfer->items as $item) {
                $this->stockService->transfer(
                    $item->product,
                    $transfer->fromStore,
                    $transfer->toStore,
                    $item->quantity,
                    $request->user(),
                    $transfer,
                );
            }

            $transfer->update(['status' => 'received', 'received_at' => now()]);
        });

        return redirect()->route('transfers.show', $transfer)->with('success', 'Transfer received — stock updated at both stores.');
    }
}
