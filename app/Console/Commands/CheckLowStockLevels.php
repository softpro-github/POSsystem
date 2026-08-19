<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Notifications\LowStockAlert;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CheckLowStockLevels extends Command
{
    protected $signature = 'stock:check-low-levels';

    protected $description = 'Notify store staff about products at or below their reorder level, deduped to at most one alert per product+store per 24h.';

    public function handle(): int
    {
        $stores = Store::where('is_active', true)->get();

        foreach ($stores as $store) {
            $lowStockProducts = Product::where('is_active', true)->lowStock($store)->get();

            if ($lowStockProducts->isEmpty()) {
                continue;
            }

            $recipients = User::where('store_id', $store->id)->permission('manage stock')->get();

            if ($recipients->isEmpty()) {
                continue;
            }

            foreach ($lowStockProducts as $product) {
                if ($this->alreadyNotifiedRecently($product->id, $store->id)) {
                    continue;
                }

                $quantity = $product->stockAt($store)?->quantity ?? 0;
                Notification::send($recipients, new LowStockAlert($product, $store, $quantity));
            }
        }

        $this->info('Low stock check complete.');

        return self::SUCCESS;
    }

    private function alreadyNotifiedRecently(int $productId, int $storeId): bool
    {
        return DB::table('notifications')
            ->where('type', LowStockAlert::class)
            ->where('created_at', '>=', now()->subHours(24))
            ->whereRaw("JSON_EXTRACT(data, '$.product_id') = ?", [$productId])
            ->whereRaw("JSON_EXTRACT(data, '$.store_id') = ?", [$storeId])
            ->exists();
    }
}
