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
        Schema::create('content_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->onDelete('cascade');
            $table->integer('views_count')->default(0);
            $table->integer('reading_time_avg')->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->decimal('engagement_score', 5, 2)->default(0);
            $table->integer('download_count')->default(0);
            $table->integer('review_count')->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->timestamps();

            $table->index(['book_id']);
            $table->index(['engagement_score']);
            $table->index(['completion_rate']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_performance');
    }
}; 