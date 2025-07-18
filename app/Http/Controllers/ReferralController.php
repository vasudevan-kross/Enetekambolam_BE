<?php

namespace App\Http\Controllers;

use App\Models\OrderModel;
use App\Models\ReferralCode;
use App\Models\ReferralUsage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\DB;

class ReferralController extends Controller
{

    public function getReferralCode(Request $request)
    {
        try {
            $userId = $request->input('user_id');

            if (!$userId) {
                return response()->json([
                    'response' => 400,
                    'message' => 'User ID is required.',
                    'data' => null
                ], 400);
            }

            // Try to get existing referral code
            $referralCode = ReferralCode::where('user_id', $userId)->first();

            // If no referral code exists, create one
            if (!$referralCode) {
                $code = ReferralCode::generateUniqueCode();

                $referralCode = ReferralCode::create([
                    'user_id' => $userId,
                    'referral_code' => $code,
                ]);
            }
            $hasOrders = OrderModel::where('user_id', $userId)->exists();
            // Load usage statistics
            $completedCount = $referralCode->completedUsagesCount();
            $pendingCount = $referralCode->pendingUsagesCount();

            return response()->json([
                'response' => 200,
                'message' => 'Referral code retrieved successfully',
                'data' => [
                    'referral_code' => $referralCode->referral_code,
                    'total_referrals' => $completedCount + $pendingCount,
                    'completed_referrals' => $completedCount,
                    'pending_referrals' => $pendingCount,
                    'has_orders' => $hasOrders,
                    'created_at' => $referralCode->created_at,
                ],
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'response' => 500,
                'message' => 'Error retrieving referral code: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public static function useReferralCode(string $referralCode, int $userId)
    {
        try {
            $user = User::find($userId);

            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'User not found',
                ];
            }

            $referral = ReferralCode::whereRaw('BINARY referral_code = ?', [$referralCode])->first();

            if (!$referral) {
                return [
                    'success' => false,
                    'message' => 'Referral code does not exist',
                ];
            }

            if ($referral->user_id === $userId) {
                return [
                    'success' => false,
                    'message' => 'You cannot use your own referral code',
                ];
            }

            $alreadyUsed = ReferralUsage::where('referred_user_id', $userId)->exists();
            if ($alreadyUsed) {
                return [
                    'success' => false,
                    'message' => 'Referral code already used by this user',
                ];
            }

            DB::beginTransaction();

            // 1. Save Referral Usage
            $usage = ReferralUsage::create([
                'referral_code_id' => $referral->id,
                'referred_user_id' => $userId,
                'status' => ReferralUsage::STATUS_PENDING,
            ]);

            // 2. Fetch Web App Settings
            $settings = DB::table('web_app_settings')
                ->whereIn('id', [19, 20])
                ->pluck('value', 'id');

            $referralRewardEnabled = isset($settings[20]) && filter_var($settings[20], FILTER_VALIDATE_BOOLEAN);
            $referralRewardAmount = isset($settings[19]) ? (float) $settings[19] : 0;

            if ($referralRewardEnabled && $referralRewardAmount > 0) {
                // 3. Credit to referrer (not referred user)
                $referrer = User::find($referral->user_id);

                if ($referrer) {
                    $paymentId = 'txn_' . now()->format('YmdHis');

                    // 3a. Add Transaction
                    DB::table('transactions')->insert([
                        'order_id' => null,
                        'user_id' => $referrer->id,
                        'payment_id' => $paymentId,
                        'amount' => $referralRewardAmount,
                        'previous_balance' => $referrer->wallet_amount ?? 0,
                        'source_type' => 1,
                        'description' => 'Referral bonus credited to wallet for referring user: ' . $user->name,
                        'type' => 4,
                        'payment_mode' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // 3b. Update Referrer's Wallet
                    $referrer->wallet_amount = ($referrer->wallet_amount ?? 0) + $referralRewardAmount;
                    $referrer->save();

                    // 4. Mark usage as completed
                    $usage->status = ReferralUsage::STATUS_COMPLETED;
                    $usage->save();
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Referral code applied successfully',
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error applying referral code: ' . $e->getMessage(),
            ];
        }
    }



    public static function updateReferralCode(Request $request)
    {
        try {
            // Validate input
            $request->validate([
                'referral_code' => 'required|string|max:20',
                'user_id' => 'required|integer|exists:users,id',
            ]);

            $userId = $request->user_id;
            $inputCode = $request->referral_code;

            // 1. Find user
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found',
                    'data' => ['message' => 'User not found']
                ], 404);
            }

            // 2. Case-sensitive exact match of referral code
            $referralCode = ReferralCode::whereRaw('BINARY referral_code = ?', [$inputCode])->first();

            if (!$referralCode) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid referral code',
                    'data' => ['message' => 'Referral code does not exist']
                ], 404);
            }

            // 3. Prevent self-referral
            if ($referralCode->user_id === $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'You cannot use your own referral code',
                    'data' => ['message' => 'You cannot use your own referral code']
                ], 400);
            }

            // 4. Check if referral code already used by this user
            $alreadyUsed = ReferralUsage::where('referred_user_id', $user->id)->exists();

            if ($alreadyUsed) {
                return response()->json([
                    'status' => false,
                    'message' => 'Referral code has already been used by this user',
                    'data' => ['message' => 'You have already used a referral code']
                ], 400);
            }

            // 5. Insert referral usage
            DB::beginTransaction();

            $referralUsage = ReferralUsage::create([
                'referral_code_id' => $referralCode->id,
                'referred_user_id' => $user->id,
                'status' => ReferralUsage::STATUS_PENDING,
            ]);

            DB::commit();

            // 6. Load referrer user
            $referrer = User::find($referralCode->user_id);

            return response()->json([
                'status' => true,
                'message' => 'Referral code applied successfully',
                'data' => [
                    'referral_code' => $referralCode->referral_code,
                    'referrer_name' => $referrer->name ?? 'Unknown',
                    'status' => $referralUsage->status,
                    'applied_at' => $referralUsage->created_at,
                ]
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'data' => [
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ],
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Error applying referral code: ' . $e->getMessage(),
                'data' => ['message' => $e->getMessage()]
            ], 500);
        }
    }


    public function validateReferralCodeByPhone(Request $request)
    {
        try {
            $request->validate([
                'referral_code' => 'required|string|max:20',
                'phone' => 'required|string|size:10',
            ]);

            $inputCode = $request->referral_code;
            $phone = trim($request->phone);

            // 1. Check exact match of referral code (case-sensitive)
            $referralCode = ReferralCode::whereRaw('BINARY referral_code = ?', [$inputCode])->first();

            if (!$referralCode) {
                return response()->json([
                    'response' => 200,
                    'message' => 'Referral code does not exist',
                    'data' => null
                ], 200);
            }

            // 2. Get the user by phone
            $user = User::where('phone', $phone)->first();

            if ($user) {
                if ($referralCode->user_id === $user->id) {
                    return response()->json([
                        'response' => 200,
                        'message' => 'You cannot use your own referral code',
                        'data' => null
                    ], 200);
                }
                $hasOrders = DB::table('orders')->where('user_id', $user->id)->exists();

                if ($hasOrders) {
                    return response()->json([
                        'response' => 200,
                        'message' => 'Referral bonus only for new users',
                        'data' => null
                    ], 200);
                }

                // // 4. Check if user has already used a referral code
                // $existingUsage = ReferralUsage::where('referred_user_id', $user->id)->first();

                // if ($existingUsage) {
                //     return response()->json([
                //         'response' => 200,
                //         'message' => 'You have already used a referral code',
                //         'data' => null
                //     ], 200);
                // }
            }

            return response()->json([
                'response' => 200,
                'message' => 'Referral code retrieved successfully',
                'data' => [
                    'referral_code' => $referralCode->referral_code,
                    'created_at' => $referralCode->created_at,
                ],
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'response' => 422,
                'message' => 'Validation failed',
                'data' => [
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ],
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'response' => 500,
                'message' => 'Error validating referral code: ' . $e->getMessage(),
                'data' => [
                    'message' => 'Error validating referral code: ' . $e->getMessage()
                ]
            ], 500);
        }
    }


    public static function completeReferral(int $userId)
    {
        try {
            $referredUserId = $userId;

            // Get referral usage
            $referralUsage = ReferralUsage::where('referred_user_id', $referredUserId)
                ->where('status', ReferralUsage::STATUS_PENDING)
                ->first();

            if (!$referralUsage) {
                return [
                    'success' => false,
                    'message' => 'No pending referral usage found for this user',
                ];
            }

            $referralCode = $referralUsage->referralCode;
            $referrer = User::find($referralCode->user_id);
            $referredUser = User::find($referredUserId);

            if (!$referrer || !$referredUser) {
                return [
                    'success' => false,
                    'message' => 'Referrer or referred user not found',
                ];
            }

            // Check if referred user already has orders
            $hasOrders = DB::table('orders')->where('user_id', $referredUserId)->exists();

            if ($hasOrders) {
                return [
                    'success' => false,
                    'message' => 'Referral bonus only for new users',
                ];
            }

            // Get settings
            $settings = DB::table('web_app_settings')
                ->whereIn('id', [19, 20])
                ->pluck('value', 'id');

            $referralRewardEnabled = isset($settings[20]) && filter_var($settings[20], FILTER_VALIDATE_BOOLEAN);
            $referralRewardAmount = isset($settings[19]) ? (float) $settings[19] : 0;

            DB::beginTransaction();

            // Update usage to completed
            $referralUsage->update(['status' => ReferralUsage::STATUS_COMPLETED]);

            // If setting 20 is false, still apply bonus (logic for first order reward)
            if (!$referralRewardEnabled && $referralRewardAmount > 0) {
                $paymentId = 'txn_' . now()->format('YmdHis');

                // Create transaction for referrer
                DB::table('transactions')->insert([
                    'order_id' => null,
                    'user_id' => $referrer->id,
                    'payment_id' => $paymentId,
                    'amount' => $referralRewardAmount,
                    'description' => 'Referral bonus credited to wallet for referring user: ' . $referredUser->name,
                    'type' => 4, // referral
                    'payment_mode' => 1, // wallet
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update wallet
                $referrer->wallet_amount = ($referrer->wallet_amount ?? 0) + $referralRewardAmount;
                $referrer->save();
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Referral marked as completed and bonus processed',
                'data' => [
                    'referral_code' => $referralCode->referral_code,
                    'referrer_name' => $referrer->name,
                    'referred_user_name' => $referredUser->name,
                    'status' => ReferralUsage::STATUS_COMPLETED,
                    'completed_at' => now(),
                ],
            ];
        } catch (ValidationException $e) {
            return [
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error completing referral: ' . $e->getMessage(),
            ];
        }
    }
}
