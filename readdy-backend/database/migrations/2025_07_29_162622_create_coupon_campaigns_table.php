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
        Schema::create('coupon_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('coupon_id');
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->json('target_audience')->nullable(); // User segments, categories, etc.
            $table->json('campaign_rules')->nullable(); // Specific campaign rules
            $table->boolean('is_active')->default(true);
            $table->integer('budget_limit')->nullable(); // Campaign budget limit
            $table->decimal('budget_used', 10, 2)->default(0); // Budget used so far
            $table->json('metadata')->nullable(); // Additional campaign data
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['start_date', 'end_date']);
            $table->index(['is_active', 'start_date']);
            $table->index(['coupon_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupon_campaigns');
    }
};
