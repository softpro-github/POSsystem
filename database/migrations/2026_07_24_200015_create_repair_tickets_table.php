<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('repair_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->string('device_type');
            $table->string('device_brand')->nullable();
            $table->string('device_model')->nullable();
            $table->string('imei_serial')->nullable();
            $table->text('issue_description');
            $table->enum('status', [
                'received', 'diagnosing', 'awaiting_parts', 'in_repair',
                'ready_for_pickup', 'completed', 'cancelled',
            ])->default('received');
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('estimated_cost', 12, 2)->nullable();
            $table->decimal('final_cost', 12, 2)->nullable();
            $table->date('received_date');
            $table->date('completed_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('repair_tickets');
    }
};
