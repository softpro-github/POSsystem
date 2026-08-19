<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('repair_ticket_id')->constrained('repair_tickets')->cascadeOnDelete();
            $table->string('status');
            $table->string('note')->nullable();
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_status_logs');
    }
};
