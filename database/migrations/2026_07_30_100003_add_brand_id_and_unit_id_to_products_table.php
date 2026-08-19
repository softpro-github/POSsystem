<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained('brands')->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->after('brand_id')->constrained('units')->nullOnDelete();
        });

        // Backfill: turn the existing free-text brand/unit strings into real
        // lookup rows and point products at them, so no data is lost.
        $brandNames = DB::table('products')->whereNotNull('brand')->where('brand', '!=', '')->distinct()->pluck('brand');
        foreach ($brandNames as $name) {
            $brandId = DB::table('brands')->where('name', $name)->value('id');
            if (! $brandId) {
                $brandId = DB::table('brands')->insertGetId(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);
            }
            DB::table('products')->where('brand', $name)->update(['brand_id' => $brandId]);
        }

        $unitNames = DB::table('products')->whereNotNull('unit')->where('unit', '!=', '')->distinct()->pluck('unit');
        foreach ($unitNames as $name) {
            $unitId = DB::table('units')->where('name', $name)->value('id');
            if (! $unitId) {
                $unitId = DB::table('units')->insertGetId(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);
            }
            DB::table('products')->where('unit', $name)->update(['unit_id' => $unitId]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
            $table->dropConstrainedForeignId('brand_id');
        });
    }
};
