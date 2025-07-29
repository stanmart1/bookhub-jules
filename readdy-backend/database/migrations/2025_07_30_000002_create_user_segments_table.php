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
        Schema::create('user_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('segment_type', 50);
            $table->string('segment_value', 100);
            $table->decimal('confidence_score', 5, 2)->default(0);
            $table->timestamps();

            $table->index(['user_id', 'segment_type']);
            $table->index(['segment_type', 'segment_value']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_segments');
    }
}; 