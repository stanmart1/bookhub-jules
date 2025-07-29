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
        Schema::create('reading_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // books, pages, time, streak
            $table->integer('target');
            $table->integer('current')->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->string('period')->default('yearly'); // daily, weekly, monthly, yearly
            $table->boolean('is_active')->default(true);
            $table->boolean('is_completed')->default(false);
            $table->date('completed_at')->nullable();
            $table->json('milestones')->nullable(); // Progress milestones
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_goals');
    }
};
