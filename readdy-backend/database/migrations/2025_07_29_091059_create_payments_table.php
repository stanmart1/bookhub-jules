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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->string('payment_reference')->unique();
            $table->string('gateway_reference')->nullable();
            $table->string('gateway_name'); // flutterwave, paystack, etc.
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('NGN');
            $table->string('payment_method')->nullable(); // card, bank_transfer, mobile_money
            $table->string('status'); // pending, processing, successful, failed, cancelled
            $table->text('gateway_response')->nullable(); // JSON response from gateway
            $table->text('metadata')->nullable(); // Additional payment data
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['payment_reference']);
            $table->index(['gateway_reference']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
