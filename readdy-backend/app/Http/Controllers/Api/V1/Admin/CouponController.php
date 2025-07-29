<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\CouponUsage;
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
     * List all coupons with pagination and filters
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'per_page' => 'sometimes|integer|min:1|max:100',
                'search' => 'sometimes|string|max:100',
                'type' => 'sometimes|string|in:percentage,fixed,bogo',
                'status' => 'sometimes|string|in:active,inactive,expired',
                'sort_by' => 'sometimes|string|in:created_at,code,name,used_count,expires_at',
                'sort_order' => 'sometimes|string|in:asc,desc'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $query = Coupon::with(['usages', 'campaign']);

            // Apply search filter
            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('code', 'like', '%' . $request->search . '%')
                      ->orWhere('name', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }

            // Apply type filter
            if ($request->type) {
                $query->where('type', $request->type);
            }

            // Apply status filter
            if ($request->status) {
                switch ($request->status) {
                    case 'active':
                        $query->where('is_active', true);
                        break;
                    case 'inactive':
                        $query->where('is_active', false);
                        break;
                    case 'expired':
                        $query->where('expires_at', '<', now());
                        break;
                }
            }

            // Apply sorting
            $sortBy = $request->input('sort_by', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $perPage = $request->input('per_page', 15);
            $coupons = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $coupons,
                'message' => 'Coupons retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving coupons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new coupon
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'code' => 'sometimes|string|max:50|unique:coupons,code',
                'name' => 'required|string|max:255',
                'description' => 'sometimes|string',
                'type' => 'required|string|in:percentage,fixed,bogo',
                'value' => 'required|numeric|min:0',
                'min_amount' => 'sometimes|numeric|min:0',
                'max_discount' => 'sometimes|numeric|min:0',
                'usage_limit' => 'sometimes|integer|min:1',
                'user_limit' => 'sometimes|integer|min:1',
                'per_user_limit' => 'sometimes|integer|min:1',
                'starts_at' => 'sometimes|date',
                'expires_at' => 'sometimes|date|after:starts_at',
                'is_active' => 'sometimes|boolean',
                'is_public' => 'sometimes|boolean',
                'applicable_books' => 'sometimes|array',
                'applicable_books.*' => 'integer|exists:books,id',
                'excluded_books' => 'sometimes|array',
                'excluded_books.*' => 'integer|exists:books,id',
                'metadata' => 'sometimes|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $coupon = $this->couponService->createCoupon($request->all());

            return response()->json([
                'success' => true,
                'data' => $coupon,
                'message' => 'Coupon created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating coupon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get coupon details
     */
    public function show(string $id): JsonResponse
    {
        try {
            $coupon = Coupon::with(['usages.user', 'usages.order', 'campaign'])
                ->find($id);

            if (!$coupon) {
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

    /**
     * Update coupon
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $coupon = Coupon::find($id);

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'sometimes|string|max:50|unique:coupons,code,' . $id,
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'type' => 'sometimes|string|in:percentage,fixed,bogo',
                'value' => 'sometimes|numeric|min:0',
                'min_amount' => 'sometimes|numeric|min:0',
                'max_discount' => 'sometimes|numeric|min:0',
                'usage_limit' => 'sometimes|integer|min:1',
                'user_limit' => 'sometimes|integer|min:1',
                'per_user_limit' => 'sometimes|integer|min:1',
                'starts_at' => 'sometimes|date',
                'expires_at' => 'sometimes|date|after:starts_at',
                'is_active' => 'sometimes|boolean',
                'is_public' => 'sometimes|boolean',
                'applicable_books' => 'sometimes|array',
                'applicable_books.*' => 'integer|exists:books,id',
                'excluded_books' => 'sometimes|array',
                'excluded_books.*' => 'integer|exists:books,id',
                'metadata' => 'sometimes|array'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $coupon->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $coupon->fresh(),
                'message' => 'Coupon updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating coupon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete coupon
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $coupon = Coupon::find($id);

            if (!$coupon) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon not found'
                ], 404);
            }

            // Check if coupon has been used
            if ($coupon->used_count > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete coupon that has been used'
                ], 400);
            }

            $coupon->delete();

            return response()->json([
                'success' => true,
                'message' => 'Coupon deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting coupon',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get coupon analytics
     */
    public function analytics(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'coupon_id' => 'sometimes|integer|exists:coupons,id',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after:start_date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $filters = [];
            if ($request->start_date) {
                $filters['start_date'] = $request->start_date;
            }
            if ($request->end_date) {
                $filters['end_date'] = $request->end_date;
            }

            $result = $this->couponService->getCouponAnalytics(
                $request->coupon_id,
                $filters
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['analytics'],
                    'message' => 'Coupon analytics retrieved successfully'
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
                'message' => 'Error retrieving coupon analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk create coupons
     */
    public function bulkCreate(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'coupon_data' => 'required|array',
                'coupon_data.name' => 'required|string|max:255',
                'coupon_data.description' => 'sometimes|string',
                'coupon_data.type' => 'required|string|in:percentage,fixed,bogo',
                'coupon_data.value' => 'required|numeric|min:0',
                'coupon_data.min_amount' => 'sometimes|numeric|min:0',
                'coupon_data.max_discount' => 'sometimes|numeric|min:0',
                'coupon_data.usage_limit' => 'sometimes|integer|min:1',
                'coupon_data.user_limit' => 'sometimes|integer|min:1',
                'coupon_data.per_user_limit' => 'sometimes|integer|min:1',
                'coupon_data.starts_at' => 'sometimes|date',
                'coupon_data.expires_at' => 'sometimes|date|after:coupon_data.starts_at',
                'coupon_data.is_active' => 'sometimes|boolean',
                'coupon_data.is_public' => 'sometimes|boolean',
                'quantity' => 'required|integer|min:1|max:100'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->couponService->bulkCreateCoupons(
                $request->coupon_data,
                $request->quantity
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'data' => $result['coupons'],
                    'message' => $result['message']
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating bulk coupons',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get coupon usage statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $totalCoupons = Coupon::count();
            $activeCoupons = Coupon::where('is_active', true)->count();
            $expiredCoupons = Coupon::where('expires_at', '<', now())->count();
            $totalUsage = CouponUsage::count();
            $totalDiscount = CouponUsage::sum('discount_amount');

            $usageByType = Coupon::selectRaw('type, COUNT(*) as count, SUM(used_count) as total_usage')
                ->groupBy('type')
                ->get();

            $recentUsage = CouponUsage::with(['coupon', 'user'])
                ->orderBy('applied_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_coupons' => $totalCoupons,
                        'active_coupons' => $activeCoupons,
                        'expired_coupons' => $expiredCoupons,
                        'total_usage' => $totalUsage,
                        'total_discount' => $totalDiscount
                    ],
                    'usage_by_type' => $usageByType,
                    'recent_usage' => $recentUsage
                ],
                'message' => 'Coupon statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving coupon statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
