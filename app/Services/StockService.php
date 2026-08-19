<?php

namespace App\Services;

use App\Models\AdjustmentReason;
use App\Models\Product;
use App\Models\ProductSerial;
use App\Models\ProductStore;
use App\Models\PurchaseOrderItem;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\StockTransfer;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for stock changes: every quantity change is written
 * as a stock_movements row inside the same transaction that updates the
 * product_stores.quantity cache for the acting store, so the two never drift
 * apart. Every method operates on one store's stock — there is no
 * "global" quantity anymore, only per-store balances.
 */
class StockService
{
    public function receivePurchase(
        Product $product,
        Store $store,
        int $quantity,
        User $user,
        PurchaseOrderItem $purchaseOrderItem,
        array $serials = [],
        ?string $note = null,
    ): void {
        DB::transaction(function () use ($product, $store, $quantity, $user, $purchaseOrderItem, $serials, $note) {
            $stock = $this->lockStockRow($product, $store);
            $stock->increment('quantity', $quantity);

            StockMovement::create([
                'product_id' => $product->id,
                'store_id' => $store->id,
                'type' => 'purchase',
                'quantity' => $quantity,
                'balance_after' => $stock->quantity,
                'reference_type' => PurchaseOrderItem::class,
                'reference_id' => $purchaseOrderItem->id,
                'user_id' => $user->id,
                'note' => $note,
            ]);

            foreach ($serials as $imeiSerial) {
                ProductSerial::create([
                    'product_id' => $product->id,
                    'imei_serial' => $imeiSerial,
                    'status' => 'in_stock',
                    'purchase_order_item_id' => $purchaseOrderItem->id,
                ]);
            }
        });
    }

    public function sell(
        Product $product,
        Store $store,
        int $quantity,
        User $user,
        SaleItem $saleItem,
        ?ProductSerial $serial = null,
        ?string $note = null,
    ): void {
        DB::transaction(function () use ($product, $store, $quantity, $user, $saleItem, $serial, $note) {
            $stock = $this->lockStockRow($product, $store);
            $stock->decrement('quantity', $quantity);

            StockMovement::create([
                'product_id' => $product->id,
                'store_id' => $store->id,
                'type' => 'sale',
                'quantity' => -$quantity,
                'balance_after' => $stock->quantity,
                'reference_type' => SaleItem::class,
                'reference_id' => $saleItem->id,
                'user_id' => $user->id,
                'note' => $note,
            ]);

            $serial?->update([
                'status' => 'sold',
                'sale_item_id' => $saleItem->id,
                'sold_at' => now(),
            ]);
        });
    }

    public function returnStock(
        Product $product,
        Store $store,
        int $quantity,
        User $user,
        ?SaleItem $saleItem = null,
        ?ProductSerial $serial = null,
        ?string $note = null,
    ): void {
        DB::transaction(function () use ($product, $store, $quantity, $user, $saleItem, $serial, $note) {
            $stock = $this->lockStockRow($product, $store);
            $stock->increment('quantity', $quantity);

            StockMovement::create([
                'product_id' => $product->id,
                'store_id' => $store->id,
                'type' => 'return',
                'quantity' => $quantity,
                'balance_after' => $stock->quantity,
                'reference_type' => $saleItem ? SaleItem::class : null,
                'reference_id' => $saleItem?->id,
                'user_id' => $user->id,
                'note' => $note,
            ]);

            $serial?->update(['status' => 'returned']);
        });
    }

    public function returnPurchase(
        Product $product,
        Store $store,
        int $quantity,
        User $user,
        PurchaseOrderItem $purchaseOrderItem,
        ?string $note = null,
    ): void {
        DB::transaction(function () use ($product, $store, $quantity, $user, $purchaseOrderItem, $note) {
            $stock = $this->lockStockRow($product, $store);

            if ($stock->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Cannot return {$quantity} of {$product->name} to the supplier — only {$stock->quantity} in stock at {$store->name} (some may already be sold).",
                ]);
            }

            $stock->decrement('quantity', $quantity);

            StockMovement::create([
                'product_id' => $product->id,
                'store_id' => $store->id,
                'type' => 'purchase_return',
                'quantity' => -$quantity,
                'balance_after' => $stock->quantity,
                'reference_type' => PurchaseOrderItem::class,
                'reference_id' => $purchaseOrderItem->id,
                'user_id' => $user->id,
                'note' => $note,
            ]);
        });
    }

    public function adjust(
        Product $product,
        Store $store,
        int $delta,
        User $user,
        string $note,
        ?AdjustmentReason $reason = null,
        ?Model $reference = null,
    ): void {
        DB::transaction(function () use ($product, $store, $delta, $user, $note, $reason, $reference) {
            $stock = $this->lockStockRow($product, $store);
            $stock->increment('quantity', $delta);

            StockMovement::create([
                'product_id' => $product->id,
                'store_id' => $store->id,
                'type' => 'adjustment',
                'adjustment_reason_id' => $reason?->id,
                'quantity' => $delta,
                'balance_after' => $stock->quantity,
                'reference_type' => $reference?->getMorphClass(),
                'reference_id' => $reference?->getKey(),
                'user_id' => $user->id,
                'note' => $note,
            ]);

            activity()
                ->causedBy($user)
                ->performedOn($product)
                ->withProperties(['delta' => $delta, 'store_id' => $store->id, 'note' => $note, 'balance_after' => $stock->quantity])
                ->log('stock adjusted');
        });
    }

    /**
     * Moves stock between two stores' balances atomically, writing a
     * transfer_out row at the source and a transfer_in row at the
     * destination, both linked back to the same StockTransfer.
     */
    public function transfer(Product $product, Store $from, Store $to, int $quantity, User $user, StockTransfer $transfer): void
    {
        DB::transaction(function () use ($product, $from, $to, $quantity, $user, $transfer) {
            $fromStock = $this->lockStockRow($product, $from);

            if ($fromStock->quantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Cannot transfer {$quantity} of {$product->name} — only {$fromStock->quantity} in stock at {$from->name}.",
                ]);
            }

            $fromStock->decrement('quantity', $quantity);

            StockMovement::create([
                'product_id' => $product->id,
                'store_id' => $from->id,
                'type' => 'transfer_out',
                'quantity' => -$quantity,
                'balance_after' => $fromStock->quantity,
                'reference_type' => StockTransfer::class,
                'reference_id' => $transfer->id,
                'user_id' => $user->id,
                'note' => "Transfer #{$transfer->id} to {$to->name}",
            ]);

            $toStock = $this->lockStockRow($product, $to);
            $toStock->increment('quantity', $quantity);

            StockMovement::create([
                'product_id' => $product->id,
                'store_id' => $to->id,
                'type' => 'transfer_in',
                'quantity' => $quantity,
                'balance_after' => $toStock->quantity,
                'reference_type' => StockTransfer::class,
                'reference_id' => $transfer->id,
                'user_id' => $user->id,
                'note' => "Transfer #{$transfer->id} from {$from->name}",
            ]);
        });
    }

    private function lockStockRow(Product $product, Store $store): ProductStore
    {
        return ProductStore::lockForUpdate()->firstOrCreate(
            ['product_id' => $product->id, 'store_id' => $store->id],
            ['quantity' => 0],
        );
    }
}
