<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('tax_group_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_group_id')->constrained('tax_groups')->cascadeOnDelete();
            $table->foreignId('tax_component_id')->constrained('tax_components')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['tax_group_id', 'tax_component_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_group_components');
        Schema::dropIfExists('tax_groups');
    }
};
