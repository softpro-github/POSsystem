<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['users', 'stock_movements', 'sales', 'purchase_orders', 'shifts', 'supplier_payments'];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->foreignId('store_id')->nullable()->after('id')->constrained('stores')->nullOnDelete();
            });
        }

        $mainStoreId = DB::table('stores')->where('name', 'Main Store')->value('id');

        foreach ($this->tables as $table) {
            DB::table($table)->update(['store_id' => $mainStoreId]);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['store_id']);
                $blueprint->dropColumn('store_id');
            });
        }
    }
};
