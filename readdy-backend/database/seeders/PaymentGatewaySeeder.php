<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentGateway;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Flutterwave Gateway
        PaymentGateway::create([
            'name' => 'flutterwave',
            'display_name' => 'Flutterwave',
            'description' => 'Flutterwave payment gateway for African markets',
            'config' => [
                'test_public_key' => env('FLUTTERWAVE_TEST_PUBLIC_KEY', ''),
                'test_secret_key' => env('FLUTTERWAVE_TEST_SECRET_KEY', ''),
                'test_webhook_secret' => env('FLUTTERWAVE_TEST_WEBHOOK_SECRET', ''),
                'live_public_key' => env('FLUTTERWAVE_LIVE_PUBLIC_KEY', ''),
                'live_secret_key' => env('FLUTTERWAVE_LIVE_SECRET_KEY', ''),
                'live_webhook_secret' => env('FLUTTERWAVE_LIVE_WEBHOOK_SECRET', ''),
            ],
            'is_active' => true,
            'is_test_mode' => true,
            'supported_currencies' => ['NGN', 'USD', 'EUR', 'GBP', 'KES', 'GHS', 'ZAR'],
            'supported_payment_methods' => ['card', 'bank_transfer', 'mobile_money'],
            'priority' => 1,
        ]);

        // PayStack Gateway
        PaymentGateway::create([
            'name' => 'paystack',
            'display_name' => 'PayStack',
            'description' => 'PayStack payment gateway for Nigerian market',
            'config' => [
                'test_public_key' => env('PAYSTACK_TEST_PUBLIC_KEY', ''),
                'test_secret_key' => env('PAYSTACK_TEST_SECRET_KEY', ''),
                'test_webhook_secret' => env('PAYSTACK_TEST_WEBHOOK_SECRET', ''),
                'live_public_key' => env('PAYSTACK_LIVE_PUBLIC_KEY', ''),
                'live_secret_key' => env('PAYSTACK_LIVE_SECRET_KEY', ''),
                'live_webhook_secret' => env('PAYSTACK_LIVE_WEBHOOK_SECRET', ''),
            ],
            'is_active' => true,
            'is_test_mode' => true,
            'supported_currencies' => ['NGN', 'USD', 'GHS', 'ZAR'],
            'supported_payment_methods' => ['card', 'bank_transfer'],
            'priority' => 2,
        ]);

        // Stripe Gateway (for future use)
        PaymentGateway::create([
            'name' => 'stripe',
            'display_name' => 'Stripe',
            'description' => 'Stripe payment gateway for international payments',
            'config' => [
                'test_public_key' => env('STRIPE_TEST_PUBLIC_KEY', ''),
                'test_secret_key' => env('STRIPE_TEST_SECRET_KEY', ''),
                'test_webhook_secret' => env('STRIPE_TEST_WEBHOOK_SECRET', ''),
                'live_public_key' => env('STRIPE_LIVE_PUBLIC_KEY', ''),
                'live_secret_key' => env('STRIPE_LIVE_SECRET_KEY', ''),
                'live_webhook_secret' => env('STRIPE_LIVE_WEBHOOK_SECRET', ''),
            ],
            'is_active' => false, // Disabled by default
            'is_test_mode' => true,
            'supported_currencies' => ['USD', 'EUR', 'GBP', 'CAD', 'AUD'],
            'supported_payment_methods' => ['card'],
            'priority' => 3,
        ]);

        $this->command->info('Payment gateways seeded successfully!');
        $this->command->info('Remember to configure your payment gateway API keys in your .env file.');
    }
}
