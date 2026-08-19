<?php

namespace App\Notifications;

use App\Models\StockTransfer;
use Illuminate\Notifications\Notification;

class StockTransferAwaitingReceipt extends Notification
{
    public function __construct(private StockTransfer $transfer) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $this->transfer->loadMissing('fromStore', 'toStore');

        return [
            'transfer_id' => $this->transfer->id,
            'from_store' => $this->transfer->fromStore->name,
            'to_store' => $this->transfer->toStore->name,
            'message' => "Stock transfer from {$this->transfer->fromStore->name} is awaiting receipt at {$this->transfer->toStore->name}.",
            'url' => route('transfers.show', $this->transfer),
        ];
    }
}
