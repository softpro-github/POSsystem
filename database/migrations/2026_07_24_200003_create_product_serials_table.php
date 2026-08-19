<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('imei_serial')->unique();
            $table->enum('status', ['in_stock', 'sold', 'returned', 'defective'])->default('in_stock');
            // FK constraints to purchase_order_items/sale_items are added later
            // (2026_07_24_200012_add_foreign_keys_to_product_serials_table) once those
            // tables exist, since they in turn reference this table.
            $table->unsignedBigInteger('purchase_order_item_id')->nullable()->index();
            $table->unsignedBigInteger('sale_item_id')->nullable()->index();
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_serials');
    }
};
