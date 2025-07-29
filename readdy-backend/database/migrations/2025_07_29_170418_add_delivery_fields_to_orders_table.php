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
        Schema::table('orders', function (Blueprint $table) {
            // Delivery status and tracking
            $table->enum('delivery_status', ['pending', 'processing', 'delivered', 'failed'])->default('pending')->after('status');
            $table->timestamp('delivered_at')->nullable()->after('refunded_at');
            $table->timestamp('delivery_attempted_at')->nullable()->after('delivered_at');
            
            // Delivery tracking
            $table->string('delivery_token')->nullable()->unique()->after('delivery_attempted_at');
            $table->integer('delivery_attempts')->default(0)->after('delivery_token');
            $table->json('delivery_metadata')->nullable()->after('delivery_attempts');
            
            // Notification tracking
            $table->boolean('confirmation_email_sent')->default(false)->after('delivery_metadata');
            $table->boolean('confirmation_sms_sent')->default(false)->after('confirmation_email_sent');
            $table->timestamp('confirmation_email_sent_at')->nullable()->after('confirmation_sms_sent');
            $table->timestamp('confirmation_sms_sent_at')->nullable()->after('confirmation_email_sent_at');
            
            // Indexes for performance
            $table->index(['delivery_status', 'delivered_at']);
            $table->index(['delivery_token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['delivery_status', 'delivered_at']);
            $table->dropIndex(['delivery_token']);
            
            $table->dropColumn([
                'delivery_status',
                'delivered_at',
                'delivery_attempted_at',
                'delivery_token',
                'delivery_attempts',
                'delivery_metadata',
                'confirmation_email_sent',
                'confirmation_sms_sent',
                'confirmation_email_sent_at',
                'confirmation_sms_sent_at'
            ]);
        });
    }
};
