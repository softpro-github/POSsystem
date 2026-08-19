<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->decimal('balance', 14, 2)->default(0)->after('address');
        });

        // Backfill: this concept didn't exist before, so seed the balance from
        // already-received purchase orders (nothing owed has been paid/returned yet).
        DB::table('purchase_orders')
            ->where('status', 'received')
            ->selectRaw('supplier_id, SUM(total_amount) as total')
            ->groupBy('supplier_id')
            ->get()
            ->each(function ($row) {
                DB::table('suppliers')->where('id', $row->supplier_id)->update(['balance' => $row->total]);
            });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('balance');
        });
    }
};
