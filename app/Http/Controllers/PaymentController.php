<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function createRazorpayOrder(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'orderData' => 'nullable|array',
        ]);

        $amount = $request->amount; // in paisa
        $gatewayId = 1;

        $gateway = DB::table("payment_gateway")
            ->where("id", $gatewayId)
            ->where("active", 1)
            ->first();

        if (!$gateway) {
            return response()->json([
                "response" => 404,
                "status" => false,
                "message" => "Payment gateway not found or inactive"
            ], 404);
        }

        // Call Razorpay API
        $response = Http::withBasicAuth($gateway->key_id, $gateway->secret_id)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $amount,
                'currency' => 'INR',
                'receipt' => 'txn_' . time(),
                'payment_capture' => 1,
            ]);

        if ($response->successful()) {
            $razorpayOrderId = $response['id'];

            if ($request->has('orderData')) {
                $data = $request->orderData;

                $productDetail = [];
                $totalQty = 0;
                $dataCart = DB::table("cart")->where("user_id", $data['user_id'])->get();

                foreach ($dataCart as $newDataCart) {
                    $productTitle = DB::table('product')->where('id', $newDataCart->product_id)->value('title');
                    $totalQty += $newDataCart->qty;
                    $priceWithTax = $newDataCart->price * (1 + $newDataCart->tax / 100);

                    $productDetail[] = [
                        'product_id' => (string) $newDataCart->product_id,
                        'product_title' => (string) $productTitle,
                        'mrp' => (string) $newDataCart->mrp,
                        'tax' => (string) $newDataCart->tax,
                        'qty' => (string) $newDataCart->qty,
                        'price' => (string) $newDataCart->price,
                        'total_price' => (string) ($priceWithTax * $newDataCart->qty)
                    ];
                }

                DB::table('pending_orders')->insert([
                    'user_id' => auth()->id() ?? ($data['user_id'] ?? null),
                    'address_id' => $data['address_id'] ?? null,
                    'razorpay_order_id' => $razorpayOrderId,
                    'payment_id' => $data['payment_id'] ?? null,
                    'payment_type' => $data['payment_type'] ?? null,
                    'payment_description' => $data['payment_description'] ?? null,
                    'payment_mode' => $data['payment_mode'] ?? 0,
                    'payment_status' => "pending",
                    'status' => $data['status'],
                    'order_amount' => $data['order_amount'] ?? ($amount / 100),
                    'delivery_charge' => $data['delivery_charge'] ?? 0,
                    'order_type' => $data['order_type'] ?? null,
                    'start_date' => $data['start_date'] ?? null,
                    'delivery_instruction' => $data['delivery_instruction'] ?? null,
                    'subscription_type' => $data['subscription_type'] ?? null,
                    'selected_days_for_weekly' => (!empty($data['selected_days_for_weekly']) && $data['selected_days_for_weekly'] !== 'null')
                        ? $data['selected_days_for_weekly']
                        : null,

                    'qty' => $data['qty'] ?? null,
                    'mrp' => $data['mrp'] ?? null,
                    'price' => $data['price'] ?? null,
                    'tax' => $data['tax'] ?? null,
                    'product_id' => $data['product_id'] ?? null,
                    'wallet_added_amount' => $data['wallet_added_amount'] ?? 0,
                    'product_details' => json_encode($productDetail),
                    'coupon_id' => $data['coupon_id'] ?? null,
                    'coupon_discount_value' => isset($data['coupon_id'], $data['coupon_discount_value']) ? $data['coupon_discount_value'] : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                "response" => 200,
                "status" => true,
                "message" => "Order created successfully",
                "data" => [
                    "order_id" => $razorpayOrderId,
                    "amount" => $response['amount'],
                    "currency" => $response['currency'],
                ],
            ]);
        }

        return response()->json([
            "response" => 500,
            "status" => false,
            "message" => "Order creation failed",
            "error" => $response->body(),
        ], 500);
    }
}
