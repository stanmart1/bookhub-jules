<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_webhooks', function (Blueprint $table) {
            $table->id();
            $table->string('gateway_name'); // flutterwave, paystack, etc.
            $table->string('event_type'); // payment.successful, payment.failed, etc.
            $table->string('webhook_reference')->unique();
            $table->json('payload'); // Raw webhook data
            $table->json('processed_data')->nullable(); // Processed webhook data
            $table->string('status'); // received, processing, processed, failed
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['gateway_name', 'event_type']);
            $table->index(['status', 'created_at']);
            $table->index(['webhook_reference']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_webhooks');
    }
};
