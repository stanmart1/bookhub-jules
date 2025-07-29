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
        Schema::create('payment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('action'); // payment.initiated, payment.verified, webhook.received, etc.
            $table->string('gateway_name')->nullable();
            $table->string('payment_reference')->nullable();
            $table->json('request_data')->nullable(); // Request data sent to gateway
            $table->json('response_data')->nullable(); // Response data from gateway
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('status'); // success, error, warning
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable(); // Additional log data
            $table->timestamps();

            $table->index(['payment_id']);
            $table->index(['user_id']);
            $table->index(['action', 'created_at']);
            $table->index(['gateway_name', 'created_at']);
            $table->index(['payment_reference']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_logs');
    }
};
