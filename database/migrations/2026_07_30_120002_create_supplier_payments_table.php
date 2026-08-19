<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 14, 2);
            $table->enum('method', ['cash', 'card', 'transfer'])->default('cash');
            $table->timestamp('paid_at');
            // FK to shifts added later (2026_07_30_130004) once that table exists —
            // Shifts (Module 5) depends on this table for its "pay from drawer"
            // feature, so the two modules reference each other; this side just
            // holds a plain nullable column until the other table exists.
            $table->unsignedBigInteger('shift_id')->nullable()->index();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_payments');
    }
};
