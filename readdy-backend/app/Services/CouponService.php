<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CouponService
{
    /**
     * Create a new coupon
     */
    public function createCoupon(array $data): Coupon
    {
        try {
            DB::beginTransaction();

            // Generate unique code if not provided
            if (!isset($data['code'])) {
                $data['code'] = $this->generateUniqueCode();
            }

            $coupon = Coupon::create($data);

            DB::commit();
            Log::info('Coupon created successfully', ['coupon_id' => $coupon->id, 'code' => $coupon->code]);

            return $coupon;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating coupon', ['error' => $e->getMessage(), 'data' => $data]);
            throw $e;
        }
    }

    /**
     * Validate coupon for user and order
     */
    public function validateCoupon(string $code, int $userId, float $orderTotal, array $bookIds = []): array
    {
        try {
            $coupon = Coupon::where('code', $code)->first();

            if (!$coupon) {
                return [
                    'valid' => false,
                    'message' => 'Invalid coupon code',
                    'coupon' => null
                ];
            }

            // Check if coupon is valid
            if (!$coupon->isValid()) {
                return [
                    'valid' => false,
                    'message' => 'Coupon is not valid or has expired',
                    'coupon' => $coupon
                ];
            }

            // Check if user can use this coupon
            if (!$coupon->canBeUsedByUser($userId)) {
                return [
                    'valid' => false,
                    'message' => 'You have already used this coupon or reached the usage limit',
                    'coupon' => $coupon
                ];
            }

            // Check minimum purchase amount
            if ($orderTotal < $coupon->min_amount) {
                return [
                    'valid' => false,
                    'message' => "Minimum purchase amount of {$coupon->min_amount} required",
                    'coupon' => $coupon
                ];
            }

            // Check if coupon applies to books
            if (!empty($bookIds)) {
                foreach ($bookIds as $bookId) {
                    if (!$coupon->appliesToBook($bookId)) {
                        return [
                            'valid' => false,
                            'message' => 'This coupon does not apply to some items in your cart',
                            'coupon' => $coupon
                        ];
                    }
                }
            }

            // Calculate discount
            $discountAmount = $coupon->calculateDiscount($orderTotal);

            return [
                'valid' => true,
                'message' => 'Coupon applied successfully',
                'coupon' => $coupon,
                'discount_amount' => $discountAmount,
                'final_total' => $orderTotal - $discountAmount
            ];

        } catch (\Exception $e) {
            Log::error('Error validating coupon', [
                'code' => $code,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'valid' => false,
                'message' => 'Error validating coupon',
                'coupon' => null
            ];
        }
    }

    /**
     * Apply coupon to order
     */
    public function applyCouponToOrder(string $code, Order $order): array
    {
        try {
            DB::beginTransaction();

            $validation = $this->validateCoupon(
                $code,
                $order->user_id,
                $order->total_amount,
                $order->items->pluck('book_id')->toArray()
            );

            if (!$validation['valid']) {
                return $validation;
            }

            $coupon = $validation['coupon'];
            $discountAmount = $validation['discount_amount'];

            // Create coupon usage record
            CouponUsage::create([
                'coupon_id' => $coupon->id,
                'user_id' => $order->user_id,
                'order_id' => $order->id,
                'discount_amount' => $discountAmount,
                'order_total_before' => $order->total_amount,
                'order_total_after' => $order->total_amount - $discountAmount,
                'applied_at' => now(),
                'metadata' => [
                    'coupon_code' => $coupon->code,
                    'coupon_type' => $coupon->type,
                    'coupon_value' => $coupon->value
                ]
            ]);

            // Update order total
            $order->update([
                'total_amount' => $order->total_amount - $discountAmount,
                'metadata' => array_merge($order->metadata ?? [], [
                    'applied_coupon' => [
                        'code' => $coupon->code,
                        'discount_amount' => $discountAmount,
                        'applied_at' => now()->toISOString()
                    ]
                ])
            ]);

            // Increment coupon usage count
            $coupon->incrementUsage();

            DB::commit();

            Log::info('Coupon applied to order successfully', [
                'coupon_id' => $coupon->id,
                'order_id' => $order->id,
                'discount_amount' => $discountAmount
            ]);

            return [
                'success' => true,
                'message' => 'Coupon applied successfully',
                'discount_amount' => $discountAmount,
                'final_total' => $order->total_amount
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error applying coupon to order', [
                'code' => $code,
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error applying coupon to order'
            ];
        }
    }

    /**
     * Remove coupon from order
     */
    public function removeCouponFromOrder(Order $order): array
    {
        try {
            DB::beginTransaction();

            $couponUsage = $order->couponUsage;

            if (!$couponUsage) {
                return [
                    'success' => false,
                    'message' => 'No coupon applied to this order'
                ];
            }

            // Restore original order total
            $originalTotal = $couponUsage->order_total_before;
            $order->update([
                'total_amount' => $originalTotal,
                'metadata' => array_merge($order->metadata ?? [], [
                    'removed_coupon' => [
                        'code' => $couponUsage->coupon->code,
                        'removed_at' => now()->toISOString()
                    ]
                ])
            ]);

            // Delete coupon usage record
            $couponUsage->delete();

            // Decrement coupon usage count
            $couponUsage->coupon->decrement('used_count');

            DB::commit();

            Log::info('Coupon removed from order successfully', [
                'order_id' => $order->id,
                'original_total' => $originalTotal
            ]);

            return [
                'success' => true,
                'message' => 'Coupon removed successfully',
                'original_total' => $originalTotal
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error removing coupon from order', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error removing coupon from order'
            ];
        }
    }

    /**
     * Get available coupons for user
     */
    public function getAvailableCouponsForUser(int $userId): array
    {
        try {
            $coupons = Coupon::valid()
                ->public()
                ->where(function ($query) {
                    $query->whereNull('user_limit')
                        ->orWhereRaw('(SELECT COUNT(*) FROM coupon_usage WHERE coupon_id = coupons.id AND user_id = ?) < user_limit', [$userId]);
                })
                ->get();

            return [
                'success' => true,
                'coupons' => $coupons
            ];

        } catch (\Exception $e) {
            Log::error('Error getting available coupons for user', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error retrieving available coupons'
            ];
        }
    }

    /**
     * Get user's coupon usage history
     */
    public function getUserCouponHistory(int $userId, int $limit = 20): array
    {
        try {
            $usageHistory = CouponUsage::with(['coupon', 'order'])
                ->where('user_id', $userId)
                ->orderBy('applied_at', 'desc')
                ->limit($limit)
                ->get();

            return [
                'success' => true,
                'history' => $usageHistory
            ];

        } catch (\Exception $e) {
            Log::error('Error getting user coupon history', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error retrieving coupon history'
            ];
        }
    }

    /**
     * Get coupon analytics
     */
    public function getCouponAnalytics(int $couponId = null, array $filters = []): array
    {
        try {
            $query = CouponUsage::with(['coupon', 'user', 'order']);

            if ($couponId) {
                $query->where('coupon_id', $couponId);
            }

            // Apply date filters
            if (isset($filters['start_date'])) {
                $query->where('applied_at', '>=', $filters['start_date']);
            }

            if (isset($filters['end_date'])) {
                $query->where('applied_at', '<=', $filters['end_date']);
            }

            $usageData = $query->get();

            $analytics = [
                'total_usage' => $usageData->count(),
                'total_discount' => $usageData->sum('discount_amount'),
                'average_discount' => $usageData->avg('discount_amount'),
                'unique_users' => $usageData->unique('user_id')->count(),
                'total_orders' => $usageData->unique('order_id')->count(),
                'usage_by_date' => $usageData->groupBy(function ($usage) {
                    return $usage->applied_at->format('Y-m-d');
                })->map->count(),
                'discount_by_date' => $usageData->groupBy(function ($usage) {
                    return $usage->applied_at->format('Y-m-d');
                })->map->sum('discount_amount')
            ];

            return [
                'success' => true,
                'analytics' => $analytics
            ];

        } catch (\Exception $e) {
            Log::error('Error getting coupon analytics', [
                'coupon_id' => $couponId,
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error retrieving coupon analytics'
            ];
        }
    }

    /**
     * Generate unique coupon code
     */
    private function generateUniqueCode(int $length = 8): string
    {
        do {
            $code = strtoupper(Str::random($length));
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }

    /**
     * Bulk create coupons
     */
    public function bulkCreateCoupons(array $couponData, int $quantity = 1): array
    {
        try {
            DB::beginTransaction();

            $createdCoupons = [];

            for ($i = 0; $i < $quantity; $i++) {
                $data = $couponData;
                $data['code'] = $this->generateUniqueCode();
                $createdCoupons[] = $this->createCoupon($data);
            }

            DB::commit();

            Log::info('Bulk coupons created successfully', [
                'quantity' => $quantity,
                'coupon_ids' => collect($createdCoupons)->pluck('id')->toArray()
            ]);

            return [
                'success' => true,
                'message' => "Successfully created {$quantity} coupons",
                'coupons' => $createdCoupons
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating bulk coupons', [
                'quantity' => $quantity,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Error creating bulk coupons'
            ];
        }
    }
}
