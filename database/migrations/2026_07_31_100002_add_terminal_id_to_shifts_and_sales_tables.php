<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->foreignId('terminal_id')->nullable()->after('store_id')->constrained('terminals')->nullOnDelete();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('terminal_id')->nullable()->after('store_id')->constrained('terminals')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropForeign(['terminal_id']);
            $table->dropColumn('terminal_id');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['terminal_id']);
            $table->dropColumn('terminal_id');
        });
    }
};
