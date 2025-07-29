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
        Schema::create('scheduled_reports', function (Blueprint $table) {
            $table->id();
            $table->string('report_type', 50);
            $table->string('frequency', 20); // daily, weekly, monthly
            $table->json('recipients');
            $table->json('parameters')->nullable(); // Report-specific parameters
            $table->timestamp('last_generated')->nullable();
            $table->timestamp('next_generation');
            $table->boolean('is_active')->default(true);
            $table->string('status', 20)->default('active'); // active, paused, error
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['report_type', 'frequency']);
            $table->index(['next_generation']);
            $table->index(['is_active', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
    }
}; 