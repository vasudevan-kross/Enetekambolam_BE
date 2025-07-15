<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\OrderModel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Exception;

class CouponController extends Controller
{
    public function getAllCoupons()
    {
        try {
            $coupons = Coupon::all();
            return response()->json([
                'status' => true,
                'message' => 'Coupons fetched successfully',
                'data' => $coupons,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching coupons: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getAvailableCouponsForUser(Request $request)
    {
        try {
            $userId = $request->user_id;

            if (!$userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'user_id is required',
                ], 400);
            }

            $now = Carbon::now();

            // Get user's previous coupon usage counts
            $usageCounts = CouponUsage::where('user_id', $userId)
                ->selectRaw('coupon_id, COUNT(*) as usage_count')
                ->groupBy('coupon_id')
                ->pluck('usage_count', 'coupon_id');

            // Check if user is a first-time user
            $hasOrder = OrderModel::where('user_id', $userId)->exists();

            // Fetch available coupons
            $coupons = Coupon::where('is_active', true)
                ->where(function ($q) use ($now) {
                    $q->whereNull('start_at')->orWhere('start_at', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
                })
                ->get()
                ->filter(function ($coupon) use ($usageCounts, $hasOrder) {
                    $usedCount = $usageCounts[$coupon->id] ?? 0;

                    // Check max uses per user
                    if (!is_null($coupon->max_uses_per_user) && $usedCount >= $coupon->max_uses_per_user) {
                        return false;
                    }

                    // Check if it's for first-time users only
                    if ($coupon->first_time_user_only && $hasOrder) {
                        return false;
                    }

                    return true;
                })
                ->values(); // Reset collection keys

            return response()->json([
                "response" => 200,
                'message' => 'Available coupons fetched successfully',
                'data' => $coupons,
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching available coupons: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function createCoupon(Request $request)
    {
        try {
            $validated = validator($request->all(), [
                'code' => 'required|string|unique:coupons',
                'type' => ['required', Rule::in([1, 2])], // 1 = amount, 2 = percentage
                'value' => 'required|numeric',
                'min_cart_value' => 'nullable|numeric',
                'max_uses_per_user' => 'nullable|integer',
                'first_time_user_only' => 'boolean',
                'start_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after_or_equal:start_at',
                "max_discount_amount" => 'nullable|numeric',
                'is_active' => 'boolean',
                'description' => 'nullable|string',
            ])->validated();

            $coupon = Coupon::create($validated);

            return response()->json([
                'status' => true,
                'message' => 'Coupon created successfully',
                'data' => $coupon,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error creating coupon: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function validateCoupon(Request $request)
    {
        $validated = validator($request->all(), [
            'code' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'cart_total' => 'required|numeric',
        ])->validated();

        $coupon = Coupon::where('code', $validated['code'])->first();

        if (!$coupon) {
            return response()->json([
                'response' => 201,
                'message' => 'Invalid coupon code.',
            ]);
        }

        if (!$coupon->is_active) {
            return response()->json([
                'response' => 201,
                'message' => 'This coupon is not active.',
            ]);
        }

        $now = now();
        if (($coupon->start_at && $now->lt($coupon->start_at)) ||
            ($coupon->expires_at && $now->gt($coupon->expires_at))
        ) {
            return response()->json([
                'response' => 201,
                'message' => 'Coupon is expired or not yet valid.',
            ]);
        }

        if ($coupon->min_cart_value && $validated['cart_total'] < $coupon->min_cart_value) {
            return response()->json([
                'response' => 201,
                'message' => "Minimum cart value is ₹{$coupon->min_cart_value}.",
            ]);
        }

        $usageCount = CouponUsage::where([
            'coupon_id' => $coupon->id,
            'user_id' => $validated['user_id'],
        ])->count();

        if ($coupon->max_uses_per_user && $usageCount >= $coupon->max_uses_per_user) {
            return response()->json([
                'response' => 201,
                'message' => "You’ve already used this coupon the maximum number of times.",
            ]);
        }

        if ($coupon->first_time_user_only) {
            $orderCount = OrderModel::where('user_id', $validated['user_id'])->count();
            if ($orderCount > 0) {
                return response()->json([
                    'response' => 201,
                    'message' => "Coupon is for first-time users only.",
                ]);
            }
        }

        // Calculate discount
        $discount = 0;
        if ($coupon->type == 1) {
            $discount = $coupon->value;
        } elseif ($coupon->type == 2) {
            $discount = ($validated['cart_total'] * $coupon->value) / 100;
        }

        return response()->json([
            'response' => 200,
            'message' => 'Coupon is valid.',
            'data' => [
                'discount' => round($discount, 2),
                'coupon' => $coupon,
            ],
        ]);
    }


    public function updateCoupon(Request $request, $id)
    {
        try {
            $coupon = Coupon::findOrFail($id);

            $validated = validator($request->all(), [
                'code' => ['sometimes', 'string', Rule::unique('coupons')->ignore($coupon->id)],
                'type' => ['sometimes', Rule::in([1, 2])],
                'value' => 'sometimes|numeric',
                'min_cart_value' => 'nullable|numeric',
                'max_uses_per_user' => 'nullable|integer',
                'first_time_user_only' => 'boolean',
                'start_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after_or_equal:start_at',
                "max_discount_amount" => 'nullable|numeric',
                'is_active' => 'boolean',
                'description' => 'nullable|string', // ✅ Add this line
            ])->validated();

            $coupon->update($validated);

            return response()->json([
                'status' => true,
                'message' => 'Coupon updated successfully',
                'data' => $coupon,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error updating coupon: ' . $e->getMessage(),
            ], 500);
        }
    }
}
