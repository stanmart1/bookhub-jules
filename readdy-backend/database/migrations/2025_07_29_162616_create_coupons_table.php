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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed', 'bogo'])->default('percentage');
            $table->decimal('value', 10, 2); // Percentage or fixed amount
            $table->decimal('min_amount', 10, 2)->default(0); // Minimum purchase amount
            $table->decimal('max_discount', 10, 2)->nullable(); // Maximum discount limit
            $table->integer('usage_limit')->nullable(); // Total usage limit
            $table->integer('used_count')->default(0); // Current usage count
            $table->integer('user_limit')->nullable(); // Limit per user
            $table->integer('per_user_limit')->default(1); // How many times per user
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true); // Public or private coupon
            $table->json('applicable_books')->nullable(); // Specific books this applies to
            $table->json('excluded_books')->nullable(); // Books excluded from this coupon
            $table->json('metadata')->nullable(); // Additional data
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['code', 'is_active']);
            $table->index(['starts_at', 'expires_at']);
            $table->index(['type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
