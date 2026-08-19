<?php

namespace App\Notifications;

use App\Models\Product;
use App\Models\Store;
use Illuminate\Notifications\Notification;

class LowStockAlert extends Notification
{
    public function __construct(private Product $product, private Store $store, private int $quantity) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'store_id' => $this->store->id,
            'store_name' => $this->store->name,
            'quantity' => $this->quantity,
            'reorder_level' => $this->product->reorder_level,
            'message' => "{$this->product->name} is low on stock at {$this->store->name} ({$this->quantity} left).",
            'url' => route('products.edit', $this->product),
        ];
    }
}
