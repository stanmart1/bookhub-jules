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
        Schema::create('download_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('book_file_id');
            $table->string('download_token')->unique();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->enum('status', ['initiated', 'downloading', 'completed', 'failed', 'expired'])->default('initiated');
            $table->timestamp('initiated_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('expires_at');
            $table->bigInteger('bytes_downloaded')->default(0);
            $table->bigInteger('total_bytes')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata')->nullable(); // additional download data
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('book_file_id')->references('id')->on('book_files')->onDelete('cascade');
            
            // Indexes for performance
            $table->index(['order_id', 'book_id']);
            $table->index(['user_id', 'status']);
            $table->index(['download_token']);
            $table->index(['status', 'initiated_at']);
            $table->index(['expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('download_logs');
    }
};
