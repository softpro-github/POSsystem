<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'store_id']);
        });

        $mainStoreId = DB::table('stores')->where('name', 'Main Store')->value('id');
        $now = now();

        $rows = DB::table('products')->select('id', 'quantity')->get()->map(fn ($product) => [
            'product_id' => $product->id,
            'store_id' => $mainStoreId,
            'quantity' => $product->quantity,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('product_stores')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stores');
    }
};
