<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('client_uuid')->nullable()->unique()->after('invoice_number');
            $table->timestamp('offline_queued_at')->nullable()->after('sold_at');
            $table->boolean('needs_review')->default(false)->after('status');
            $table->text('review_note')->nullable()->after('needs_review');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['client_uuid', 'offline_queued_at', 'needs_review', 'review_note']);
        });
    }
};
