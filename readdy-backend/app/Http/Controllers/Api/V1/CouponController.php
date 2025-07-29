<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponController extends Controller
{
    public function __construct(
        private CouponService $couponService
    ) {}

    /**
     * List available coupons for the authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $result = $this->couponService->getAvailableCouponsForUser($user->id);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $result['coupons'],
                'message' => 'Available coupons retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving available coupons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate a coupon code
     */
    public function validate(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:50',
                'order_total' => 'required|numeric|min:0',
                'book_ids' => 'sometimes|array',
                'book_ids.*' => 'integer|exists:books,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $result = $this->couponService->validateCoupon(
                $request->code,
                $user->id,
                $request->order_total,
                $request->book_ids ?? []
            );

            if ($result['valid']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'coupon' => $result['coupon'],
                        'discount_amount' => $result['discount_amount'],
                        'final_total' => $result['final_total']
                    ],
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                    'data' => [
                        'coupon' => $result['coupon']
                    ]
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error validating coupon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply coupon to an order
     */
    public function apply(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:50',
                'order_id' => 'required|integer|exists:orders,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or access denied'
                ], 404);
            }

            // Check if order already has a coupon applied
            if ($order->couponUsage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order already has a coupon applied'
                ], 400);
            }

            $result = $this->couponService->applyCouponToOrder($request->code, $order);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'discount_amount' => $result['discount_amount'],
                        'final_total' => $result['final_total'],
                        'order' => $order->fresh()
                    ],
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error applying coupon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove coupon from an order
     */
    public function remove(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'order_id' => 'required|integer|exists:orders,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $order = Order::where('id', $request->order_id)
                ->where('user_id', $user->id)
                ->first();

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found or access denied'
                ], 404);
            }

            $result = $this->couponService->removeCouponFromOrder($order);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'original_total' => $result['original_total'],
                        'order' => $order->fresh()
                    ],
                    'message' => $result['message']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing coupon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's coupon usage history
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'sometimes|integer|min:1|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $limit = $request->input('limit', 20);
            
            $result = $this->couponService->getUserCouponHistory($user->id, $limit);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['history'],
                    'message' => 'Coupon history retrieved successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving coupon history',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get coupon details by code
     */
    public function show(Request $request, string $code): JsonResponse
    {
        try {
            $coupon = Coupon::where('code', $code)->first();

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon not found'
                ], 404);
            }

            // Only show public coupons or if user is authenticated
            if (!$coupon->is_public && !$request->user()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $coupon,
                'message' => 'Coupon details retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving coupon details',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
