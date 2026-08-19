<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['receipt', 'label']);
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->json('payload')->nullable(); // label item snapshot, used to reconstruct a Reprint link
            $table->string('reference'); // invoice number, or "N labels"
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            // No true "printed successfully" status exists — window.print() gives no completion
            // signal. 'opened' means the dialog was invoked; nothing stronger can be asserted.
            $table->enum('status', ['opened', 'unknown'])->default('opened');
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};
