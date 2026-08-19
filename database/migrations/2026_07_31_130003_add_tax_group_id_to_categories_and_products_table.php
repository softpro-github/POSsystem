<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('tax_group_id')->nullable()->after('tax_rate')->constrained('tax_groups')->nullOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('tax_group_id')->nullable()->after('unit_id')->constrained('tax_groups')->nullOnDelete();
        });

        $now = now();

        // Seed the "Default" group from the old global default_tax_rate setting (0 if never set).
        $defaultRate = (float) (DB::table('settings')->where('key', 'default_tax_rate')->value('value') ?? 0);

        $defaultComponentId = DB::table('tax_components')->insertGetId([
            'name' => 'Default Rate',
            'rate' => $defaultRate,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $defaultGroupId = DB::table('tax_groups')->insertGetId([
            'name' => 'Default',
            'is_default' => true,
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('tax_group_components')->insert([
            'tax_group_id' => $defaultGroupId,
            'tax_component_id' => $defaultComponentId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Backfill one tax group per distinct existing category tax_rate value.
        $distinctRates = DB::table('categories')->whereNotNull('tax_rate')->distinct()->pluck('tax_rate');

        foreach ($distinctRates as $rate) {
            $componentId = DB::table('tax_components')->insertGetId([
                'name' => 'Legacy '.rtrim(rtrim(number_format((float) $rate, 2), '0'), '.').'%',
                'rate' => $rate,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $groupId = DB::table('tax_groups')->insertGetId([
                'name' => 'Legacy '.rtrim(rtrim(number_format((float) $rate, 2), '0'), '.').'% (from categories)',
                'is_default' => false,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('tax_group_components')->insert([
                'tax_group_id' => $groupId,
                'tax_component_id' => $componentId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('categories')->where('tax_rate', $rate)->update(['tax_group_id' => $groupId]);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['tax_group_id']);
            $table->dropColumn('tax_group_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['tax_group_id']);
            $table->dropColumn('tax_group_id');
        });
    }
};
