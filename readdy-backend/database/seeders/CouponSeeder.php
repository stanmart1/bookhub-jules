<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Coupon;
use Carbon\Carbon;

class CouponSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'name' => 'Welcome Discount',
                'description' => 'Get 10% off your first purchase',
                'type' => 'percentage',
                'value' => 10.00,
                'min_amount' => 1000.00,
                'max_discount' => 500.00,
                'usage_limit' => 100,
                'user_limit' => 1,
                'per_user_limit' => 1,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
                'is_public' => true,
                'applicable_books' => null,
                'excluded_books' => null,
                'metadata' => [
                    'category' => 'welcome',
                    'target_audience' => 'new_users'
                ]
            ],
            [
                'code' => 'SAVE500',
                'name' => 'Fixed Discount',
                'description' => 'Save ₦500 on orders over ₦2000',
                'type' => 'fixed',
                'value' => 500.00,
                'min_amount' => 2000.00,
                'max_discount' => 500.00,
                'usage_limit' => 50,
                'user_limit' => 2,
                'per_user_limit' => 1,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(2),
                'is_active' => true,
                'is_public' => true,
                'applicable_books' => null,
                'excluded_books' => null,
                'metadata' => [
                    'category' => 'general',
                    'target_audience' => 'all_users'
                ]
            ],
            [
                'code' => 'SUMMER25',
                'name' => 'Summer Sale',
                'description' => '25% off all summer reading books',
                'type' => 'percentage',
                'value' => 25.00,
                'min_amount' => 500.00,
                'max_discount' => 1000.00,
                'usage_limit' => 200,
                'user_limit' => 3,
                'per_user_limit' => 2,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(1),
                'is_active' => true,
                'is_public' => true,
                'applicable_books' => [1, 2, 3, 4, 5], // Sample book IDs
                'excluded_books' => null,
                'metadata' => [
                    'category' => 'seasonal',
                    'target_audience' => 'all_users',
                    'season' => 'summer'
                ]
            ],
            [
                'code' => 'LOYALTY15',
                'name' => 'Loyalty Discount',
                'description' => '15% off for loyal customers',
                'type' => 'percentage',
                'value' => 15.00,
                'min_amount' => 1500.00,
                'max_discount' => 750.00,
                'usage_limit' => 75,
                'user_limit' => 1,
                'per_user_limit' => 1,
                'starts_at' => now(),
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
                'is_public' => false, // Private coupon
                'applicable_books' => null,
                'excluded_books' => null,
                'metadata' => [
                    'category' => 'loyalty',
                    'target_audience' => 'returning_customers'
                ]
            ],
            [
                'code' => 'FLASH300',
                'name' => 'Flash Sale',
                'description' => '₦300 off for 24 hours only',
                'type' => 'fixed',
                'value' => 300.00,
                'min_amount' => 1000.00,
                'max_discount' => 300.00,
                'usage_limit' => 30,
                'user_limit' => 1,
                'per_user_limit' => 1,
                'starts_at' => now(),
                'expires_at' => now()->addDay(),
                'is_active' => true,
                'is_public' => true,
                'applicable_books' => null,
                'excluded_books' => null,
                'metadata' => [
                    'category' => 'flash_sale',
                    'target_audience' => 'all_users',
                    'urgency' => 'high'
                ]
            ]
        ];

        foreach ($coupons as $couponData) {
            Coupon::create($couponData);
        }

        $this->command->info('Sample coupons created successfully!');
    }
}
