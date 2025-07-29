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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // flutterwave, paystack, stripe
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->json('config'); // API keys, webhook secrets, etc.
            $table->boolean('is_active')->default(true);
            $table->boolean('is_test_mode')->default(true);
            $table->json('supported_currencies')->nullable();
            $table->json('supported_payment_methods')->nullable();
            $table->integer('priority')->default(0); // For gateway selection order
            $table->timestamps();

            $table->unique('name');
            $table->index(['is_active', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
