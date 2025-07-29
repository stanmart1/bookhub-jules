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
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->json('reading_preferences')->nullable(); // Font size, theme, etc.
            $table->json('notification_preferences')->nullable(); // Email, push, etc.
            $table->json('display_preferences')->nullable(); // UI settings
            $table->json('privacy_preferences')->nullable(); // Privacy settings
            $table->string('language')->default('en');
            $table->string('timezone')->nullable();
            $table->boolean('email_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
