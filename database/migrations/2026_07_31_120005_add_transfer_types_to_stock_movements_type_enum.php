<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE stock_movements MODIFY type ENUM('purchase', 'sale', 'adjustment', 'return', 'purchase_return', 'transfer_out', 'transfer_in') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stock_movements MODIFY type ENUM('purchase', 'sale', 'adjustment', 'return', 'purchase_return') NOT NULL");
    }
};
