<?php

namespace App\Http\Controllers;

use App\Helpers\notificationHelper;
use App\Models\SubOderDeliveyModel;
use Illuminate\Http\Request;
use App\Models\OrderModel;
use App\Models\CartModel;
use App\Models\TransactionsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ProductModel;
use App\Http\Controllers\WebAppSettingsController;
use App\Models\User;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class OrderController extends Controller
{
  function getDataById($id)
  {
    try {
      $today = now()->toDateString();

      $data = DB::table("orders")
        ->select(
          'orders.id',
          'orders.user_id',
          'orders.product_id',
          'orders.address_id',
          'orders.created_at',
          'orders.order_type',
          'orders.order_status',
          'orders.delivery_status',
          'orders.order_number',
          'orders.pause_dates',
          'orders.resume_dates',
          'orders.start_date',
          'orders.subscription_type',
          'orders.trasation_id',
          'orders.order_amount',
          'orders.delivery_charge',
          'orders.qty',
          'orders.price',
          'orders.mrp',
          'orders.tax',
          'orders.status',
          'orders.delivery_instruction',
          'orders.selected_days_for_weekly',
          'orders.coupon_discount_value',

          'product.title',
          'product.qty_text',

          'user_address.name',
          'user_address.s_phone',
          'user_address.flat_no',
          'user_address.apartment_name',
          'user_address.area',
          'user_address.city',
          'user_address.pincode',
          'user_address.landmark',

          DB::raw('GROUP_CONCAT(DISTINCT images.image ORDER BY images.id ASC SEPARATOR ", ") AS product_image'),
          DB::raw('GROUP_CONCAT(DISTINCT delivery_executive.name ORDER BY delivery_executive.id ASC SEPARATOR ", ") AS executive_name'),
          DB::raw('GROUP_CONCAT(DISTINCT delivery_executive.phn_no1 ORDER BY delivery_executive.id ASC SEPARATOR ", ") AS executive_phone')
        )
        ->leftJoin('images', function ($join) {
          $join->on('images.table_id', '=', 'orders.product_id')
            ->where('images.table_name', '=', "product")
            ->where('images.image_type', '=', 1);
        })
        ->join('product', 'orders.product_id', '=', 'product.id')
        ->join('user_address', 'user_address.id', '=', 'orders.address_id')
        ->leftJoin('delivery_executive_orders', function ($join) use ($today) {
          $join->on('delivery_executive_orders.order_id', '=', 'orders.id')
            ->where('delivery_executive_orders.assigned_date', '=', $today);
        })
        ->leftJoin('delivery_executive', 'delivery_executive.id', '=', 'delivery_executive_orders.delivery_executive_id')
        ->where("orders.id", "=", $id)
        ->groupBy(
          'orders.id',
          'orders.user_id',
          'orders.product_id',
          'orders.address_id',
          'orders.created_at',
          'orders.order_type',
          'orders.order_status',
          'orders.delivery_status',
          'orders.order_number',
          'orders.pause_dates',
          'orders.resume_dates',
          'orders.start_date',
          'orders.subscription_type',
          'orders.trasation_id',
          'orders.order_amount',
          'orders.delivery_charge',
          'orders.qty',
          'orders.price',
          'orders.mrp',
          'orders.tax',
          'orders.status',
          'orders.delivery_instruction',
          'orders.selected_days_for_weekly',
          'orders.coupon_discount_value',

          'product.title',
          'product.qty_text',

          'user_address.name',
          'user_address.s_phone',
          'user_address.flat_no',
          'user_address.apartment_name',
          'user_address.area',
          'user_address.city',
          'user_address.pincode',
          'user_address.landmark'
        )
        ->orderBy('orders.created_at', 'DESC')
        ->first();

      // Fetch delivery dates
      $deliveryDates = SubOderDeliveyModel::where('order_id', $id)
        ->orderBy('date', 'ASC')
        ->pluck('date');

      if ($data) {
        $data->deliveryDates = $deliveryDates;

        // Optional: Add product_detail logic
        $data->product_detail = json_encode([]); // placeholder

        // ✅ Fetch coupon from coupon_usages + coupons
        if (Schema::hasTable('coupon_usages') && Schema::hasTable('coupons')) {
          $coupon = DB::table('coupon_usages')
            ->join('coupons', 'coupon_usages.coupon_id', '=', 'coupons.id')
            ->where('coupon_usages.order_id', $data->id)
            ->select('coupons.id', 'coupons.code', 'coupons.type', 'coupons.value')
            ->first();

          $data->coupon = $coupon;
        }
      }

      return response()->json([
        "response" => 200,
        'data' => $data,
      ], 200);
    } catch (Exception $e) {
      return response()->json([
        "response" => 500,
        "error" => "An error occurred: " . $e->getMessage(),
      ], 500);
    }
  }


  // function getCartDataById($id)
  // {
  //   // $today = now()->addDay()->toDateString();
  //   // Start building the query
  //   $query = DB::table("orders")
  //     ->select(
  //       'orders.*',
  //       'user_address.name',
  //       'user_address.s_phone',
  //       'user_address.flat_no',
  //       'user_address.apartment_name',
  //       'user_address.area',
  //       'user_address.city',
  //       'user_address.pincode',
  //       'user_address.lat',
  //       'user_address.lng',
  //       'delivery_executive.name as executive_name',
  //       'delivery_executive.phn_no1 as executive_phone',
  //     )
  //     ->leftJoin('images', function ($join) {
  //       $join->on('images.table_id', '=', 'orders.product_id')
  //         ->where('images.table_name', '=', "product")
  //         ->where('images.image_type', '=', 1);
  //     })
  //     ->join('user_address', 'user_address.id', '=', 'orders.address_id')
  //     ->leftJoin('delivery_executive_orders', 'delivery_executive_orders.order_id', '=', 'orders.id')
  //     ->leftJoin('delivery_executive', 'delivery_executive.id', '=', 'delivery_executive_orders.delivery_executive_id')
  //     ->where("orders.id", "=", $id)
  //     ->whereNull('orders.subscription_type');

  //   // Check if the order has a delivery executive assigned
  //   $hasExecutive = DB::table('delivery_executive_orders')
  //     ->where('order_id', $id)
  //     ->exists();

  //   // Finalize the query and fetch the data
  //   $data = $query->orderBy('orders.created_at', 'DESC')->first();

  //   // Prepare the response
  //   $response = [
  //     "response" => 200,
  //     'data' => $data,
  //   ];

  //   return response($response, 200);
  // }


  public function getCartDataById($id)
  {
    try {
      $query = DB::table("orders")
        ->select(
          'orders.*',
          'user_address.name',
          'user_address.s_phone',
          'user_address.flat_no',
          'user_address.apartment_name',
          'user_address.area',
          'user_address.city',
          'user_address.pincode',
          'user_address.lat',
          'user_address.lng',
          'delivery_executive.name as executive_name',
          'delivery_executive.phn_no1 as executive_phone'
        )
        ->leftJoin('images', function ($join) {
          $join->on('images.table_id', '=', 'orders.product_id')
            ->where('images.table_name', '=', "product")
            ->where('images.image_type', '=', 1);
        })
        ->join('user_address', 'user_address.id', '=', 'orders.address_id')
        ->leftJoin('delivery_executive_orders', 'delivery_executive_orders.order_id', '=', 'orders.id')
        ->leftJoin('delivery_executive', 'delivery_executive.id', '=', 'delivery_executive_orders.delivery_executive_id')
        ->where("orders.id", "=", $id)
        ->whereNull('orders.subscription_type');

      $data = $query->orderBy('orders.created_at', 'DESC')->first();

      if ($data) {
        // Attach coupon data if exists
        if (Schema::hasTable('coupon_usages') && Schema::hasTable('coupons')) {
          $coupon = DB::table('coupon_usages')
            ->join('coupons', 'coupon_usages.coupon_id', '=', 'coupons.id')
            ->where('coupon_usages.order_id', $data->id)
            ->select('coupons.id', 'coupons.code', 'coupons.type', 'coupons.value')
            ->first();

          $data->coupon = $coupon;
        }
      }

      return response([
        "response" => 200,
        'data' => $data,
      ], 200);
    } catch (Exception $e) {
      Log::error("Get Cart Data By ID Error: " . $e->getMessage());

      return response([
        "response" => 500,
        'status' => false,
        'message' => "Failed to retrieve cart order data"
      ], 500);
    }
  }


  function getCartProductDataById($id)
  {
    $productDetail = DB::table('orders')
      ->where('id', $id)
      ->value('product_detail');
    $productDetailList = json_decode($productDetail, true);

    $productIds = array_map(function ($item) {
      return (int)$item['product_id'];
    }, $productDetailList);

    $productInfo = DB::table('product')
      ->select('product.id', 'product.title', 'product.qty_text', 'images.image as product_image')
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'product.id')
          ->where('images.table_name', '=', "product")
          ->where('images.image_type', '=', 1);
      })
      ->whereIn('product.id', $productIds) // Use your desired product IDs
      ->get();

    $productDetailsMap = [];
    foreach ($productDetailList as $product) {
      $productDetailsMap[$product['product_id']] = [
        'qty' => $product['qty'] ?? "",
        'price' => $product['price'] ?? "",
        'mrp' => $product['mrp'] ?? "",
        'total_price' => $product['total_price'] ?? "",
        'tax' => $product['tax'] ?? "",
      ];
    }
    $data = $productInfo->map(function ($product) use ($productDetailsMap) {
      $productId = (string)$product->id;
      if (isset($productDetailsMap[$productId])) {
        $product->qty = (int) $productDetailsMap[$productId]['qty'] ?? "";
        $product->price = $productDetailsMap[$productId]['price'] ?? "";
        $product->mrp = $productDetailsMap[$productId]['mrp'] ?? "";
        $product->total_price = $productDetailsMap[$productId]['total_price'] ?? "";
        $product->tax = $productDetailsMap[$productId]['tax'] ?? "";
      }
      return $product;
    });

    $response = [
      "response" => 200,
      "data" => $data,
    ];

    return response($response, 200);
  }

  private function generatePaymentId()
  {
    return 'txn_' . date('YmdHis'); // Format: txn_YYYYMMDDHHMMSS
  }

  // public function getOrderProductsByDateRange(Request $request)
  // {
  //   try {
  //     $request->validate([
  //       'start_date' => 'required|date',
  //       'end_date' => 'required|date|after_or_equal:start_date'
  //     ]);

  //     $startDate = Carbon::parse($request->start_date)->startOfDay();
  //     $endDate = Carbon::parse($request->end_date)->endOfDay();

  //     $orders = DB::table('orders')
  //       ->where(function ($query) use ($startDate, $endDate) {
  //         $query->whereBetween('start_date', [$startDate, $endDate])
  //           ->orWhereNotNull('subscription_type');
  //       })
  //       ->get();

  //     $filteredProducts = [];
  //     $mergedProducts = [];

  //     foreach ($orders as $order) {
  //       try {
  //         $subscriptionStart = Carbon::parse($order->start_date);
  //         $pauseDates = [];

  //         if (is_null($order->subscription_type)) {
  //           $productDetails = json_decode($order->product_detail, true);
  //           if (json_last_error() !== JSON_ERROR_NONE) continue;

  //           foreach ($productDetails as $product) {
  //             $filteredProducts[] = $this->formatOneTimeProductData($order, $product);
  //           }
  //         } else {
  //           // ✅ Subscription Orders Handling
  //           if ($order->pause_dates) {
  //             $pauseDates = array_map('trim', explode(',', trim($order->pause_dates, '[]')));
  //           }

  //           $subscriptionEnd = $this->calculateEndDate($subscriptionStart, $order, $pauseDates);
  //           if ($order->subscription_type == 1) {
  //             if ($subscriptionStart->between($startDate, $endDate)) {
  //               $filteredProducts[] = $this->formatSubscriptionProductData($order, $subscriptionStart->toDateString());
  //             }
  //           } elseif ($order->subscription_type == 2) {
  //             $selectedDaysJson = preg_replace('/(\w+):/', '"$1":', $order->selected_days_for_weekly);
  //             $selectedDays = json_decode($selectedDaysJson, true);

  //             if (json_last_error() !== JSON_ERROR_NONE) {
  //               Log::error("JSON decode error for selected_days_for_weekly in Order ID: {$order->id}");
  //               continue;
  //             }

  //             $selectedDayCodes = array_column($selectedDays, 'dayCode'); // Extract day codes

  //             for ($date = $subscriptionStart->copy(); $date <= $subscriptionEnd; $date->addDay()) {
  //               $dayCode = ($date->dayOfWeekIso % 7); // Convert to your system’s 0-6 format

  //               if (in_array($dayCode, $selectedDayCodes) && $date->between($startDate, $endDate)) {
  //                 $filteredProducts[] = $this->formatSubscriptionProductData($order, $date->toDateString(), $selectedDays[array_search($dayCode, array_column($selectedDays, 'dayCode'))]['qty']);
  //               }
  //             }
  //           } elseif ($order->subscription_type == 3) {
  //             for ($date = $subscriptionStart->copy(); $date <= $subscriptionEnd; $date->addDay()) {
  //               if ($date->between($startDate, $endDate)) {
  //                 $filteredProducts[] = $this->formatSubscriptionProductData($order, $date->toDateString());
  //               }
  //             }
  //           } elseif ($order->subscription_type == 4) {
  //             for ($date = $subscriptionStart->copy(); $date <= $subscriptionEnd; $date->addDays(2)) {
  //               if ($date->between($startDate, $endDate)) {
  //                 $filteredProducts[] = $this->formatSubscriptionProductData($order, $date->toDateString());
  //               }
  //             }
  //           }
  //         }
  //       } catch (\Exception $e) {
  //         Log::error("Error processing order ID {$order->id}: " . $e->getMessage());
  //       }
  //     }

  //     // 🔹 Merge Products by `product_id`
  //     foreach ($filteredProducts as $product) {
  //       $productId = $product['product_id'];

  //       if (!isset($mergedProducts[$productId])) {
  //         $mergedProducts[$productId] = [
  //           'product_id' => $productId,
  //           'product_name' => $product['product_name'],
  //           'image' => $product['image'],
  //           'total_qty' => 0,
  //           'price' => $product['price'],
  //           'mrp' => $product['mrp'],
  //           'tax' => $product['tax'],
  //           'total_price' => 0
  //         ];
  //       }

  //       // ✅ Add quantity only in merged products
  //       $mergedProducts[$productId]['total_qty'] += $product['qty'];
  //       $mergedProducts[$productId]['total_price'] += $product['total_price'];
  //     }

  //     // Convert associative array to indexed array
  //     $finalMergedProducts = array_values($mergedProducts);
  //     $vendorList = DB::table('vendor')
  //     ->select('id as vendor_id', 'supplier_name as vendor_name')
  //     ->get();
  //     return response([
  //       "response" => 200,
  //       "data" => [
  //         "order_details" => $filteredProducts,
  //         "merged_products" => $finalMergedProducts
  //       ]
  //     ], 200);
  //   } catch (\Exception $e) {
  //     Log::error("Error fetching orders: " . $e->getMessage());
  //     return response([
  //       "response" => 500,
  //       "error" => "An error occurred while processing the request."
  //     ], 500);
  //   }
  // }


  // public function getOrderProductsByDateRange(Request $request)
  // {
  //   try {
  //     $request->validate([
  //       'start_date' => 'required|date',
  //       'end_date' => 'required|date|after_or_equal:start_date',
  //       'vendor_id' => 'nullable|integer' // ✅ Optional vendor_id filter
  //     ]);

  //     $startDate = Carbon::parse($request->start_date)->startOfDay();
  //     $endDate = Carbon::parse($request->end_date)->endOfDay();
  //     $vendorId = $request->vendor_id; // ✅ Get vendor_id from request

  //     $orders = DB::table('orders')
  //       ->where(function ($query) use ($startDate, $endDate) {
  //         $query->whereBetween('start_date', [$startDate, $endDate])
  //           ->orWhereNotNull('subscription_type');
  //       })
  //       ->get();

  //     $filteredProducts = [];
  //     $mergedProducts = [];

  //     foreach ($orders as $order) {
  //       try {
  //         $subscriptionStart = Carbon::parse($order->start_date);
  //         $pauseDates = [];

  //         if (is_null($order->subscription_type)) {
  //           $productDetails = json_decode($order->product_detail, true);
  //           if (json_last_error() !== JSON_ERROR_NONE) continue;

  //           foreach ($productDetails as $product) {
  //             $productData = $this->formatOneTimeProductData($order, $product);

  //             if ($vendorId && $productData['vendor_id'] != $vendorId) {
  //               continue; // ✅ Skip if vendor_id doesn't match
  //             }

  //             $filteredProducts[] = $productData;
  //           }
  //         } else {
  //           if ($order->pause_dates) {
  //             $pauseDates = array_map('trim', explode(',', trim($order->pause_dates, '[]')));
  //           }

  //           $subscriptionEnd = $this->calculateEndDate($subscriptionStart, $order, $pauseDates);
  //           if ($order->subscription_type == 1) {
  //             if ($subscriptionStart->between($startDate, $endDate)) {
  //               $productData = $this->formatSubscriptionProductData($order, $subscriptionStart->toDateString());

  //               if ($vendorId && $productData['vendor_id'] != $vendorId) {
  //                 continue; // ✅ Skip if vendor_id doesn't match
  //               }

  //               $filteredProducts[] = $productData;
  //             }
  //           } elseif ($order->subscription_type == 2) {
  //             $selectedDaysJson = preg_replace('/(\w+):/', '"$1":', $order->selected_days_for_weekly);
  //             $selectedDays = json_decode($selectedDaysJson, true);

  //             if (json_last_error() !== JSON_ERROR_NONE) {
  //               Log::error("JSON decode error for selected_days_for_weekly in Order ID: {$order->id}");
  //               continue;
  //             }

  //             $selectedDayCodes = array_column($selectedDays, 'dayCode');

  //             for ($date = $subscriptionStart->copy(); $date <= $subscriptionEnd; $date->addDay()) {
  //               $dayCode = ($date->dayOfWeekIso % 7);

  //               if (in_array($dayCode, $selectedDayCodes) && $date->between($startDate, $endDate)) {
  //                 $productData = $this->formatSubscriptionProductData(
  //                   $order,
  //                   $date->toDateString(),
  //                   $selectedDays[array_search($dayCode, array_column($selectedDays, 'dayCode'))]['qty']
  //                 );

  //                 if ($vendorId && $productData['vendor_id'] != $vendorId) {
  //                   continue; // ✅ Skip if vendor_id doesn't match
  //                 }

  //                 $filteredProducts[] = $productData;
  //               }
  //             }
  //           } elseif ($order->subscription_type == 3) {
  //             for ($date = $subscriptionStart->copy(); $date <= $subscriptionEnd; $date->addDay()) {
  //               if ($date->between($startDate, $endDate)) {
  //                 $productData = $this->formatSubscriptionProductData($order, $date->toDateString());

  //                 if ($vendorId && $productData['vendor_id'] != $vendorId) {
  //                   continue; // ✅ Skip if vendor_id doesn't match
  //                 }

  //                 $filteredProducts[] = $productData;
  //               }
  //             }
  //           } elseif ($order->subscription_type == 4) {
  //             for ($date = $subscriptionStart->copy(); $date <= $subscriptionEnd; $date->addDays(2)) {
  //               if ($date->between($startDate, $endDate)) {
  //                 $productData = $this->formatSubscriptionProductData($order, $date->toDateString());

  //                 if ($vendorId && $productData['vendor_id'] != $vendorId) {
  //                   continue; // ✅ Skip if vendor_id doesn't match
  //                 }

  //                 $filteredProducts[] = $productData;
  //               }
  //             }
  //           }
  //         }
  //       } catch (Exception $e) {
  //         Log::error("Error processing order ID {$order->id}: " . $e->getMessage());
  //       }
  //     }

  //     // 🔹 Merge Products by `product_id`
  //     foreach ($filteredProducts as $product) {
  //       $productId = $product['product_id'];

  //       if (!isset($mergedProducts[$productId])) {
  //         $mergedProducts[$productId] = [
  //           'product_id' => $productId,
  //           'product_name' => $product['product_name'],
  //           'image' => $product['image'],
  //           'vendor_id' => $product['vendor_id'], // ✅ Include vendor_id
  //           'vendor_name' => $product['vendor_name'], // ✅ Include vendor_name
  //           'total_qty' => 0,
  //           'price' => $product['price'],
  //           'mrp' => $product['mrp'],
  //           'tax' => $product['tax'],
  //           'total_price' => 0
  //         ];
  //       }

  //       // ✅ Add quantity only in merged products
  //       $mergedProducts[$productId]['total_qty'] += $product['qty'];
  //       $mergedProducts[$productId]['total_price'] += $product['total_price'];
  //     }

  //     // Convert associative array to indexed array
  //     $finalMergedProducts = array_values($mergedProducts);
  //     $vendorList = DB::table('vendor')
  //       ->select('id as vendor_id', 'supplier_name as vendor_name')
  //       ->get();
  //     return response([
  //       "response" => 200,
  //       "data" => [
  //         "order_details" => $filteredProducts,
  //         "merged_products" => $finalMergedProducts,
  //         "vendor_details" => $vendorList
  //       ]
  //     ], 200);
  //   } catch (Exception $e) {
  //     Log::error("Error fetching orders: " . $e->getMessage());
  //     return response([
  //       "response" => 500,
  //       "error" => "An error occurred while processing the request."
  //     ], 500);
  //   }
  // }


  public function getOrderProductsByDateRange(Request $request)
  {
    try {
      $request->validate([
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'vendor_id' => 'nullable|integer'
      ]);

      $startDate = Carbon::parse($request->start_date)->startOfDay();
      $endDate = Carbon::parse($request->end_date)->endOfDay();
      $vendorId = $request->vendor_id;

      $orders = DB::table('orders')
        ->where(function ($query) use ($startDate, $endDate) {
          $query->whereBetween('start_date', [$startDate, $endDate])
            ->orWhereNotNull('subscription_type');
        })
        ->get();

      $filteredProducts = [];
      $mergedProducts = [];

      foreach ($orders as $order) {
        try {
          $subscriptionStart = Carbon::parse($order->start_date);
          $pauseDates = [];

          if (is_null($order->subscription_type)) {
            $productDetails = json_decode($order->product_detail, true);
            if (json_last_error() !== JSON_ERROR_NONE) continue;

            foreach ($productDetails as $product) {
              $productData = $this->formatOneTimeProductData($order, $product);

              // ✅ Null check before pushing
              if (!$productData || !isset($productData['product_id'], $productData['qty'], $productData['total_price'])) {
                Log::warning("Skipping null/invalid product data (One-time) for Order ID: {$order->id}");
                continue;
              }

              if ($vendorId && $productData['vendor_id'] != $vendorId) {
                continue;
              }

              $filteredProducts[] = $productData;
            }
          } else {
            if ($order->pause_dates) {
              $pauseDates = array_map('trim', explode(',', trim($order->pause_dates, '[]')));
            }

            $subscriptionEnd = $this->calculateEndDate($subscriptionStart, $order, $pauseDates);

            if ($order->subscription_type == 1) {
              if ($subscriptionStart->between($startDate, $endDate)) {
                $productData = $this->formatSubscriptionProductData($order, $subscriptionStart->toDateString());

                if (!$productData || !isset($productData['product_id'], $productData['qty'], $productData['total_price'])) {
                  Log::warning("Skipping invalid productData (type 1) for Order ID: {$order->id}");
                  continue;
                }

                if ($vendorId && $productData['vendor_id'] != $vendorId) {
                  continue;
                }

                $filteredProducts[] = $productData;
              }
            } elseif ($order->subscription_type == 2) {
              if (empty($order->selected_days_for_weekly)) {
                Log::warning("Empty selected_days_for_weekly for Order ID: {$order->id}");
                continue;
              }

              $selectedDaysString = $order->selected_days_for_weekly;
              $selectedDays = json_decode($selectedDaysString, true);

              if (json_last_error() !== JSON_ERROR_NONE) {
                $selectedDaysJson = preg_replace('/(\w+):/', '"$1":', $selectedDaysString);
                $selectedDays = json_decode($selectedDaysJson, true);
              }

              if (json_last_error() !== JSON_ERROR_NONE || empty($selectedDays)) {
                Log::error("JSON decode error for selected_days_for_weekly in Order ID: {$order->id}. Data: " . $selectedDaysString);
                continue;
              }

              $selectedDayCodes = array_column($selectedDays, 'dayCode');
              if (empty($selectedDayCodes)) {
                Log::warning("Empty selectedDayCodes for Order ID: {$order->id}");
                continue;
              }

              for ($date = $subscriptionStart->copy(); $date <= $subscriptionEnd; $date->addDay()) {
                $dayCode = ($date->dayOfWeekIso % 7);
                if (in_array($dayCode, $selectedDayCodes) && $date->between($startDate, $endDate)) {
                  $qty = 1;
                  foreach ($selectedDays as $dayData) {
                    if (isset($dayData['dayCode']) && $dayData['dayCode'] == $dayCode) {
                      $qty = isset($dayData['qty']) ? (int)$dayData['qty'] : 1;
                      break;
                    }
                  }

                  $productData = $this->formatSubscriptionProductData($order, $date->toDateString(), $qty);

                  if (!$productData || !isset($productData['product_id'], $productData['qty'], $productData['total_price'])) {
                    Log::warning("Skipping invalid productData (type 2) for Order ID: {$order->id}");
                    continue;
                  }

                  if ($vendorId && $productData['vendor_id'] != $vendorId) {
                    continue;
                  }

                  $filteredProducts[] = $productData;
                }
              }
            } elseif ($order->subscription_type == 3) {
              for ($date = $subscriptionStart->copy(); $date <= $subscriptionEnd; $date->addDay()) {
                if ($date->between($startDate, $endDate)) {
                  $productData = $this->formatSubscriptionProductData($order, $date->toDateString());

                  if (!$productData || !isset($productData['product_id'], $productData['qty'], $productData['total_price'])) {
                    Log::warning("Skipping invalid productData (type 3) for Order ID: {$order->id}");
                    continue;
                  }

                  if ($vendorId && $productData['vendor_id'] != $vendorId) {
                    continue;
                  }

                  $filteredProducts[] = $productData;
                }
              }
            } elseif ($order->subscription_type == 4) {
              for ($date = $subscriptionStart->copy(); $date <= $subscriptionEnd; $date->addDays(2)) {
                if ($date->between($startDate, $endDate)) {
                  $productData = $this->formatSubscriptionProductData($order, $date->toDateString());

                  if (!$productData || !isset($productData['product_id'], $productData['qty'], $productData['total_price'])) {
                    Log::warning("Skipping invalid productData (type 4) for Order ID: {$order->id}");
                    continue;
                  }

                  if ($vendorId && $productData['vendor_id'] != $vendorId) {
                    continue;
                  }

                  $filteredProducts[] = $productData;
                }
              }
            }
          }
        } catch (Exception $e) {
          Log::error("Error processing order ID {$order->id}: " . $e->getMessage());
        }
      }

      // ✅ Merge logic with null/key checks
      foreach ($filteredProducts as $product) {
        if (!is_array($product) || !isset($product['product_id'], $product['qty'], $product['total_price'])) {
          Log::warning("Invalid product during merging: " . json_encode($product));
          continue;
        }

        $productId = $product['product_id'];

        if (!isset($mergedProducts[$productId])) {
          $mergedProducts[$productId] = [
            'product_id' => $productId,
            'product_name' => $product['product_name'],
            'image' => $product['image'],
            'vendor_id' => $product['vendor_id'],
            'vendor_name' => $product['vendor_name'],
            'total_qty' => 0,
            'price' => $product['price'],
            'mrp' => $product['mrp'],
            'tax' => $product['tax'],
            'total_price' => 0
          ];
        }

        $mergedProducts[$productId]['total_qty'] += $product['qty'];
        $mergedProducts[$productId]['total_price'] += $product['total_price'];
      }

      $finalMergedProducts = array_values($mergedProducts);
      $vendorList = DB::table('vendor')
        ->select('id as vendor_id', 'supplier_name as vendor_name')
        ->get();

      return response([
        "response" => 200,
        "data" => [
          "order_details" => $filteredProducts,
          "merged_products" => $finalMergedProducts,
          "vendor_details" => $vendorList
        ]
      ], 200);
    } catch (Exception $e) {
      Log::error("Error fetching orders: " . $e->getMessage());
      return response([
        "response" => 500,
        "error" => "An error occurred while processing the request."
      ], 500);
    }
  }


  private function formatOneTimeProductData($order, $product)
  {
    try {
      // ✅ Validate essential keys
      $requiredKeys = ['product_id', 'qty', 'price', 'mrp', 'tax', 'total_price'];
      foreach ($requiredKeys as $key) {
        if (!isset($product[$key])) {
          Log::warning("Missing key '{$key}' in product data for Order ID: {$order->id}");
          return null;
        }
      }

      $productData = DB::table('product')
        ->select(
          'product.id',
          'product.title',
          'product.qty_text',
          'images.image',
          'product.vendor_id',
          'vendor.supplier_name'
        )
        ->leftJoin('images', function ($join) {
          $join->on('images.table_id', '=', 'product.id')
            ->where('images.table_name', '=', 'product')
            ->where('images.image_type', '=', 1);
        })
        ->leftJoin('vendor', 'vendor.id', '=', 'product.vendor_id')
        ->where('product.id', (int)$product['product_id']) // cast to int
        ->first();

      if (!$productData) {
        Log::warning("Product not found for ID {$product['product_id']} in Order ID: {$order->id}");
        return null;
      }

      return [
        'order_id' => $order->id,
        'delivery_date' => $order->start_date,
        'product_id' => (int)$productData->id,
        'product_name' => $productData->title,
        'image' => $productData->image,
        'qty' => (int)$product['qty'],
        'price' => (float)$product['price'],
        'mrp' => (float)$product['mrp'],
        'tax' => (float)$product['tax'],
        'total_price' => (float)$product['total_price'],
        'order_type' => 'One-Time',
        'vendor_id' => $productData->vendor_id,
        'vendor_name' => $productData->supplier_name,
      ];
    } catch (Exception $e) {
      Log::error("Error fetching product details for order ID {$order->id}: " . $e->getMessage());
      return null;
    }
  }


  private function formatSubscriptionProductData($order, $deliveryDate, $quantity = null)
  {
    try {
      $productData = DB::table('product')
        ->select(
          'product.id',
          'product.title',
          'product.qty_text',
          'images.image',
          'product.vendor_id',
          'vendor.supplier_name' // Fetch vendor name
        )
        ->leftJoin('images', function ($join) {
          $join->on('images.table_id', '=', 'product.id')
            ->where('images.table_name', '=', 'product')
            ->where('images.image_type', '=', 1);
        })
        ->leftJoin('vendor', 'vendor.id', '=', 'product.vendor_id') // Join vendor table
        ->where('product.id', $order->product_id)
        ->first();

      if (!$productData) {
        return null;
      }

      return [
        'order_id' => $order->id,
        'delivery_date' => $deliveryDate,
        'product_id' => $productData->id,
        'product_name' => $productData->title,
        'image' => $productData->image,
        'qty' => $quantity ?? $order->qty,
        'price' => $order->price,
        'mrp' => $order->mrp,
        'tax' => $order->tax,
        'total_price' => $order->order_amount,
        'order_type' => 'Subscription',
        'vendor_id' => $productData->vendor_id,  // ✅ Vendor ID
        'vendor_name' => $productData->supplier_name, // ✅ Vendor Name
      ];
    } catch (Exception $e) {
      Log::error("Error fetching product details for order ID {$order->id}: " . $e->getMessage());
      return null;
    }
  }

  public function checkOrderCreated(Request $request)
  {
    $razorpay_order_id = $request->input('razorpay_order_id');
    $payment_id = $request->input('payment_id');

    $orderExists = DB::table('orders')
      ->where('razorpay_order_id', $razorpay_order_id)
      ->exists();

    $transactionExists = DB::table('transactions')
      ->where('payment_id', $payment_id)
      ->exists();

    $status = $orderExists || $transactionExists;

    $response = [
      "response" => 200,
      "data" => [
        "status" => $status,
        "message" => $status
          ? "Order or payment exists"
          : "Order not yet created or payment not found"
      ]
    ];

    return response($response, 200);
  }

  public function addRazorpayCardAndOrderData(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'user_id' => 'required',
      'status' => 'required',
      'address_id' => 'required',
      'order_type' => 'required',
      'order_amount' => 'required'
    ]);

    if ($validator->fails()) {
      return response(["response" => 400, "message" => "Validation failed"], 400);
    }

    DB::beginTransaction(); // Start transaction

    try {
      $productDetail = [];
      $dataCart = DB::table("cart")->where("user_id", $request->user_id)->get();
      $totalQty = 0;

      if ($dataCart->isEmpty() && !empty($request->razorpay_order_id)) {
        if (!empty($request->product_details)) {
          $productDetail = json_decode($request->product_details, true);
          foreach ($productDetail as $item) {
            $totalQty += isset($item['qty']) ? (int)$item['qty'] : 0;
          }
        } else {
          return response(["response" => 404, "status" => false, "message" => "No cart or product details found"], 404);
        }
      } else {
        foreach ($dataCart as $newDataCart) {
          $productTitle = DB::table('product')->where('id', $newDataCart->product_id)->value('title');
          $totalQty += $newDataCart->qty;
          $priceWithTax = $newDataCart->price * (1 + $newDataCart->tax / 100);
          $productDetail[] = [
            'product_id' => (string)$newDataCart->product_id,
            'product_title' => (string)$productTitle,
            'mrp' => (string)$newDataCart->mrp,
            'tax' => (string)$newDataCart->tax,
            'qty' => (string)$newDataCart->qty,
            'price' => (string)$newDataCart->price,
            'total_price' => (string)($priceWithTax * $newDataCart->qty)
          ];
        }
      }

      $timeStamp = now();
      $orderNumber = $this->generateOrderNumber();
      $webAppSettingsController = new WebAppSettingsController();
      $statusDataResponse = $webAppSettingsController->getDataDataByTitle("Auto Approve");
      $statusData = json_decode($statusDataResponse->getContent(), true);
      // Save Transaction
      $dataTransactionModel = new TransactionsModel;
      $dataTransactionModel->user_id = $request->user_id;
      $dataTransactionModel->payment_id = $request->payment_id ?? $this->generatePaymentId();
      $dataTransactionModel->amount = $request->order_amount;
      $dataTransactionModel->type = $request->payment_type;
      $dataTransactionModel->description = $request->Payment_description;
      $dataTransactionModel->created_at = $timeStamp;
      $dataTransactionModel->updated_at = $timeStamp;
      $dataTransactionModel->save();

      // Update Wallet
      if ($request->payment_mode == 0 && $request->wallet_added_amount > 0) {
        $dataModelUser = User::where("id", $request->user_id)->first();
        if ($dataModelUser && isset($request->payment_type)) {
          // if (in_array($request->payment_type, [1, 3])) {
          //   $dataModelUser->wallet_amount = ($dataModelUser->wallet_amount ?? 0) + $request->order_amount;
          // } else
          if ($request->payment_type == 2 && $dataModelUser->wallet_amount >= $request->order_amount) {
            $dataModelUser->wallet_amount -= $request->order_amount;
          }
          $dataModelUser->save();
        }
      }
      $result = ReferralController::completeReferral($request->user_id);
      if (!$result['success']) {
        Log::warning('Referral not applied: ' . $request->user_id . ' ' . $result['message']);
      }
      // Get the transaction ID
      $transactionId = $dataTransactionModel->id;
      // Save Order
      $dataModel = new OrderModel;
      $dataModel->user_id = $request->user_id;
      $dataModel->qty = $totalQty;
      $dataModel->price = $request->order_amount;
      $dataModel->order_amount = $request->order_amount;
      $dataModel->mrp = $request->order_amount;
      $dataModel->tax = 0;
      $dataModel->address_id = $request->address_id;
      $dataModel->status = $request->status;
      $dataModel->order_type = $request->order_type;
      $dataModel->order_number = $orderNumber;
      $dataModel->product_detail = json_encode($productDetail);
      $dataModel->start_date = $request->start_date ?? null;
      $dataModel->delivery_charge = $request->delivery_charge ?? null;
      $dataModel->delivery_instruction = $request->delivery_instruction ?? "";
      $dataModel->razorpay_order_id = $request->razorpay_order_id ?? null;
      $dataModel->trasation_id = $transactionId;
      if (isset($request->coupon_id)) {
        $dataModel->coupon_discount_value = $request->coupon_discount_value ?? 0;
      }
      $dataModel->created_at = $timeStamp;
      $dataModel->updated_at = $timeStamp;
      if ($statusData['response'] === 200 && isset($statusData['data']) && $statusData['data']['value'] === "true") {
        $dataModel->status = 1;
      }
      $dataModel->save();

      // Link Transaction to Order
      $dataTransactionModel->order_id = $dataModel->id;
      $dataTransactionModel->save();
      if (isset($request->coupon_id)) {
        $this->recordCouponUsage($request->coupon_id, $request->user_id, $dataModel->id, $request->coupon_discount_value);
      }
      // Delete Cart Items
      CartModel::where("user_id", $request->user_id)->delete();

      // Update Stock
      if (!$dataCart->isEmpty()) {
        foreach ($dataCart as $cartItem) {
          $dataModelProduct = ProductModel::where("id", $cartItem->product_id)->first();
          if ($dataModelProduct && $dataModelProduct->stock_qty >= $cartItem->qty) {
            $dataModelProduct->stock_qty -= $cartItem->qty;
            $dataModelProduct->save();
          }
        }
      } else {
        // Fallback to pending order product details
        foreach ($productDetail as $item) {
          $productId = $item['product_id'] ?? null;
          $qty = isset($item['qty']) ? (int)$item['qty'] : 0;

          if ($productId && $qty > 0) {
            $dataModelProduct = ProductModel::where("id", $productId)->first();
            if ($dataModelProduct && $dataModelProduct->stock_qty >= $qty) {
              $dataModelProduct->stock_qty -= $qty;
              $dataModelProduct->save();
            }
          }
        }
      }

      $this->sendNotification($dataModel);
      DB::commit();


      $response = [
        "response" => 200,
        'status' => true,
        'message' => "Order created successfully"
      ];

      return response($response, 200);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction if anything fails
      Log::error("Order Processing Error: " . $e->getMessage());
      return response(["response" => 500, "status" => false, "message" => "Order processing failed"], 500);
    }
  }


  function addCardAndOrderData(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'user_id' => 'required',
      'status' => 'required',
      'address_id' => 'required',
      'order_type' => 'required',
      'order_amount' => 'required'
    ]);

    if ($validator->fails()) {
      return response(["response" => 400, "message" => "Validation failed"], 400);
    }

    DB::beginTransaction(); // Start transaction

    try {
      $productDetail = [];
      $dataCart = DB::table("cart")->where("user_id", $request->user_id)->get();
      $totalQty = 0;
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

      $timeStamp = now();
      $orderNumber = $this->generateOrderNumber();
      $webAppSettingsController = new WebAppSettingsController();
      $statusDataResponse = $webAppSettingsController->getDataDataByTitle("Auto Approve");
      $statusData = json_decode($statusDataResponse->getContent(), true);
      // Save Transaction
      $dataTransactionModel = new TransactionsModel;
      $dataTransactionModel->user_id = $request->user_id;
      $dataTransactionModel->payment_id = $request->payment_id ?? $this->generatePaymentId();
      $dataTransactionModel->amount = $request->order_amount;
      $dataTransactionModel->source_type = $request->source_type ?? 2;
      $dataTransactionModel->type = $request->payment_type;
      $dataTransactionModel->description = $request->Payment_description;
      $dataTransactionModel->created_at = $timeStamp;
      $dataTransactionModel->updated_at = $timeStamp;
      $dataTransactionModel->save();

      // Update Wallet
      if ($request->payment_mode == 0) {
        $dataModelUser = User::where("id", $request->user_id)->first();
        $dataTransactionModel->previous_balance = $dataModelUser->wallet_amount ?? 0;
        if ($dataModelUser && isset($request->payment_type)) {
          // if (in_array($request->payment_type, [1, 3])) {
          //   $dataModelUser->wallet_amount = ($dataModelUser->wallet_amount ?? 0) + $request->order_amount;
          // } else
          if ($request->payment_type == 2 && $dataModelUser->wallet_amount >= $request->order_amount) {
            $dataModelUser->wallet_amount -= $request->order_amount;
          }
          $dataModelUser->save();
        }
      }
      // Get the transaction ID
      $transactionId = $dataTransactionModel->id;
      $result = ReferralController::completeReferral($request->user_id);
      if (!$result['success']) {
        Log::warning('Referral not applied: ' . $request->user_id . ' ' . $result['message']);
      }
      // Save Order
      $dataModel = new OrderModel;
      $dataModel->user_id = $request->user_id;
      $dataModel->qty = $totalQty;
      $dataModel->price = $request->order_amount;
      $dataModel->order_amount = $request->order_amount;
      $dataModel->mrp = $request->order_amount;
      $dataModel->tax = 0;
      $dataModel->address_id = $request->address_id;
      $dataModel->status = $request->status;
      $dataModel->order_type = $request->order_type;
      $dataModel->order_number = $orderNumber;
      $dataModel->product_detail = json_encode($productDetail);
      $dataModel->start_date = $request->start_date ?? null;
      $dataModel->delivery_charge = $request->delivery_charge ?? null;
      $dataModel->delivery_instruction = $request->delivery_instruction ?? "";
      $dataModel->razorpay_order_id = $request->razorpay_order_id ?? null;
      if (isset($request->coupon_id)) {
        $dataModel->coupon_discount_value = $request->coupon_discount_value ?? 0;
      }
      $dataModel->trasation_id = $transactionId;
      $dataModel->created_at = $timeStamp;
      $dataModel->updated_at = $timeStamp;
      if ($statusData['response'] === 200 && isset($statusData['data']) && $statusData['data']['value'] === "true") {
        $dataModel->status = 1;
      }
      $dataModel->save();
      if (isset($request->coupon_id)) {
        $this->recordCouponUsage($request->coupon_id, $request->user_id, $dataModel->id, $request->coupon_discount_value);
      }

      // Link Transaction to Order
      $dataTransactionModel->order_id = $dataModel->id;
      $dataTransactionModel->save();

      // Delete Cart Items
      CartModel::where("user_id", $request->user_id)->delete();

      // Update Stock
      foreach ($dataCart as $cartItem) {
        $dataModelProduct = ProductModel::where("id", $cartItem->product_id)->first();
        if ($dataModelProduct && $dataModelProduct->stock_qty >= $cartItem->qty) {
          $dataModelProduct->stock_qty -= $cartItem->qty;
          $dataModelProduct->save();
        }
      }
      $this->sendNotification($dataModel);
      DB::commit();


      $response = [
        "response" => 200,
        'status' => true,
        'message' => "Order created successfully"
      ];

      return response($response, 200);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction if anything fails
      Log::error("Order Processing Error: " . $e->getMessage());
      return response(["response" => 500, "status" => false, "message" => "Order processing failed"], 500);
    }
  }

  public function recordCouponUsage(int $couponId, int $userId, int $orderId, float $couponDiscountValue): bool
  {
    try {
      $alreadyUsed = DB::table('coupon_usages')
        ->where('coupon_id', $couponId)
        ->where('order_id', $orderId)
        ->exists();

      if (!$alreadyUsed) {
        DB::table('coupon_usages')->insert([
          'coupon_id' => $couponId,
          'user_id' => $userId,
          'order_id' => $orderId,
          'coupon_discount_value' => $couponDiscountValue ?? 0,
          'used_at' => now(),
          'created_at' => now(),
          'updated_at' => now(),
        ]);
      }

      return true;
    } catch (Exception $e) {
      Log::error("Failed to record coupon usage: " . $e->getMessage(), [
        'coupon_id' => $couponId,
        'user_id' => $userId,
        'order_id' => $orderId
      ]);
      return false;
    }
  }
  // Separate method for sending notification
  protected function sendNotification($dataModel)
  {
    try {
      if ($dataModel->subscription_type !== null) {
        // Subscription notification
        notificationHelper::subscriptionConfirmNotify(
          $dataModel->id,
          $dataModel->order_number,
          $dataModel->user_id
        );
      } else {
        // Buy Once notification
        notificationHelper::buyOnceConfirmNotify(
          $dataModel->id,
          $dataModel->order_number,
          $dataModel->user_id
        );
      }
    } catch (Exception $e) {
      // Log notification error but don't affect order creation
      Log::error("Notification Sending Error: " . $e->getMessage());
    }
  }

  function generateOrderNumber()
  {
    // Get the current date in the format YYYYMMDD
    $date = date('Ymd');

    // Count the number of orders placed today
    $orderCountToday = OrderModel::whereDate('created_at', '=', date('Y-m-d'))->count();

    // Increment the count for the new order (e.g., if 200 orders today, next will be 201)
    $nextOrderNumber = $orderCountToday + 1;

    // Concatenate the date and the order number for that day
    $orderNumber = $date . str_pad($nextOrderNumber, 2, '0', STR_PAD_LEFT);

    return "#" . $orderNumber;
  }

  // function getBuyOnceData()
  // {
  //   $selectFields = [
  //     'orders.*',
  //     'user_address.name',
  //     'user_address.s_phone',
  //     'user_address.flat_no',
  //     'user_address.apartment_name',
  //     'user_address.area',
  //     'user_address.city',
  //     'user_address.pincode',
  //     'users.wallet_amount'
  //   ];

  //   $data = DB::table("orders")
  //     ->select(array_merge($selectFields, [
  //       'transactions.payment_id', // Add payment_id from transactions table
  //       DB::raw('IF(subscribed_order_delivery.order_id IS NOT NULL, 1, 0) AS delivered') // Add delivered flag
  //     ]))
  //     ->whereNull('orders.subscription_type') // Fetch only non-subscription orders
  //     ->join('users', 'users.id', '=', 'orders.user_id') // Join users table
  //     ->join('user_address', 'user_address.id', '=', 'orders.address_id') // Join user_address table
  //     ->leftJoin('transactions', 'transactions.id', '=', 'orders.trasation_id') // Fetch payment_id using the correct relationship
  //     ->leftJoin('subscribed_order_delivery', 'subscribed_order_delivery.order_id', '=', 'orders.id') // Join for delivery status
  //     ->orderBy('orders.created_at', 'DESC') // Order by creation date of orders in descending order
  //     ->get();

  //   $data = collect($data);
  //   $response = [
  //     "response" => 200,
  //     'data' => $data,
  //   ];

  //   return response($response, 200);
  // }

  function getBuyOnceData()
  {
    $selectFields = [
      'orders.*',
      'user_address.name',
      'user_address.s_phone',
      'user_address.flat_no',
      'user_address.apartment_name',
      'user_address.area',
      'user_address.city',
      'user_address.pincode',
      'users.wallet_amount',
      'orders.start_date',
      'orders.created_at'
    ];

    $data = DB::table("orders")
      ->select(array_merge($selectFields, [
        'transactions.payment_id',
        DB::raw('IF(subscribed_order_delivery.order_id IS NOT NULL, 1, 0) AS delivered')
      ]))
      ->whereNull('orders.subscription_type')
      ->join('users', 'users.id', '=', 'orders.user_id')
      ->join('user_address', 'user_address.id', '=', 'orders.address_id')
      ->leftJoin('transactions', 'transactions.id', '=', 'orders.trasation_id')
      ->leftJoin('subscribed_order_delivery', 'subscribed_order_delivery.order_id', '=', 'orders.id')
      ->orderBy('orders.created_at', 'DESC')
      ->get();

    $data = collect($data);

    // Handle delivery date filtering
    $startFilter = request('startfilterdate');
    $endFilter = request('endfilterdate');

    if ($startFilter && $endFilter) {
      $startDate = Carbon::parse($startFilter)->startOfDay();
      $endDate = Carbon::parse($endFilter)->endOfDay();

      $data = $data->filter(function ($order) use ($startDate, $endDate) {
        $deliveryDate = $order->start_date
          ? Carbon::parse($order->start_date)
          : Carbon::parse($order->created_at)->addDay();

        return $deliveryDate->between($startDate, $endDate);
      });
    }

    // Add delivery_date to each item
    $data = $data->map(function ($order) {
      $deliveryDate = $order->start_date
        ? Carbon::parse($order->start_date)
        : Carbon::parse($order->created_at)->addDay();

      $order->delivery_date = $deliveryDate->toDateString();
      return $order;
    });

    return response([
      "response" => 200,
      "data" => $data->values()->all() // ensures it's a plain indexed array
    ], 200);
  }

  function getSubscriptionOrdereData()
  {
    $selectFields = [
      'orders.*',
      'user_address.name',
      'user_address.s_phone',
      'user_address.flat_no',
      'user_address.apartment_name',
      'user_address.area',
      'user_address.city',
      'user_address.pincode',
      'users.wallet_amount',
      'product.title',
      'product.qty_text',
      'images.image as product_image',
      'transactions.payment_id'
    ];

    // Fetch data from the orders table
    $data = DB::table("orders")
      ->select($selectFields)
      ->whereNotNull('orders.subscription_type') // Fetch subscription orders only
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'orders.product_id')
          ->where('images.table_name', '=', "product")
          ->where('images.image_type', '=', 1);
      })
      ->join('users', 'users.id', '=', 'orders.user_id')
      ->join('product', 'orders.product_id', '=', 'product.id')
      ->join('user_address', 'user_address.id', '=', 'orders.address_id')
      ->leftJoin('transactions', 'transactions.id', '=', 'orders.trasation_id')
      ->orderBy('orders.created_at', 'DESC')
      ->get();

    $data = collect($data);

    $data = $data->map(function ($order) {
      $startDate = Carbon::parse($order->start_date);
      $pauseDates = [];
      if ($order->pause_dates) {
        $pauseDates = array_map('trim', explode(',', trim($order->pause_dates, '[]')));
      }
      switch ($order->subscription_type) {
        case '1':
          $totalDeliveries = 1;
          $endDate = $this->calculateEndDate($startDate, $order, $pauseDates);
          break;
        case '2':
          $totalDeliveries = 7;
          $endDate = $endDate = $this->calculateEndDate($startDate, $order, $pauseDates);
          break;
        case '3':
          $totalDeliveries = 30;
          $endDate = $endDate = $this->calculateEndDate($startDate, $order, $pauseDates);
          break;
        case '4':
          $totalDeliveries = 15;
          $endDate = $endDate = $this->calculateEndDate($startDate, $order, $pauseDates);
          break;
        default:
          $totalDeliveries = 0;
          $endDate = null;
      }

      // Fetch deliveries from subscribed_order_delivery
      $deliveredCount = DB::table('subscribed_order_delivery')
        ->where('order_id', $order->id)
        ->count();

      // Calculate deliveries left
      $deliveriesLeft = $totalDeliveries - $deliveredCount;

      // Add delivery details and end date to the order
      $order->total_deliveries = $totalDeliveries;
      $order->delivered = $deliveredCount;
      $order->deliveries_left = $deliveriesLeft;
      $order->end_date = $endDate;

      return $order;
    });

    // Prepare response
    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  private static function calculateEndDate($startDate, $order, $pauseDates = [])
  {
    try {
      $startDate = Carbon::parse($startDate);
      $pausedaysDifference = count($pauseDates);

      switch ($order->subscription_type) {
        case 2: // Weekly
          $weekdayCount = 0;
          $tempStartDate = $startDate->copy();

          $selectedDaysJson = $order->selected_days_for_weekly;
          $selectedDaysJson = preg_replace('/(\w+):/', '"$1":', $selectedDaysJson);
          $selectedDays = is_string($selectedDaysJson) ? json_decode($selectedDaysJson, true) : $selectedDaysJson;

          if (json_last_error() !== JSON_ERROR_NONE || !is_array($selectedDays)) {
            throw new Exception("Invalid JSON format in selected_days_for_weekly.");
          }

          $selectedDayCodes = array_map(function ($item) {
            return (string)$item['dayCode'];  // Convert dayCode to string for comparison
          }, $selectedDays);

          // Add 6 valid delivery days
          while ($weekdayCount < 6) {
            $tempStartDate->addDay();
            $dayCode = ($tempStartDate->dayOfWeekIso % 7);

            if (in_array($dayCode, $selectedDayCodes)) {
              $weekdayCount++;
            }
          }

          if ($pausedaysDifference > 0) {
            $additionalDaysAdded = 0;
            while ($additionalDaysAdded < $pausedaysDifference) {
              $tempStartDate->addDay();
              $dayCode = ($tempStartDate->dayOfWeekIso % 7);

              if (in_array($dayCode, $selectedDayCodes)) {
                $additionalDaysAdded++;
              }
            }
          }

          return $tempStartDate;

        case 3: // Monthly
          return $startDate->addDays(29 + $pausedaysDifference);

        case 4: // Alternate Days
          return $startDate->addDays(28 + ($pausedaysDifference * 2));

        default: // Default (One-Time or others)
          return $startDate;
      }
    } catch (
      Exception $e
    ) {
      Log::error("Error calculating end date: " . $e->getMessage());
      return $startDate; // Fallback to start date if error occurs
    }
  }

  function calculatePauseDays($pauseDates, $resumeDates, $startDate)
  {
    $totalPauseDays = 0;

    // Parse the start date for comparison
    $start = Carbon::parse($startDate);

    foreach ($pauseDates as $index => $pauseDate) {
      if (isset($resumeDates[$index])) {
        // Parse pause and resume dates
        $pauseStart = Carbon::parse($pauseDate);
        $pauseEnd = Carbon::parse($resumeDates[$index]);

        // Adjust the pause start to the start date if it overlaps
        if ($pauseStart < $start) {
          $pauseStart = $start;
        }

        // Calculate pause duration only if the adjusted pauseStart is before pauseEnd
        if ($pauseStart <= $pauseEnd) {
          $totalPauseDays += $pauseEnd->diffInDays($pauseStart) + 1; // Include the pauseEnd day
        }
      }
    }

    return $totalPauseDays;
  }


  // Function to calculate weekly subscription end date
  function calculateWeeklyEndDate($startDate, $selectedDays, $totalDeliveries)
  {
    // Check if selectedDays is valid
    if (empty($selectedDays)) {
      return date('Y-m-d', strtotime($startDate)); // Default to start date if invalid
    }

    $selectedDays = json_decode($selectedDays, true); // Decode selected_days_for_weekly JSON
    if (!is_array($selectedDays)) {
      return date('Y-m-d', strtotime($startDate)); // Default to start date if decoding fails
    }

    $dayCodes = array_column($selectedDays, 'dayCode'); // Extract day codes
    sort($dayCodes); // Ensure day codes are sorted

    $deliveriesCompleted = 0;
    $currentDate = strtotime($startDate);

    while ($deliveriesCompleted < $totalDeliveries) {
      $dayOfWeek = date('N', $currentDate); // Get day of the week (1 = Monday, 7 = Sunday)
      if (in_array($dayOfWeek, $dayCodes)) {
        $deliveriesCompleted++;
      }
      if ($deliveriesCompleted < $totalDeliveries) {
        $currentDate = strtotime('+1 day', $currentDate); // Move to the next day
      }
    }

    return date('Y-m-d', $currentDate);
  }

  // Function to calculate alternate day subscription end date
  function calculateAlternateEndDate($startDate, $totalDeliveries)
  {
    $deliveriesCompleted = 0;
    $currentDate = strtotime($startDate);

    while ($deliveriesCompleted < $totalDeliveries) {
      $deliveriesCompleted++;
      if ($deliveriesCompleted < $totalDeliveries) {
        $currentDate = strtotime('+2 days', $currentDate); // Move to every alternate day
      }
    }

    return date('Y-m-d', $currentDate);
  }


  // function getData()
  // {
  //   $selectFields = [
  //     'orders.*',
  //     'user_address.name',
  //     'user_address.s_phone',
  //     'user_address.flat_no',
  //     'user_address.apartment_name',
  //     'user_address.area',
  //     'user_address.city',
  //     'user_address.pincode',
  //     'users.wallet_amount'
  //   ];

  //   $subOrderData = DB::table("orders")
  //     ->select(array_merge($selectFields, ['product.title', 'product.qty_text', 'images.image as product_image']))
  //     ->whereNotNull('orders.subscription_type')
  //     ->leftJoin('images', function ($join) {
  //       $join->on('images.table_id', '=', 'orders.product_id')
  //         ->where('images.table_name', '=', "product")
  //         ->where('images.image_type', '=', 1);
  //     })
  //     ->Join('users', 'users.id', '=', 'orders.user_id')
  //     ->Join('product', 'orders.product_id', '=', 'product.id')
  //     ->Join('user_address', 'user_address.id', '=', 'orders.address_id')
  //     ->orderBy('orders.created_at', 'DESC')
  //     ->get();

  //   $cartOrderData = DB::table("orders")
  //     ->select($selectFields)
  //     ->whereNull('orders.subscription_type')
  //     ->join('users', 'users.id', '=', 'orders.user_id')
  //     ->join('user_address', 'user_address.id', '=', 'orders.address_id')
  //     ->orderBy('orders.created_at', 'DESC')
  //     ->get();
  //   $data = array_merge($subOrderData->toArray(), $cartOrderData->toArray());

  //   usort($data, function ($a, $b) {
  //     return $b->id <=> $a->id;
  //   });
  //   $data = collect($data);
  //   $response = [
  //     "response" => 200,
  //     'data' => $data,
  //   ];

  //   return response($response, 200);
  // }


  function getData()
  {
    try {
      $selectFields = [
        'orders.*',
        'user_address.name',
        'user_address.s_phone',
        'user_address.flat_no',
        'user_address.apartment_name',
        'user_address.area',
        'user_address.city',
        'user_address.pincode',
        'users.wallet_amount'
      ];

      // Fetch subscription orders
      $subOrderData = DB::table("orders")
        ->select(array_merge($selectFields, ['product.title', 'product.qty_text', 'images.image as product_image']))
        ->whereNotNull('orders.subscription_type')
        ->leftJoin('images', function ($join) {
          $join->on('images.table_id', '=', 'orders.product_id')
            ->where('images.table_name', '=', "product")
            ->where('images.image_type', '=', 1);
        })
        ->join('users', 'users.id', '=', 'orders.user_id')
        ->join('product', 'orders.product_id', '=', 'product.id')
        ->join('user_address', 'user_address.id', '=', 'orders.address_id')
        ->orderBy('orders.created_at', 'DESC')
        ->get();

      // Fetch cart orders
      $cartOrderData = DB::table("orders")
        ->select($selectFields)
        ->whereNull('orders.subscription_type')
        ->join('users', 'users.id', '=', 'orders.user_id')
        ->join('user_address', 'user_address.id', '=', 'orders.address_id')
        ->orderBy('orders.created_at', 'DESC')
        ->get();

      // Merge orders
      $mergedData = array_merge($subOrderData->toArray(), $cartOrderData->toArray());
      $data = collect($mergedData);

      // Attach coupon data if tables exist
      if (Schema::hasTable('coupon_usages') && Schema::hasTable('coupons')) {
        $data = $data->map(function ($order) {
          $coupon = DB::table('coupon_usages')
            ->join('coupons', 'coupon_usages.coupon_id', '=', 'coupons.id')
            ->where('coupon_usages.order_id', $order->id)
            ->select('coupons.id', 'coupons.code', 'coupons.type', 'coupons.value')
            ->first();

          $order->coupon = $coupon;
          return $order;
        });
      }

      // Final sorting
      $data = $data->sortByDesc('id')->values();

      return response([
        "response" => 200,
        "data" => $data
      ], 200);
    } catch (Exception $e) {
      return response([
        "response" => 500,
        "error" => "Something went wrong.",
        "message" => $e->getMessage()
      ], 500);
    }
  }


  function getStopOrderDataByUId($id)
  {
    $data = DB::table("orders")
      ->select(
        'orders.*',
        'product.title',
        'images.image as product_image',
        'product.qty_text',
        'user_address.name',
        'user_address.s_phone',
        'user_address.flat_no',
        'user_address.apartment_name',
        'user_address.area',
        'user_address.city',
        'user_address.pincode'

      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'orders.product_id')
          ->where('images.table_name', '=', "product")
          ->where('images.image_type', '=', 1);
      })
      ->Join('product', 'orders.product_id', '=', 'product.id')
      ->Join('user_address', 'user_address.id', '=', 'orders.address_id')
      ->where("orders.user_id", "=", $id)
      ->where('orders.order_status', '=', 1)
      ->orderBy('orders.created_at', 'DESC')
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }
  // function getDataByUId($id)
  // {
  //   $selectFields = [
  //     'orders.*',
  //     'user_address.name',
  //     'user_address.s_phone',
  //     'user_address.flat_no',
  //     'user_address.apartment_name',
  //     'user_address.area',
  //     'user_address.city',
  //     'user_address.pincode',
  //   ];

  //   $subOrderData = DB::table("orders")
  //     ->select(array_merge($selectFields, ['product.title', 'product.qty_text', 'images.image as product_image']))
  //     ->whereNotNull('orders.subscription_type')
  //     ->leftJoin('images', function ($join) {
  //       $join->on('images.table_id', '=', 'orders.product_id')
  //         ->where('images.table_name', '=', "product")
  //         ->where('images.image_type', '=', 1);
  //     })
  //     ->Join('product', 'orders.product_id', '=', 'product.id')
  //     ->Join('user_address', 'user_address.id', '=', 'orders.address_id')
  //     ->where("orders.user_id", "=", $id)
  //     ->where('orders.order_status', '=', 0)
  //     ->orderBy('orders.created_at', 'DESC')
  //     ->get()
  //     ->map(function ($order) {
  //       // Determine expected deliveries based on subscription type
  //       $expectedDeliveries = match ($order->subscription_type) {
  //         1 => 1,   // One time
  //         2 => 7,   // Weekly
  //         3 => 30,  // Monthly
  //         4 => 15,  // Alternative days
  //         default => 0
  //       };

  //       // Count completed deliveries from subscribed_order_delivery table
  //       $completedDeliveries = DB::table('subscribed_order_delivery')
  //         ->where('order_id', $order->id)
  //         ->count();

  //       // Determine delivery status (1 if all deliveries are completed, otherwise 0)
  //       $order->delivery_status = ($completedDeliveries === $expectedDeliveries) ? 1 : 0;
  //       return $order;
  //     });

  //   $cartOrderData = DB::table("orders")
  //     ->select($selectFields)
  //     ->whereNull('orders.subscription_type')
  //     ->Join('user_address', 'user_address.id', '=', 'orders.address_id')
  //     ->where("orders.user_id", "=", $id)
  //     ->where('orders.order_status', '=', 0)
  //     ->orderBy('orders.created_at', 'DESC')
  //     ->get();

  //   $data = array_merge($subOrderData->toArray(), $cartOrderData->toArray());

  //   usort($data, function ($a, $b) {
  //     return $b->id <=> $a->id;
  //   });
  //   $data = collect($data);

  //   $response = [
  //     "response" => 200,
  //     'data' => $data,
  //   ];

  //   return response($response, 200);
  // }

  // public function getDataByUId(Request $request, $id)
  // {
  //   $limit = $request->get('limit');
  //   $offset = $request->get('offset', 0);

  //   $selectFields = [
  //     'orders.*',
  //     'user_address.name',
  //     'user_address.s_phone',
  //     'user_address.flat_no',
  //     'user_address.apartment_name',
  //     'user_address.area',
  //     'user_address.city',
  //     'user_address.pincode',
  //   ];

  //   // Base query for subscription orders
  //   $subQuery = DB::table("orders")
  //     ->select(array_merge($selectFields, ['product.title', 'product.qty_text', 'images.image as product_image']))
  //     ->whereNotNull('orders.subscription_type')
  //     ->leftJoin('images', function ($join) {
  //       $join->on('images.table_id', '=', 'orders.product_id')
  //         ->where('images.table_name', '=', "product")
  //         ->where('images.image_type', '=', 1);
  //     })
  //     ->join('product', 'orders.product_id', '=', 'product.id')
  //     ->join('user_address', 'user_address.id', '=', 'orders.address_id')
  //     ->where("orders.user_id", "=", $id)
  //     ->where('orders.order_status', '=', 0)
  //     ->orderBy('orders.created_at', 'DESC');

  //   if (!is_null($limit)) {
  //     $subQuery->offset((int)$offset)->limit((int)$limit);
  //   }

  //   $subOrderData = $subQuery->get()->map(function ($order) {
  //     $expectedDeliveries = match ($order->subscription_type) {
  //       1 => 1,
  //       2 => 7,
  //       3 => 30,
  //       4 => 15,
  //       default => 0
  //     };

  //     $completedDeliveries = DB::table('subscribed_order_delivery')
  //       ->where('order_id', $order->id)
  //       ->count();

  //     $order->delivery_status = ($completedDeliveries === $expectedDeliveries) ? 1 : 0;
  //     return $order;
  //   });

  //   // Base query for non-subscription (cart) orders
  //   $cartQuery = DB::table("orders")
  //     ->select($selectFields)
  //     ->whereNull('orders.subscription_type')
  //     ->join('user_address', 'user_address.id', '=', 'orders.address_id')
  //     ->where("orders.user_id", "=", $id)
  //     ->where('orders.order_status', '=', 0)
  //     ->orderBy('orders.created_at', 'DESC');

  //   if (!is_null($limit)) {
  //     $cartQuery->offset((int)$offset)->limit((int)$limit);
  //   }

  //   $cartOrderData = $cartQuery->get();

  //   $data = array_merge($subOrderData->toArray(), $cartOrderData->toArray());

  //   usort($data, function ($a, $b) {
  //     return $b->id <=> $a->id;
  //   });

  //   $response = [
  //     "response" => 200,
  //     'data' => collect($data),
  //   ];

  //   return response($response, 200);
  // }

  public function getDataByUId(Request $request, $id)
  {
    try {
      $limit = $request->get('limit');
      $offset = $request->get('offset', 0);

      $selectFields = [
        'orders.*',
        'user_address.name',
        'user_address.s_phone',
        'user_address.flat_no',
        'user_address.apartment_name',
        'user_address.area',
        'user_address.city',
        'user_address.pincode',
      ];

      // Subscription Orders
      $subQuery = DB::table("orders")
        ->select(array_merge($selectFields, ['product.title', 'product.qty_text', 'images.image as product_image']))
        ->whereNotNull('orders.subscription_type')
        ->leftJoin('images', function ($join) {
          $join->on('images.table_id', '=', 'orders.product_id')
            ->where('images.table_name', '=', "product")
            ->where('images.image_type', '=', 1);
        })
        ->join('product', 'orders.product_id', '=', 'product.id')
        ->join('user_address', 'user_address.id', '=', 'orders.address_id')
        ->where("orders.user_id", "=", $id)
        ->where('orders.order_status', '=', 0)
        ->orderBy('orders.created_at', 'DESC');

      if (!is_null($limit)) {
        $subQuery->offset((int)$offset)->limit((int)$limit);
      }

      $subOrderData = $subQuery->get()->map(function ($order) use ($id) {
        $expectedDeliveries = match ($order->subscription_type) {
          1 => 1,
          2 => 7,
          3 => 30,
          4 => 15,
          default => 0
        };

        $completedDeliveries = DB::table('subscribed_order_delivery')
          ->where('order_id', $order->id)
          ->count();

        $order->delivery_status = ($completedDeliveries === $expectedDeliveries) ? 1 : 0;
        if (Schema::hasTable('coupon_usages') && Schema::hasTable('coupons')) {
          // Attach Coupon if used
          $coupon = DB::table('coupon_usages')
            ->join('coupons', 'coupon_usages.coupon_id', '=', 'coupons.id')
            ->where('coupon_usages.user_id', $id)
            ->where('coupon_usages.order_id', $order->id)
            ->select('coupons.id', 'coupons.code', 'coupons.type', 'coupons.value')
            ->first();

          $order->coupon = $coupon;
        }

        return $order;
      });

      // Cart Orders
      $cartQuery = DB::table("orders")
        ->select($selectFields)
        ->whereNull('orders.subscription_type')
        ->join('user_address', 'user_address.id', '=', 'orders.address_id')
        ->where("orders.user_id", "=", $id)
        ->where('orders.order_status', '=', 0)
        ->orderBy('orders.created_at', 'DESC');

      if (!is_null($limit)) {
        $cartQuery->offset((int)$offset)->limit((int)$limit);
      }

      if (Schema::hasTable('coupon_usages') && Schema::hasTable('coupons')) {
        $cartOrderData = $cartQuery->get()->map(function ($order) use ($id) {
          // Attach Coupon if used
          $coupon = DB::table('coupon_usages')
            ->join('coupons', 'coupon_usages.coupon_id', '=', 'coupons.id')
            ->where('coupon_usages.user_id', $id)
            ->where('coupon_usages.order_id', $order->id)
            ->select('coupons.id', 'coupons.code', 'coupons.type', 'coupons.value')
            ->first();

          $order->coupon = $coupon;

          return $order;
        });
      }

      // Merge and sort all orders by id desc
      $data = array_merge($subOrderData->toArray(), $cartOrderData->toArray());

      usort($data, function ($a, $b) {
        return $b->id <=> $a->id;
      });

      return response([
        "response" => 200,
        'data' => collect($data),
      ], 200);
    } catch (Exception $e) {
      Log::error("Get Orders By User ID Error: " . $e->getMessage());

      return response([
        "response" => 500,
        'status' => false,
        'message' => "Failed to retrieve orders",
      ], 500);
    }
  }

  function addOrderData(Request $request)
  {
    // Validate the incoming data
    $validator = Validator::make($request->all(), [
      'user_id' => 'required',
      'order_amount' => 'required',
      'status' => 'required',
      'qty' => 'required',
      'price' => 'required',
      'mrp' => 'required',
      'tax' => 'required'
    ]);

    if ($validator->fails()) {
      return response(["response" => 400], 400);
    }

    DB::beginTransaction(); // Start the database transaction

    try {
      // Setting up variables
      $webAppSettingsController = new WebAppSettingsController();
      $statusDataResponse = $webAppSettingsController->getDataDataByTitle("Auto Approve");
      $statusData = json_decode($statusDataResponse->getContent(), true);
      $timeStamp = date("Y-m-d H:i:s");
      $orderNumber = $this->generateOrderNumber();

      $result = ReferralController::completeReferral($request->user_id);
      if (!$result['success']) {
        Log::warning('Referral not applied: ' . $request->user_id . ' ' . $result['message']);
      }

      // Create new order model
      $dataModel = new OrderModel;
      $dataModel->user_id = $request->user_id;
      $dataModel->qty = $request->qty;
      $dataModel->order_amount = $request->order_amount;
      $dataModel->product_id = $request->product_id ?? null;
      $dataModel->address_id = $request->address_id;
      $dataModel->status = $request->status;
      $dataModel->price = $request->price;
      $dataModel->mrp = $request->mrp;
      $dataModel->tax = $request->tax;
      $dataModel->order_number = $orderNumber;
      $dataModel->product_detail = $request->product_detail ? json_encode($request->product_detail) : null;
      $dataModel->delivery_instruction = $request->delivery_instruction ?? "";
      $dataModel->razorpay_order_id = $request->razorpay_order_id ?? null;

      // Set optional fields
      if (isset($request->order_status)) {
        $dataModel->order_status = $request->order_status;
      } else {
        $dataModel->order_status = 0;
      }

      if ($statusData['response'] === 200 && isset($statusData['data']) && $statusData['data']['value'] === "true") {
        $dataModel->status = 1;
      }

      if (isset($request->start_date)) {
        $dataModel->start_date = $request->start_date;
      }

      if (isset($request->delivery_charge)) {
        $dataModel->delivery_charge = $request->delivery_charge;
      }

      if (isset($request->selected_days_for_weekly)) {
        $dataModel->selected_days_for_weekly = $request->selected_days_for_weekly;
      }

      if (isset($request->subscription_type)) {
        $dataModel->subscription_type = $request->subscription_type;
      }

      if (isset($request->trasation_id)) {
        $dataModel->trasation_id = $request->trasation_id;
      }

      if (isset($request->order_type)) {
        $dataModel->order_type = $request->order_type;
      }

      if (isset($request->coupon_id)) {
        $dataModel->coupon_discount_value = $request->coupon_discount_value ?? 0;
      }
      $dataModel->created_at = $timeStamp;
      $dataModel->updated_at = $timeStamp;
      // Save the order model to the database
      $qResponce = $dataModel->save();

      // Add transaction details before saving the order and performing wallet checks
      $dataTransactionModel = new TransactionsModel;
      $dataTransactionModel->user_id = $request->user_id;
      $dataTransactionModel->payment_id = $request->payment_id ?? $this->generatePaymentId();
      $dataTransactionModel->amount = $request->order_amount;
      if (isset($request->source_type)) {
        $dataTransactionModel->source_type = $request->source_type;
      } else {
        $dataTransactionModel->source_type = $request->wallet_added_amount > 0 ? 1 : 2;
      }
      $dataTransactionModel->type = $request->payment_type;
      $dataTransactionModel->description = $request->Payment_description;
      $dataTransactionModel->created_at = $timeStamp;
      $dataTransactionModel->updated_at = $timeStamp;

      // Save the transaction model
      $transactionSaved = $dataTransactionModel->save();
      if (isset($request->coupon_id)) {
        $this->recordCouponUsage($request->coupon_id, $request->user_id, $dataModel->id, $request->coupon_discount_value);
      }

      // Check if payment mode is 0 (wallet-based)
      // if ($request->payment_mode == 0) {
      //   $dataModelUser = User::where("id", $request->user_id)->first();
      //   if ($dataModelUser) {
      //     // Perform wallet operation based on payment type
      //     // if (in_array($request->payment_type, [1, 3])) {
      //     //   $dataModelUser->wallet_amount = ($dataModelUser->wallet_amount ?? 0) + $request->order_amount;
      //     // } else
      //     if ($request->payment_type == 2 && $dataModelUser->wallet_amount >= $request->order_amount) {
      //       $dataModelUser->wallet_amount -= $request->order_amount;
      //     }

      //     // Save wallet changes
      //     $walletSaved = $dataModelUser->save();
      //     if (!$walletSaved) {
      //       DB::rollBack(); // Rollback the transaction if wallet update fails
      //       return response([
      //         "response" => 201,
      //         'status' => false,
      //         'message' => "Wallet update failed",
      //       ], 200);
      //     }
      //   }
      // }
      if ($request->payment_mode == 0) {
        $dataModelUser = User::where("id", $request->user_id)->first();
        $dataTransactionModel->previous_balance = $dataModelUser->wallet_amount ?? 0;
        if ($dataModelUser && $request->payment_type == 2) {
          $walletAddedAmount = $request->wallet_added_amount ?? 0;
          $isFromRazorpay = $request->isFromrazorpay ?? false;
          $shouldUpdateWallet = false;

          if ($isFromRazorpay) {
            // Only allow if wallet_added_amount > 0
            if ($walletAddedAmount > 0 && $dataModelUser->wallet_amount >= $request->order_amount) {
              $dataModelUser->wallet_amount -= $request->order_amount;
              $shouldUpdateWallet = true;
            }
          } else {
            // Normal case
            if ($dataModelUser->wallet_amount >= $request->order_amount) {
              $dataModelUser->wallet_amount -= $request->order_amount;
              $shouldUpdateWallet = true;
            }
          }

          // Save wallet if required
          if ($shouldUpdateWallet) {
            $walletSaved = $dataModelUser->save();

            if (!$walletSaved) {
              DB::rollBack();
              return response([
                "response" => 201,
                'status' => false,
                'message' => "Wallet update failed",
              ], 200);
            }
          }
        }
      }
      // Proceed with stock update only if wallet deduction was successful
      if ($transactionSaved && $qResponce) {
        $dataModel->trasation_id = $dataTransactionModel->id;
        $dataModel->save();
        // Link transaction to the order
        $dataTransactionModel->order_id = $dataModel->id;
        $dataTransactionModel->save();

        // Update product stock based on order quantity
        if ($request->product_id) {
          $dataModelProduct = ProductModel::where("id", $request->product_id)->first();
          if ($dataModelProduct->stock_qty > 0) {
            $oldQty = $dataModelProduct->stock_qty;
            if ($oldQty >= $dataModel->qty) {
              $dataModelProduct->stock_qty = $oldQty - $dataModel->qty;
              $dataModelProduct->save();
            }
          }
        } else {
          for ($i = 0; $i < count($request->product_detail); $i++) {
            $dataModelProduct = ProductModel::where("id", $request->product_detail[$i]['product_id'])->first();
            if ($dataModelProduct->stock_qty > 0) {
              $oldQty = $dataModelProduct->stock_qty;
              if ($oldQty >= $dataModel->qty) {
                $dataModelProduct->stock_qty = $oldQty - $dataModel->qty;
                $dataModelProduct->save();
              }
            }
          }
        }

        // Commit the transaction if all operations are successful
        DB::commit();

        $response = [
          "response" => 200,
          'status' => true,
          'message' => "successfully"
        ];
      } else {
        DB::rollBack(); // Rollback the transaction if any of the previous steps failed
        $response = [
          "response" => 201,
          'status' => false,
          'message' => "error",
        ];
      }

      // Send appropriate notifications for subscription or one-time purchase
      if ($dataModel->subscription_type !== null) {
        try {
          $notificationResponse = notificationHelper::subscriptionConfirmNotify(
            $request->order_id,
            $dataModel->order_number,
            $dataModel->user_id
          );
        } catch (Exception $e) {
          Log::error("Subscription Notification Error: " . $e->getMessage());
        }
      } else {
        try {
          $notificationResponse = notificationHelper::buyOnceConfirmNotify(
            $request->order_id,
            $dataModel->order_number,
            $dataModel->user_id
          );
        } catch (Exception $e) {
          Log::error("One-time Purchase Notification Error: " . $e->getMessage());
        }
      }

      return response($response, 200);
    } catch (Exception $e) {
      DB::rollBack(); // Rollback transaction if there is any error
      $response = [
        "response" => 201,
        'status' => false,
        'message' => "error",
      ];
      return response($response, 200);
    }
  }


  function addData(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'user_id' => 'required',
      'order_amount' => 'required',
      'status' => 'required',
      'qty' => 'required',
      'price' => 'required',
      'mrp' => 'required',
      'tax' => 'required'

    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      try {
        $webAppSettingsController = new WebAppSettingsController();
        $statusDataResponse = $webAppSettingsController->getDataDataByTitle("Auto Approve");
        $statusData = json_decode($statusDataResponse->getContent(), true);
        $timeStamp = date("Y-m-d H:i:s");
        $orderNumber = $this->generateOrderNumber();
        $dataModel = new OrderModel;
        $dataModel->user_id  = $request->user_id;
        $dataModel->qty  = $request->qty;
        $dataModel->order_amount = $request->order_amount;
        $dataModel->product_id  = $request->product_id ?? null;
        $dataModel->address_id  = $request->address_id;
        $dataModel->status = $request->status;
        $dataModel->price  = $request->price;
        $dataModel->mrp = $request->mrp;
        $dataModel->tax = $request->tax;
        $dataModel->order_number = $orderNumber;
        $dataModel->product_detail = $request->product_detail ? json_encode($request->product_detail) : null;
        $dataModel->delivery_instruction =  $request->delivery_instruction;


        if (isset($request->order_status)) {
          $dataModel->order_status = $request->order_status;
        } else {
          $dataModel->order_status = 0;
        }
        if ($statusData['response'] === 200 && isset($statusData['data']) && $statusData['data']['value'] === "true") {
          $dataModel->status = 1;
        }
        if (isset($request->start_date)) {
          $dataModel->start_date = $request->start_date;
        }

        if (isset($request->delivery_charge)) {
          $dataModel->delivery_charge = $request->delivery_charge;
        }

        if (isset($request->selected_days_for_weekly)) {
          $dataModel->selected_days_for_weekly = $request->selected_days_for_weekly;
        }

        if (isset($request->subscription_type)) {
          $dataModel->subscription_type = $request->subscription_type;
        }
        if (isset($request->trasation_id)) {
          $dataModel->trasation_id = $request->trasation_id;
        }
        if (isset($request->order_type)) {
          $dataModel->order_type = $request->order_type;
        }

        if (isset($request->coupon_id)) {
          $dataModel->coupon_discount_value = $request->coupon_discount_value ?? 0;
        }
        $dataModel->created_at = $timeStamp;
        $dataModel->updated_at = $timeStamp;
        $qResponce = $dataModel->save();
        if ($qResponce && $request->isFromAdmin) {
          // Get user data to check wallet amount
          $transactionModel = TransactionsModel::where("id", $dataModel->trasation_id)->first();
          $dataModelUser = User::where("id", $dataModel->user_id)->first();
          $oldAmount = $dataModelUser->wallet_amount;

          // Check if wallet amount is sufficient
          if ($oldAmount !== null && $oldAmount > 0 && $oldAmount >= $dataModel->order_amount) {
            // Deduct order amount from wallet
            $newAmount = $oldAmount - $dataModel->order_amount;
            $dataModelUser->wallet_amount = $newAmount;

            if ($dataModelUser->save()) {
              // Update the transaction model to reflect wallet deduction
              if ($transactionModel) {
                $transactionModel->order_id = $dataModel->id;
                $transactionModel->save();
                if (isset($request->coupon_id)) {
                  $this->recordCouponUsage($request->coupon_id, $request->user_id, $dataModel->id, $request->coupon_discount_value);
                }
              }
            } else {
              // If wallet deduction fails, delete order and transaction entries
              $dataModel->delete();
              $transactionModel->delete();

              // Return failure response due to wallet update error
              return response([
                "response" => 201,
                'status' => false,
                'message' => "Wallet update failed, transaction canceled",
              ], 200);
            }
          } else {
            // Wallet has insufficient funds, respond with an error
            return response([
              "response" => 201,
              'status' => false,
              'message' => "Insufficient wallet balance",
            ], 200);
          }
        } else {
          $transactionModel = TransactionsModel::where("id", $dataModel->trasation_id)->first();
          if ($transactionModel) {
            $transactionModel->order_id = $dataModel->id;
            $transactionModel->save();
          }
        }
        if ($qResponce) {
          if ($request->product_id) {
            $dataModelProduct = ProductModel::where("id", $request->product_id)->first();
            if ($dataModelProduct->stock_qty > 0) {
              $oldQty = $dataModelProduct->stock_qty;
              if ($oldQty >= $dataModel->qty) {
                $dataModelProduct->stock_qty = $oldQty - $dataModel->qty;
                $dataModelProduct->save();
              }
            }
          } else {
            for ($i = 0; $i < count($request->product_detail); $i++) {
              $dataModelProduct = ProductModel::where("id", $request->product_detail[$i]['product_id'])->first();
              if ($dataModelProduct->stock_qty > 0) {
                $oldQty = $dataModelProduct->stock_qty;
                if ($oldQty >= $dataModel->qty) {
                  $dataModelProduct->stock_qty = $oldQty - $dataModel->qty;
                  $dataModelProduct->save();
                }
              }
            }
          }

          $response = [
            "response" => 200,
            'status' => true,
            'message' => "successfully"

          ];
        } else
          $response = [
            "response" => 201,
            'status' => false,
            'message' => "error",

          ];
        if ($dataModel->subscription_type !== null) {
          try {
            $notificationResponse = notificationHelper::subscriptionConfirmNotify(
              $request->order_id,
              $dataModel->order_number,
              $dataModel->user_id
            );
          } catch (Exception $e) {
            // Log notification error but don't affect order processing
            Log::error("Subscription Notification Error: " . $e->getMessage());
          }
        } else {
          try {
            $notificationResponse = notificationHelper::buyOnceConfirmNotify(
              $request->order_id,
              $dataModel->order_number,
              $dataModel->user_id
            );
          } catch (Exception $e) {
            // Log notification error but don't affect order processing
            Log::error("One-time Purchase Notification Error: " . $e->getMessage());
          }
        }

        return response($response, 200);
      } catch (Exception $e) {

        $response = [
          "response" => 201,
          'status' => false,
          'message' => "error",

        ];
        return response($response, 200);
      }
    }
  }

  function updateOrderTxnAndAddNewTxn(Request $request)
  {
    $initialCheck = false;
    $validator = Validator::make(request()->all(), [
      'order_id' => 'required',
      'payment_id' => 'required',


    ]);
    if ($validator->fails())
      $initialCheck = true;


    if ($initialCheck)
      return response(["response" => 400], 400);
    else {
      try {
        $dataModel = OrderModel::where("id", $request->order_id)->first();
        $timeStamp = date("Y-m-d H:i:s");

        $dataModelTxn = new TransactionsModel;
        $dataModelTxn->user_id  = $dataModel->user_id;
        $dataModelTxn->payment_id  = $request->payment_id;
        $dataModelTxn->amount = $dataModel->order_amount;
        $dataModelTxn->type  = 2;
        $productName = $dataModel->title;
        $dataModelTxn->description  = "Amount debited from account - $productName";

        $dataModelTxn->created_at = $timeStamp;
        $dataModelTxn->updated_at = $timeStamp;

        $qResponce = $dataModelTxn->save();
        if ($qResponce) {
          $dataModel->trasation_id = $dataModelTxn->id;
          $dataModel->updated_at = $timeStamp;
          $dataModel->save();

          $response = [
            "response" => 200,
            'status' => true,
            'message' => "successfully",

          ];
        } else
          $response = [
            "response" => 201,
            'status' => false,
            'message' => "error",

          ];
        return response($response, 200);
      } catch (Exception $e) {
        $response = [
          "response" => 201,
          'status' => false,
          'message' => "error",
        ];
        return response($response, 200);
      }
    }
  }
  function updateDetails(Request $request)
  {
    $initialCheck = false;
    $validator = Validator::make(request()->all(), [
      'id' => 'required'
    ]);
    if ($validator->fails())
      $initialCheck = true;


    if ($initialCheck)
      return response(["response" => 400], 400);
    else {
      try {
        $timeStamp = date("Y-m-d H:i:s");
        $dataModel = OrderModel::where("id", $request->id)->first();

        if (isset($request->qty))
          $dataModel->qty  = $request->qty;
        if (isset($request->order_amount))
          $dataModel->order_amount = $request->order_amount;
        if (isset($request->product_id))
          $dataModel->product_id  = $request->product_id;
        if (isset($request->address_id))
          $dataModel->address_id  = $request->address_id;
        if (isset($request->start_date))
          $dataModel->start_date = $request->start_date;
        if (isset($request->status))
          $dataModel->status = $request->status;
        if (isset($request->order_status))
          $dataModel->order_status = $request->order_status;


        if (isset($request->pause_date)) {
          $newPauseDates = array_map('trim', explode(',', $request->pause_date));
          try {
            $validDates = [];
            foreach ($newPauseDates as $newPauseDate) {
              if (!empty($newPauseDate)) {
                $carbonDate = Carbon::createFromFormat('Y-m-d', $newPauseDate);
                $validDates[] = $carbonDate->toDateString();
              }
            }

            if (empty($validDates)) {
              $dataModel->pause_dates = null;
            } else {
              $dataModel->pause_dates = '[' . implode(',', $validDates) . ']';
            }
          } catch (Exception $e) {
            return response()->json(['error' => 'Invalid date format in provided dates.'], 400);
          }
        } else {
          $dataModel->pause_dates = null;
        }

        if (isset($request->resume_date)) {
          // Retrieve existing pause_dates from the text column
          // Remove any extra brackets and split by commas to ensure it's in the correct format
          $existingResumeDates = $dataModel->resume_dates ? explode(',', trim($dataModel->resume_dates, '[]')) : [];

          // Trim any whitespace and check if the new date is not already in the array
          $newResumeDate = trim($request->resume_date);

          if (!in_array($newResumeDate, $existingResumeDates)) {
            // Add the new pause date to the array
            $existingResumeDates[] = $newResumeDate; // Append the new date
          }

          // Convert the updated array back to a string with square brackets around it
          $dataModel->resume_dates = '[' . implode(',', $existingResumeDates) . ']';
        }

        if (isset($request->selected_days_for_weekly)) {
          $dataModel->selected_days_for_weekly = $request->selected_days_for_weekly;
        }

        if (isset($request->subscription_type)) {
          $dataModel->subscription_type = $request->subscription_type;
        }
        if (isset($request->trasation_id)) {
          $dataModel->trasation_id = $request->trasation_id;
        }
        if (isset($request->order_type)) {
          $dataModel->order_type = $request->order_type;
        }


        $dataModel->updated_at = $timeStamp;


        $qResponce = $dataModel->save();

        if ($qResponce) {


          $response = [
            "response" => 200,
            'status' => true,
            'message' => "successfully",
            'data' => $dataModel

          ];
        } else
          $response = [
            "response" => 201,
            'status' => false,
            'message' => "error",

          ];
        // if (($request->pause_date && $dataModel->order_status == 0) || ($request->order_status === 1 && $dataModel->order_status === 0)) {
        //   // Subscription Paused Notification (Order Status changed from Active to Paused)
        //   $notificationResponse = notificationHelper::subscriptionPausedNotify($request->order_id, $dataModel->order_number, $dataModel->user_id);
        // } else if (($request->resume_date && $dataModel->order_status === 1) || ($request->order_status === 0 && $dataModel->order_status === 1)) {
        //   // Subscription Resumed Notification (Order Status changed from Paused to Active)
        //   $notificationResponse = notificationHelper::subscriptionResumeNotify($request->order_id, $dataModel->order_number, $dataModel->user_id);
        // }
        return response($response, 200);
      } catch (Exception $e) {
        $response = [
          "response" => 201,
          'status' => false,
          'message' => "error ",
        ];
        return response($response, 200);
      }
    }
  }
}
