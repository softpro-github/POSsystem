<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_serials', function (Blueprint $table) {
            $table->foreign('purchase_order_item_id')
                ->references('id')->on('purchase_order_items')
                ->nullOnDelete();
            $table->foreign('sale_item_id')
                ->references('id')->on('sale_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_serials', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_item_id']);
            $table->dropForeign(['sale_item_id']);
        });
    }
};
