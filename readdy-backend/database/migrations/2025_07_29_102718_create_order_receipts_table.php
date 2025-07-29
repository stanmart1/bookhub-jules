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
        Schema::create('order_receipts', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('order_id');
            $table->string('receipt_number')->unique();
            $table->string('file_path')->nullable();
            $table->string('file_type')->default('pdf');
            $table->json('receipt_data');
            $table->boolean('is_generated')->default(false);
            $table->timestamp('generated_at')->nullable();
            $table->json('metadata')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_receipts');
    }
};
