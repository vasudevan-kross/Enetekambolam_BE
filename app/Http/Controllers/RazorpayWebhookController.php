<?php

namespace App\Http\Controllers;

use App\Http\Controllers\OrderController;
use App\Http\Controllers\TransactionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\OrderModel;

class RazorpayWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $secret = env('RAZORPAY_WEBHOOK_SECRET');

        $signature = $request->header('X-Razorpay-Signature');
        $payload = $request->getContent();

        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Razorpay Webhook signature mismatch.');
            return response('Invalid signature', 400);
        }

        $data = $request->all();
        Log::info('Razorpay Webhook received:', $data);

        if ($data['event'] === 'order.paid') {
            $orderData = $data['payload']['payment']['entity'];
            $razorpayOrderId = $orderData['order_id'];

            try {
                DB::transaction(function () use ($razorpayOrderId, $orderData) {
                    $pendingOrder = DB::table('pending_orders')
                        ->where('razorpay_order_id', $razorpayOrderId)
                        ->lockForUpdate()
                        ->first();

                    if (!$pendingOrder) {
                        Log::warning("Pending order with Razorpay ID {$razorpayOrderId} not found.");
                        return;
                    }

                    if ($pendingOrder->status === 'paid') {
                        Log::info("Duplicate webhook: Pending order already marked as paid.");
                        return;
                    }

                    // 1. Mark pending order as paid
                    DB::table('pending_orders')
                        ->where('id', $pendingOrder->id)
                        ->update([
                            'payment_status' => 'paid',
                            'payment_id' => $orderData['id'] ?? null,
                            'updated_at' => now(),
                        ]);

                    Log::info('Pending order marked as paid.', [
                        'pending_order_id' => $pendingOrder->id,
                        'razorpay_order_id' => $razorpayOrderId,
                    ]);

                    $existingOrder = DB::table('orders')
                        ->where('razorpay_order_id', $razorpayOrderId)
                        ->first();

                    if ($existingOrder) {
                        Log::info("Order already exists for Razorpay Order ID: {$razorpayOrderId}. Skipping order creation.");
                        return;
                    }

                    if ($pendingOrder->wallet_added_amount > 0) {
                        $transactionController = new TransactionsController();

                        $transactionRequest = new Request([
                            'user_id' => $pendingOrder->user_id,
                            'amount' => $pendingOrder->wallet_added_amount,
                            'type' => "1",
                            'description' => "Amount credited to wallet",
                            'payment_id' => $orderData['id'] ?? null,
                            'order_id' => null,
                        ]);

                        $transactionResponse = $transactionController->addData($transactionRequest);
                    }

                    $orderController = new OrderController();

                    if ($pendingOrder->subscription_type === null) {
                        // One-time order
                        $orderRequest = new Request([
                            'user_id' => $pendingOrder->user_id,
                            'status' => 1,
                            'address_id' => $pendingOrder->address_id,
                            'order_type' => $pendingOrder->order_type,
                            'order_amount' => $pendingOrder->order_amount,
                            'start_date' => $pendingOrder->start_date,
                            'delivery_charge' => $pendingOrder->delivery_charge,
                            'delivery_instruction' => $pendingOrder->delivery_instruction,
                            'payment_id' => $orderData['id'] ?? null,
                            'payment_type' => $pendingOrder->payment_type,
                            'payment_mode' => $pendingOrder->payment_mode,
                            'Payment_description' => $pendingOrder->payment_description ?? '',
                            'razorpay_order_id' => $razorpayOrderId,
                            'product_details' => $pendingOrder->product_details,
                            'coupon_id' => $pendingOrder->coupon_id ?? null,
                            'coupon_discount_value' => $pendingOrder->coupon_discount_value ?? 0,
                            'wallet_added_amount' => $pendingOrder->wallet_added_amount ?? 0,
                        ]);

                        $response = $orderController->addRazorpayCardAndOrderData($orderRequest);
                    } else {
                        // Subscription order
                        $orderRequest = new Request([
                            'user_id' => $pendingOrder->user_id,
                            'status' => $pendingOrder->status,
                            'qty' => $pendingOrder->qty,
                            'price' => $pendingOrder->price,
                            'mrp' => $pendingOrder->mrp,
                            'tax' => $pendingOrder->tax,
                            'product_id' => $pendingOrder->product_id,
                            'address_id' => $pendingOrder->address_id,
                            'order_type' => $pendingOrder->order_type,
                            'order_amount' => $pendingOrder->order_amount,
                            'start_date' => $pendingOrder->start_date,
                            'delivery_charge' => $pendingOrder->delivery_charge,
                            'delivery_instruction' => $pendingOrder->delivery_instruction,
                            'payment_id' => $orderData['id'] ?? null,
                            'payment_type' => $pendingOrder->payment_type,
                            'payment_mode' => $pendingOrder->payment_mode,
                            'Payment_description' => $pendingOrder->payment_description ?? '',
                            'subscription_type' => $pendingOrder->subscription_type,
                            'selected_days_for_weekly' => $pendingOrder->selected_days_for_weekly,
                            'razorpay_order_id' => $razorpayOrderId,
                            'coupon_id' => $pendingOrder->coupon_id ?? null,
                            'coupon_discount_value' => $pendingOrder->coupon_discount_value ?? 0,
                            'wallet_added_amount' => $pendingOrder->wallet_added_amount ?? 0,
                            'isFromrazorpay' => true,
                        ]);

                        $response = $orderController->addOrderData($orderRequest);
                    }
                });
            } catch (\Exception $e) {
                Log::error('Error handling Razorpay webhook: ' . $e->getMessage());
                return response('Error', 500);
            }
        }

        return response('Webhook handled', 200);
    }
}
