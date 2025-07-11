<?php

namespace App\Http\Controllers;

use App\Models\AllowPincodeModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{


  function getDataAllDeliveryReport()
  {
    // Calculate the last 7 days date range
    $toDate = Carbon::now()->endOfDay(); // Current date and time
    $fromDate = Carbon::now()->subDays(7)->startOfDay(); // 7 days before

    $data = DB::table("subscribed_order_delivery")
      ->select(
        'subscribed_order_delivery.*',
        'delivery_executive.name as executive_name',
        'users.name',
        'users.id as entry_user_id',
        'user_address.*',
        'product.qty_text',
        'product.title',
        'orders.subscription_type',
        'orders.product_detail',
        'orders.order_type',
        'orders.order_amount',
        'orders.order_number',
        'orders.qty',
        'orders.selected_days_for_weekly',
        'orders.subscription_type',
        'orders.created_at as order_created_date'
      )
      ->Join('orders', 'orders.id', '=', 'subscribed_order_delivery.order_id')
      ->Join('delivery_executive', 'delivery_executive.id', '=', 'subscribed_order_delivery.executive_id')
      ->Join('users', 'users.id', '=', 'orders.user_id')
      ->leftJoin('product', 'product.id', '=', 'orders.product_id')
      ->Join('user_address', 'user_address.id', '=', 'orders.address_id')
      ->whereBetween('subscribed_order_delivery.date', [$fromDate, $toDate]) // Use Carbon instances
      ->orderBy('subscribed_order_delivery.date', 'DESC')
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }


  function getDataAllDeliveryByUser($userId)
  {

    $data = DB::table("subscribed_order_delivery")
      ->select(
        'subscribed_order_delivery.*',
        'users.name',
        'user_address.pincode',
        'user_address.s_phone',
        'product.qty_text',
        'product.title',
        'orders.subscription_type',
        'orders.order_type',
        'orders.order_amount',
        'order.order_number',
        'orders.qty',
        'orders.selected_days_for_weekly',
        'orders.subscription_type'
      )
      ->Join('users', 'users.id', '=', 'subscribed_order_delivery.entry_user_id')
      ->Join('orders', 'orders.id', '=', 'subscribed_order_delivery.order_id')
      ->Join('product', 'product.id', '=', 'orders.product_id')
      ->Join('user_address', 'user_address.id', '=', 'orders.address_id')
      ->where('subscribed_order_delivery.entry_user_id', '=', $userId)
      ->orderBy('subscribed_order_delivery.date', 'DESC')
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  function getDataAllDeliveryReportByDate($firstDate, $lastDate)
  {

    // Ensure correct date format
    $firstDate = Carbon::createFromFormat('d-m-Y', $firstDate)->format('Y-m-d');
    $lastDate = Carbon::createFromFormat('d-m-Y', $lastDate)->format('Y-m-d');

    $data = DB::table("subscribed_order_delivery")
      ->select(
        'subscribed_order_delivery.*',
        'delivery_executive.name as executive_name',
        'users.name',
        'users.id as entry_user_id',
        'user_address.*',
        'product.qty_text',
        'product.title',
        'orders.subscription_type',
        'orders.order_type',
        'orders.order_amount',
        'orders.order_number',
        'orders.product_detail',
        'orders.qty',
        'orders.selected_days_for_weekly',
        'orders.subscription_type',
        'orders.created_at as order_created_date'
      )
      ->Join('orders', 'orders.id', '=', 'subscribed_order_delivery.order_id')
      ->Join('delivery_executive', 'delivery_executive.id', '=', 'subscribed_order_delivery.executive_id')
      ->Join('users', 'users.id', '=', 'orders.user_id')
      ->leftJoin('product', 'product.id', '=', 'orders.product_id')
      ->Join('user_address', 'user_address.id', '=', 'orders.address_id')
      ->whereBetween(DB::raw("DATE(subscribed_order_delivery.date)"), [$firstDate, $lastDate])
      ->orderBy('subscribed_order_delivery.date', 'DESC')
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }
  function getDataAllDeliveryReportByDateAndUser($userId, $firstDate, $lastDate)
  {

    $data = DB::table("subscribed_order_delivery")
      ->select(
        'subscribed_order_delivery.*',
        'users.name',
        'user_address.pincode',
        'user_address.s_phone',
        'product.qty_text',
        'product.title',
        'orders.subscription_type',
        'orders.order_type',
        'orders.order_amount',
        'order.order_number',
        'orders.qty',
        'orders.selected_days_for_weekly',
        'orders.subscription_type'
      )
      ->Join('users', 'users.id', '=', 'subscribed_order_delivery.entry_user_id')
      ->Join('orders', 'orders.id', '=', 'subscribed_order_delivery.order_id')
      ->Join('product', 'product.id', '=', 'orders.product_id')
      ->Join('user_address', 'user_address.id', '=', 'orders.address_id')
      ->where('subscribed_order_delivery.entry_user_id', '=', $userId)
      ->where('subscribed_order_delivery.date', '>=', $firstDate)
      ->where('subscribed_order_delivery.date', '<=', $lastDate)
      ->orderBy('subscribed_order_delivery.date', 'DESC')
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  function getCustomerReportData()
  {
    // Calculate the last 7 days date range
    $toDate = now(); // Current date and time
    $fromDate = now()->subDays(7); // 7 days before the current date

    $data = DB::table("users")
      ->select(
        'users.id',
        'users.email',
        'users.phone',
        'users.name',
        'users.wallet_amount',
        'users.created_at',
        'users.updated_at',
        'images.id as image_id',
        'images.image'
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'users.id')
          ->where('images.table_name', '=', 'users')
          ->where('images.image_type', '=', 1);
      })
      // ->leftJoin('assign_role', 'assign_role.user_id', '=', 'users.id') // Join to check role assignment
      // ->whereNull('assign_role.user_id') // Only include users without a role assignment (customers)
      ->whereNotNull('users.name')  // Ensure name is not null
      ->whereNotNull('users.phone') // Ensure phone is not null
      ->whereBetween(DB::raw("DATE(users.created_at)"), [$fromDate, $toDate])
      ->orderBy('users.created_at', 'DESC')
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  function getCustomerReportDataByDate($startDate = null, $endDate = null)
  {
    $firstDate = Carbon::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
    $lastDate = Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');

    $data = DB::table("users")
      ->select(
        'users.id',
        'users.email',
        'users.phone',
        'users.name',
        'users.wallet_amount',
        'users.created_at',
        'users.updated_at',
        'images.id as image_id',
        'images.image'
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'users.id')
          ->where('images.table_name', '=', 'users')
          ->where('images.image_type', '=', 1);
      })
      // ->leftJoin('assign_role', 'assign_role.user_id', '=', 'users.id')
      // ->whereNull('assign_role.user_id')
      ->whereNotNull('users.name')
      ->whereNotNull('users.phone')
      ->whereBetween(DB::raw("DATE(users.created_at)"), [$firstDate, $lastDate])
      ->orderBy('users.created_at', 'DESC')
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  function getSubscriptionReportData()
  {
    // Calculate the last 7 days date range
    $toDate = now()->endOfDay(); // Current date and time
    $fromDate = now()->subDays(7)->startOfDay(); // 7 days before the current date

    // Fetch data from the database
    $data = DB::table("orders")
      ->select(
        "orders.id",
        'orders.order_type',
        'orders.order_amount',
        'orders.qty',
        'orders.product_detail',
        'orders.selected_days_for_weekly',
        'orders.subscription_type',
        'orders.start_date',
        'orders.created_at',
        'orders.updated_at',
        "orders.user_id",
        'orders.order_status',
        'orders.order_number',
        'product.title',
        DB::raw('MIN(images.image) as product_image'), // Select only one image
        'product.qty_text',
        'user_address.name',
        'user_address.s_phone',
        'user_address.flat_no',
        'user_address.apartment_name',
        'user_address.area',
        'user_address.city',
        'user_address.pincode',
        'users.wallet_amount',
        DB::raw('MAX(subscribed_order_delivery.date) as delivered_date'), // Latest delivery date
        'transactions.payment_id',
        DB::raw('IFNULL(delivery_executive.name, "") as executive_name')
        // 'delivery_executive.name as executive_name'
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'orders.product_id')
          ->where('images.table_name', '=', "product")
          ->where('images.image_type', '=', 1);
      })
      ->join('product', 'orders.product_id', '=', 'product.id')
      ->join('users', 'users.id', '=', 'orders.user_id')
      ->leftJoin('subscribed_order_delivery', 'subscribed_order_delivery.order_id', '=', 'orders.id')
      ->leftJoin('delivery_executive_orders', 'delivery_executive_orders.order_id', '=', 'orders.id')
      ->leftJoin('delivery_executive', 'delivery_executive.id', '=', 'delivery_executive_orders.delivery_executive_id')
      ->leftJoin('transactions', 'transactions.id', '=', 'orders.trasation_id')
      ->join('user_address', 'user_address.id', '=', 'orders.address_id')
      ->where("orders.subscription_type", "!=", null)
      // ->whereBetween(DB::raw("DATE(orders.created_at)"), [$fromDate, $toDate])
      ->whereBetween('orders.created_at', [$fromDate, $toDate])
      ->groupBy(
        "orders.id",
        'orders.order_type',
        'orders.order_amount',
        'orders.qty',
        'orders.product_detail',
        'orders.selected_days_for_weekly',
        'orders.subscription_type',
        'orders.start_date',
        'orders.created_at',
        'orders.updated_at',
        "orders.user_id",
        'orders.order_status',
        'orders.order_number',
        'product.title',
        'product.qty_text',
        'user_address.name',
        'user_address.s_phone',
        'user_address.flat_no',
        'user_address.apartment_name',
        'user_address.area',
        'user_address.city',
        'user_address.pincode',
        'users.wallet_amount',
        'transactions.payment_id',
        'delivery_executive.name'
      )
      ->orderBy('orders.created_at', 'DESC')
      ->get();

    // Map and process the data
    $data = $data->map(function ($order) {
      // Determine the total deliveries and end date based on subscription type
      switch ($order->subscription_type) {
        case '1': // One Time
          $totalDeliveries = 1;
          $endDate = $order->start_date;
          break;
        case '2': // Weekly
          $totalDeliveries = 7;
          $endDate = null; // Weekly end date can be calculated if required
          break;
        case '3': // Monthly
          $totalDeliveries = 30;
          $endDate = date('Y-m-d', strtotime($order->start_date . ' + 29 days'));
          break;
        case '4': // Alternate Days
          $totalDeliveries = 15;
          $endDate = $this->calculateAlternateEndDate($order->start_date, $totalDeliveries);
          break;
        default:
          $totalDeliveries = 0;
          $endDate = null;
      }

      // Count delivered orders for the current order
      $deliveredCount = DB::table('subscribed_order_delivery')
        ->where('order_id', $order->id)
        ->count();

      // Calculate deliveries left
      $deliveriesLeft = max(0, $totalDeliveries - $deliveredCount);

      // Add additional details to the order object
      $order->total_deliveries = $totalDeliveries;
      $order->delivered = $deliveredCount;
      $order->deliveries_left = $deliveriesLeft;
      $order->end_date = $endDate;

      return $order;
    });

    // Structure the response
    $response = [
      "response" => 200,
      'data' => $data
    ];

    return response($response, 200);
  }

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
  function getSubscriptionReportDataByDate($startDate = null, $endDate = null)
  {
    // Convert dates to the correct format
    $firstDate = Carbon::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
    $lastDate = Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');

    // Query the database
    $data = DB::table('orders')
      ->select(
        'orders.id',
        'orders.order_type',
        'orders.order_amount',
        'orders.qty',
        'orders.selected_days_for_weekly',
        'orders.subscription_type',
        'orders.product_detail',
        'orders.start_date',
        'orders.created_at',
        'orders.updated_at',
        'orders.user_id',
        'orders.order_status',
        'orders.order_number',
        'product.title',
        DB::raw('MIN(images.image) as product_image'),
        'product.qty_text',
        'user_address.name',
        'user_address.s_phone',
        'user_address.flat_no',
        'user_address.apartment_name',
        'user_address.area',
        'user_address.city',
        'user_address.pincode',
        'users.wallet_amount',
        DB::raw('MAX(subscribed_order_delivery.date) as delivered_date'), // Use MAX to get the latest delivery date
        'transactions.payment_id',
        DB::raw('IFNULL(delivery_executive.name, "") as executive_name')
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'orders.product_id')
          ->where('images.table_name', '=', 'product')
          ->where('images.image_type', '=', 1);
      })
      ->join('product', 'orders.product_id', '=', 'product.id')
      ->join('users', 'users.id', '=', 'orders.user_id')
      ->leftJoin('subscribed_order_delivery', 'subscribed_order_delivery.order_id', '=', 'orders.id')
      ->leftJoin('delivery_executive_orders', 'delivery_executive_orders.order_id', '=', 'orders.id')
      ->leftJoin('delivery_executive', 'delivery_executive.id', '=', 'delivery_executive_orders.delivery_executive_id')
      ->leftJoin('transactions', 'transactions.id', '=', 'orders.trasation_id')
      ->join('user_address', 'user_address.id', '=', 'orders.address_id')
      ->whereNotNull('orders.subscription_type')
      ->whereBetween(DB::raw('DATE(orders.created_at)'), [$firstDate, $lastDate])
      ->groupBy( // Group by the unique fields of the orders table to avoid duplicates
        'orders.id',
        'orders.order_type',
        'orders.order_amount',
        'orders.qty',
        'orders.selected_days_for_weekly',
        'orders.subscription_type',
        'orders.product_detail',
        'orders.start_date',
        'orders.created_at',
        'orders.updated_at',
        'orders.user_id',
        'orders.order_status',
        'orders.order_number',
        'product.title',
        //'images.image',
        'product.qty_text',
        'user_address.name',
        'user_address.s_phone',
        'user_address.flat_no',
        'user_address.apartment_name',
        'user_address.area',
        'user_address.city',
        'user_address.pincode',
        'users.wallet_amount',
        'transactions.payment_id',
        'delivery_executive.name'
      )
      ->orderBy('orders.created_at', 'DESC')
      ->get();

    // Process the data to calculate additional fields
    $data = $data->map(function ($order) {
      // Calculate total deliveries and end date based on subscription type
      switch ($order->subscription_type) {
        case '1': // One Time
          $totalDeliveries = 1;
          $endDate = $order->start_date;
          break;
        case '2': // Weekly
          $totalDeliveries = 7;
          $endDate = null;
          break;
        case '3': // Monthly
          $totalDeliveries = 30;
          $endDate = date('Y-m-d', strtotime($order->start_date . ' + 29 days'));
          break;
        case '4': // Alternate Days
          $totalDeliveries = 15;
          $endDate = $this->calculateAlternateEndDate($order->start_date, $totalDeliveries);
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

      // Add calculated fields to the order
      $order->total_deliveries = $totalDeliveries;
      $order->delivered = $deliveredCount;
      $order->deliveries_left = $deliveriesLeft;
      $order->end_date = $endDate;

      return $order;
    });

    // Return the response
    return response([
      'response' => 200,
      'data' => $data,
    ], 200);
  }

  function getSubscriberReportData()
  {
    $toDate = now(); // Current date and time
    $fromDate = now()->subDays(7); // 7 days before the current date

    $data = DB::table("orders")
      ->select(
        "users.id as user_id",
        'users.name',
        'users.email',
        'users.phone',
        'users.wallet_amount',
        DB::raw("SUM(orders.order_amount) as total_order_amount") // Total order amount for each user
      )
      ->join('users', 'users.id', '=', 'orders.user_id')
      ->whereNotNull("orders.subscription_type")
      ->whereBetween(DB::raw("DATE(orders.created_at)"), [$fromDate, $toDate])
      ->groupBy('users.id', 'users.name', 'users.email', 'users.phone', 'users.wallet_amount') // Group by user details
      ->orderBy('total_order_amount', 'DESC') // Order by total amount if needed
      ->get();

    $response = [
      "response" => 200,
      'data' => $data
    ];

    return response($response, 200);
  }

  function getSubscriberReportDataByDate($startDate = null, $endDate = null)
  {
    $firstDate = Carbon::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
    $lastDate = Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');

    $data = DB::table("orders")
      ->select(
        "users.id as user_id",
        'users.name',
        'users.email',
        'users.phone',
        'users.wallet_amount',
        DB::raw("SUM(orders.order_amount) as total_order_amount")
      )
      ->join('users', 'users.id', '=', 'orders.user_id')
      ->whereNotNull("orders.subscription_type")
      ->whereBetween(DB::raw("DATE(orders.created_at)"), [$firstDate, $lastDate])
      ->groupBy('users.id', 'users.name', 'users.email', 'users.phone', 'users.wallet_amount')
      ->orderBy('total_order_amount', 'DESC')
      ->get();

    $response = [
      "response" => 200,
      'data' => $data
    ];

    return response($response, 200);
  }

  function getSalesReport()
  {
    $toDate = now()->endOfDay(); // Current date and time
    $fromDate = now()->subDays(7)->startOfDay(); // 7 days before the current date

    $mainQuery = DB::table("orders")
      ->select(
        DB::raw("UUID() as unique_id"), // Generate unique key for each record
        "orders.id",
        'orders.order_type',
        'orders.order_amount',
        'orders.qty',
        'orders.selected_days_for_weekly',
        'orders.subscription_type',
        'orders.product_detail',
        'orders.start_date',
        'orders.created_at',
        'orders.updated_at',
        "orders.user_id",
        'orders.order_number',
        'orders.delivery_status',
        'product.title',
        'product.qty_text',
        'product.tax',
        'user_address.name',
        'user_address.s_phone',
        'user_address.flat_no',
        'user_address.apartment_name',
        'user_address.area',
        'user_address.city',
        'user_address.pincode',
        'users.wallet_amount',
        'users.email',
        'users.phone',
        'subscribed_order_delivery.date as delivered_date',
        DB::raw('NULL as refund_amount')  // No refund amount for this record
      )
      ->leftJoin('product', 'orders.product_id', '=', 'product.id')
      ->join('users', 'users.id', '=', 'orders.user_id')
      ->leftJoin('subscribed_order_delivery', 'subscribed_order_delivery.order_id', '=', 'orders.id')
      ->join('user_address', 'user_address.id', '=', 'orders.address_id')
      // ->whereBetween(DB::raw("DATE(orders.created_at)"), [$fromDate, $toDate]);
      ->whereBetween('orders.created_at', [$fromDate, $toDate]);

    $refundQuery = DB::table("orders")
      ->select(
        DB::raw("UUID() as unique_id"), // Generate unique key for each record
        "orders.id",
        'orders.order_type',
        'orders.order_amount',
        'orders.qty',
        'orders.selected_days_for_weekly',
        'orders.subscription_type',
        'orders.product_detail',
        'orders.start_date',
        'orders.created_at',
        'orders.updated_at',
        "orders.user_id",
        'orders.order_number',
        'orders.delivery_status',
        'product.title',
        'product.qty_text',
        'product.tax',
        'user_address.name',
        'user_address.s_phone',
        'user_address.flat_no',
        'user_address.apartment_name',
        'user_address.area',
        'user_address.city',
        'user_address.pincode',
        'users.wallet_amount',
        'users.email',
        'users.phone',
        'subscribed_order_delivery.date as delivered_date',
        'transactions.amount as refund_amount'  // Include refund amount from transactions table
      )
      ->leftJoin('product', 'orders.product_id', '=', 'product.id')
      ->join('users', 'users.id', '=', 'orders.user_id')
      ->leftJoin('subscribed_order_delivery', 'subscribed_order_delivery.order_id', '=', 'orders.id')
      ->join('user_address', 'user_address.id', '=', 'orders.address_id')
      ->leftJoin('transactions', function ($join) {
        $join->on('orders.id', '=', 'transactions.order_id')
          ->where('transactions.type', '=', 3);  // 3 Refer Refund
      })
      ->whereNotNull('transactions.amount')
      // ->whereBetween(DB::raw("DATE(orders.created_at)"), [$fromDate, $toDate]); // Ensure there is a refund amount
      ->whereBetween('orders.created_at', [$fromDate, $toDate]);

    // Combine the two queries with UNION ALL
    $data = $mainQuery->unionAll($refundQuery)
      ->orderBy('created_at', 'DESC')
      ->get();

    // Response
    $response = [
      "response" => 200,
      'data' => $data
    ];

    return response($response, 200);
  }

  function getSalesReportByDate($startDate, $endDate)
  {
    $firstDate = Carbon::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
    $lastDate = Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');

    $mainQuery = DB::table("orders")
      ->select(
        DB::raw("UUID() as unique_id"), // Generate unique key for each record
        "orders.id",
        'orders.order_type',
        'orders.order_amount',
        'orders.qty',
        'orders.selected_days_for_weekly',
        'orders.subscription_type',
        'orders.product_detail',
        'orders.start_date',
        'orders.created_at',
        'orders.updated_at',
        "orders.user_id",
        'orders.order_number',
        'orders.delivery_status',
        'product.title',
        'product.qty_text',
        'product.tax',
        'user_address.name',
        'user_address.s_phone',
        'user_address.flat_no',
        'user_address.apartment_name',
        'user_address.area',
        'user_address.city',
        'user_address.pincode',
        'users.wallet_amount',
        'users.email',
        'users.phone',
        'subscribed_order_delivery.date as delivered_date',
        DB::raw('NULL as refund_amount')  // No refund amount for this record
      )
      ->leftJoin('product', 'orders.product_id', '=', 'product.id')
      ->join('users', 'users.id', '=', 'orders.user_id')
      ->leftJoin('subscribed_order_delivery', 'subscribed_order_delivery.order_id', '=', 'orders.id')
      ->join('user_address', 'user_address.id', '=', 'orders.address_id')
      ->whereBetween(DB::raw("DATE(orders.created_at)"), [$firstDate, $lastDate]);

    $refundQuery = DB::table("orders")
      ->select(
        DB::raw("UUID() as unique_id"), // Generate unique key for each record
        "orders.id",
        'orders.order_type',
        'orders.order_amount',
        'orders.qty',
        'orders.selected_days_for_weekly',
        'orders.subscription_type',
        'orders.product_detail',
        'orders.start_date',
        'orders.created_at',
        'orders.updated_at',
        "orders.user_id",
        'orders.order_number',
        'orders.delivery_status',
        'product.title',
        'product.qty_text',
        'product.tax',
        'user_address.name',
        'user_address.s_phone',
        'user_address.flat_no',
        'user_address.apartment_name',
        'user_address.area',
        'user_address.city',
        'user_address.pincode',
        'users.wallet_amount',
        'users.email',
        'users.phone',
        'subscribed_order_delivery.date as delivered_date',
        'transactions.amount as refund_amount'  // Include refund amount from transactions table
      )
      ->leftJoin('product', 'orders.product_id', '=', 'product.id')
      ->join('users', 'users.id', '=', 'orders.user_id')
      ->leftJoin('subscribed_order_delivery', 'subscribed_order_delivery.order_id', '=', 'orders.id')
      ->join('user_address', 'user_address.id', '=', 'orders.address_id')
      ->leftJoin('transactions', function ($join) {
        $join->on('orders.id', '=', 'transactions.order_id')
          ->where('transactions.type', '=', 3);  // 3 Refer Refund
      })
      ->whereNotNull('transactions.amount') // Ensure there is a refund amount
      ->whereBetween(DB::raw("DATE(orders.created_at)"), [$firstDate, $lastDate]);

    // Combine the two queries with UNION ALL
    $data = $mainQuery->unionAll($refundQuery)
      ->orderBy('created_at', 'DESC')
      ->get();

    // Response
    $response = [
      "response" => 200,
      'data' => $data
    ];

    return response($response, 200);
  }

  function getSalesReportByDaily($selectedDate)
  {
    $selectedDate = Carbon::parse($selectedDate)->startOfDay();
    $fromDate = $selectedDate;
    $toDate = $selectedDate->copy()->endOfDay();

    $orders = DB::table("orders")
      ->select(
        DB::raw("UUID() as unique_id"),
        "orders.id",
        'orders.order_type',
        'orders.order_amount',
        'orders.qty',
        'orders.selected_days_for_weekly',
        'orders.subscription_type',
        'orders.product_detail',
        'orders.start_date',
        'orders.created_at',
        'orders.pause_dates',
        'orders.updated_at',
        "orders.user_id",
        'orders.order_number',
        'orders.delivery_status',
        'orders.price',
        'orders.tax',
        'product.title',
        'product.qty_text',
        'user_address.name',
        'user_address.s_phone',
        'user_address.flat_no',
        'user_address.apartment_name',
        'user_address.area',
        'user_address.city',
        'user_address.pincode',
        'users.wallet_amount',
        'users.email',
        'users.phone',
        // 'subscribed_order_delivery.date as delivered_date',
        DB::raw('NULL as refund_amount')
      )
      ->leftJoin('product', 'orders.product_id', '=', 'product.id')
      ->join('users', 'users.id', '=', 'orders.user_id')
      // ->leftJoin('subscribed_order_delivery', 'subscribed_order_delivery.order_id', '=', 'orders.id')
      ->join('user_address', 'user_address.id', '=', 'orders.address_id')
      //->where('orders.status', 1)
      ->get();

    $filtered = $orders->filter(function ($order) use ($selectedDate) {
      $startDate = $order->start_date ? Carbon::parse($order->start_date) : null;
      $createdAt = Carbon::parse($order->created_at);
      $pauseDates = $order->pause_dates
        ? array_map('trim', explode(',', trim($order->pause_dates, '[]')))
        : [];

      $isPaused = function ($date) use ($pauseDates) {
        foreach ($pauseDates as $pauseDate) {
          if (Carbon::parse($date)->isSameDay(Carbon::parse($pauseDate))) {
            return true;
          }
        }
        return false;
      };

      if ($isPaused($selectedDate)) {
        return false;
      }

      if (is_null($order->subscription_type)) {
        return is_null($startDate)
          ? $createdAt->copy()->addDay()->isSameDay($selectedDate)
          : $startDate->isSameDay($selectedDate);
      }

      $newEndDate = $this->calculateEndDate($startDate, $order, $pauseDates);

      if ($selectedDate->lt($startDate) || $selectedDate->gt($newEndDate)) {
        return false;
      }

      switch ($order->subscription_type) {
        case '2': // Weekly
          return $this->isDeliveryDayForWeekly($selectedDate, $order->selected_days_for_weekly);
        case '4': // Alternate Days
          return $startDate->diffInDays($selectedDate) % 2 === 0;
        case '1': // One-Time
        case '3': // Monthly
          return true;
        default:
          return false;
      }
    });

    $refundQuery = DB::table("orders")
      ->select(
        DB::raw("UUID() as unique_id"),
        "orders.id",
        'orders.order_type',
        'orders.order_amount',
        'orders.qty',
        'orders.selected_days_for_weekly',
        'orders.subscription_type',
        'orders.product_detail',
        'orders.start_date',
        'orders.created_at',
        'orders.pause_dates',
        'orders.updated_at',
        "orders.user_id",
        'orders.order_number',
        'orders.delivery_status',
        'orders.price',
        'orders.tax',
        'product.title',
        'product.qty_text',
        'user_address.name',
        'user_address.s_phone',
        'user_address.flat_no',
        'user_address.apartment_name',
        'user_address.area',
        'user_address.city',
        'user_address.pincode',
        'users.wallet_amount',
        'users.email',
        'users.phone',
        // 'subscribed_order_delivery.date as delivered_date',
        'transactions.amount as refund_amount'
      )
      ->leftJoin('product', 'orders.product_id', '=', 'product.id')
      ->join('users', 'users.id', '=', 'orders.user_id')
      // ->leftJoin('subscribed_order_delivery', 'subscribed_order_delivery.order_id', '=', 'orders.id')
      ->join('user_address', 'user_address.id', '=', 'orders.address_id')
      ->leftJoin('transactions', function ($join) {
        $join->on('orders.id', '=', 'transactions.order_id')
          ->where('transactions.type', '=', 3);
      })
      ->whereNotNull('transactions.amount')
      //->where('orders.status', 1)
      ->whereBetween('transactions.created_at', [$fromDate, $toDate])
      ->get();

    $finalData = $filtered->merge($refundQuery)->sortByDesc('created_at')->values();

    return response([
      "response" => 200,
      "data" => $finalData,
    ], 200);
  }


  private static function calculateEndDate($startDate, $order, $pauseDates = [])
  {
    $startDate = Carbon::parse($startDate);
    $pausedaysDifference = count($pauseDates);

    switch ($order->subscription_type) {
      case 2: // Weekly
        $weekdayCount = 0;
        $tempStartDate = $startDate->copy();

        $selectedDaysJson = $order->selected_days_for_weekly;
        $selectedDaysJson = preg_replace('/(\w+):/', '"$1":', $selectedDaysJson);
        $selectedDays = is_string($selectedDaysJson) ? json_decode($selectedDaysJson, true) : $selectedDaysJson;

        $selectedDayCodes = array_map(function ($item) {
          return (string)($item['dayCode'] === 0 ? 7 : $item['dayCode']);
        }, $selectedDays);

        // Add 6 valid delivery days
        while ($weekdayCount < 6) {
          $tempStartDate->addDay();
          $dayCode = $tempStartDate->dayOfWeekIso;
          if (in_array($dayCode, $selectedDayCodes)) {
            $weekdayCount++;
          }
        }

        // Add paused days to the end date
        if ($pausedaysDifference > 0) {
          $additionalDaysAdded = 0;
          while ($additionalDaysAdded < $pausedaysDifference) {
            $tempStartDate->addDay();
            $dayCode = $tempStartDate->dayOfWeekIso;
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

      default: // One-Time or others
        return $startDate;
    }
  }

  private function noOfDeliveryCompleted($orderId)
  {
    $deliveredCount = DB::table('subscribed_order_delivery')
      ->where('order_id', $orderId)
      ->count();

    return $deliveredCount;
  }

  private function isDeliveryDayForWeekly($givenDate, $selectedDays)
  {
    $selectedDaysJson = preg_replace('/(\w+):/', '"$1":', $selectedDays);
    $selectedDays = is_string($selectedDays) ? json_decode($selectedDaysJson, true) : $selectedDaysJson;
    $selectedDayCodes = array_map(function ($item) {
      return (string)$item['dayCode'];  // Convert dayCode to string for comparison
    }, $selectedDays);

    $currentDay = Carbon::parse($givenDate)->format('N');
    $currentDayCode = $currentDay % 7;
    return in_array($currentDayCode, $selectedDayCodes);
  }

  function getReconciliationReport()
  {
    $today = date('Y-m-d');

    // 1. Refund Query: Fetch refund totals based on order_id (refunds processed until today)
    $refunds = DB::table('transactions')
      ->select('order_id', DB::raw('SUM(amount) as total_refund'))
      ->where('type', '=', 3)  // Refund type
      ->whereDate('created_at', $today)  // Refunds processed until today
      ->groupBy('order_id')
      ->get();

    // 2. Main Reconciliation Query: Fetch order amounts for today's deliveries
    $data = DB::table("subscribed_order_delivery")
      ->select(
        DB::raw("SUM(CASE WHEN orders.order_type = 1 THEN orders.order_amount ELSE 0 END) as prepaid_total"),
        DB::raw("SUM(CASE WHEN orders.order_type = 2 THEN orders.order_amount ELSE 0 END) as postpaid_total"),
        DB::raw("SUM(CASE WHEN orders.order_type = 3 THEN orders.order_amount ELSE 0 END) as paynow_total"),
        DB::raw("SUM(CASE WHEN orders.order_type = 4 THEN orders.order_amount ELSE 0 END) as cod_total")
      )
      ->join('orders', 'orders.id', '=', 'subscribed_order_delivery.order_id')
      ->whereDate('subscribed_order_delivery.date', $today) // Only today's deliveries
      ->first();

    // 3. Append Refund Data to Main Data
    $totalRefundsToday = 0;
    foreach ($refunds as $refund) {
      $totalRefundsToday += $refund->total_refund;  // Sum up total refunds for today
    }

    // Add the refund amount to the result
    $data->refund_amount_today = $totalRefundsToday;

    // 4. Prepare the response
    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  function getReconciliationReportByDate($startDate, $endDate)
  {
    // Convert the input dates to 'Y-m-d' format
    $firstDate = Carbon::createFromFormat('d-m-Y', $startDate)->format('Y-m-d');
    $lastDate = Carbon::createFromFormat('d-m-Y', $endDate)->format('Y-m-d');

    // 1. Refund Query: Fetch refund amounts for each order within the date range
    $refunds = DB::table('transactions')
      ->select('order_id', DB::raw('SUM(amount) as total_refund'))
      ->where('type', '=', 3) // Refund type
      ->whereBetween(DB::raw('DATE(created_at)'), [$firstDate, $lastDate]) // Refunds processed within date range
      ->groupBy('order_id')
      ->get();

    // 2. Main Reconciliation Query: Fetch order amounts for deliveries within the date range
    $data = DB::table("subscribed_order_delivery")
      ->select(
        DB::raw("SUM(CASE WHEN orders.order_type = 1 THEN orders.order_amount ELSE 0 END) as prepaid_total"),
        DB::raw("SUM(CASE WHEN orders.order_type = 2 THEN orders.order_amount ELSE 0 END) as postpaid_total"),
        DB::raw("SUM(CASE WHEN orders.order_type = 3 THEN orders.order_amount ELSE 0 END) as paynow_total"),
        DB::raw("SUM(CASE WHEN orders.order_type = 4 THEN orders.order_amount ELSE 0 END) as cod_total")
      )
      ->join('orders', 'orders.id', '=', 'subscribed_order_delivery.order_id')
      ->whereBetween('subscribed_order_delivery.date', [$firstDate, $lastDate]) // Date range for subscribed order delivery
      ->first();

    // 3. Append Refund Data to the Main Data
    $totalRefundsInDateRange = 0;
    foreach ($refunds as $refund) {
      $totalRefundsInDateRange += $refund->total_refund;  // Sum up total refunds for the given date range
    }

    // Add the total refund amount to the result
    $data->refund_amount_today = $totalRefundsInDateRange;

    // 4. Prepare the response
    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  function getActiveVendorList()
  {

    $data = DB::table("vendor")
      ->select('vendor.*')
      ->where("vendor.is_active", "=", 1)
      ->orderBy("vendor.created_at", "DESC")
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  function getPoReportByDate($startDate, $endDate)
  {
    if (empty($startDate) || empty($endDate)) {
      Log::error("Start date or End date is missing");
      return response()->json([
        'success' => false,
        'message' => 'Start date or End date is required.',
      ], 400);
    }

    try {
      // Parse dates with Carbon to ensure proper handling
      $startDate = Carbon::parse($startDate)->startOfDay();
      $endDate = Carbon::parse($endDate)->endOfDay();

      // Fetch records where created_at is between the given dates and po_status is "Approved"
      $results = DB::table('purchars_order')
        ->select(
          'purchars_order.*',
          'vendor.supplier_name',
          'warehouse.warehouse_name'
        )
        ->join('warehouse', 'warehouse.id', '=', 'purchars_order.warehouse_id')
        ->join("vendor", "vendor.id", "=", "purchars_order.supplier_id")
        ->where('purchars_order.po_status', 'Approved')
        ->whereBetween(DB::raw("DATE(purchars_order.created_at)"), [$startDate, $endDate])
        ->orderBy('created_at', 'DESC')
        ->get();

      return response()->json([
        'success' => true,
        'data' => $results,
      ]);
    } catch (\Exception $e) {
      Log::error("Error in getPoReportByDate: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'message' => 'Error fetching purchase order data',
      ], 500);
    }
  }


  function getPoReport()
  {
    // Define the date range: 7 days ago to now
    $toDate = now(); // Current date and time
    $fromDate = now()->subDays(7); // 7 days before the current date
    try {
      // Fetch records where created_at is within the last 7 days and po_approved is "Approved"
      $results = DB::table('purchars_order')
        ->select(
          'purchars_order.*',
          'vendor.supplier_name',
          'warehouse.warehouse_name'
        )
        ->join('warehouse', 'warehouse.id', '=', 'purchars_order.warehouse_id')
        ->join("vendor", "vendor.id", "=", "purchars_order.supplier_id")
        ->where('purchars_order.po_status', 'Approved')
        ->whereBetween(DB::raw("DATE(purchars_order.created_at)"), [$fromDate, $toDate])
        ->orderBy('created_at', 'DESC')
        ->get();

      return response()->json([
        'success' => true,
        'data' => $results,
      ]);
    } catch (\Exception $e) {
      Log::error("Error in getAllPoReport: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'message' => 'Error fetching purchase order data',
      ], 500);
    }
  }


  public function getPiReportByDate($startDate, $endDate)
  {
    if (empty($startDate) || empty($endDate)) {
      Log::error("Start date or End date is missing");
      return response()->json([
        'success' => false,
        'message' => 'Start date or End date is required.',
      ], 400);
    }

    try {
      // Convert startDate and endDate to Carbon instances
      $start = Carbon::parse($startDate)->startOfDay(); // Start of the day for startDate
      $end = Carbon::parse($endDate)->endOfDay();       // End of the day for endDate

      $results = DB::table('purchase_invoice')
        ->select(
          'purchase_invoice.*',
          'vendor.supplier_name',
          'warehouse.district',
          'warehouse.warehouse_name'
        )
        ->join('warehouse', 'warehouse.id', '=', 'purchase_invoice.warehouse_id')
        ->join("vendor", "vendor.id", "=", "purchase_invoice.supplier_id")
        ->where('purchase_invoice.approval_status', 'Approved')
        ->whereBetween(DB::raw("DATE(purchase_invoice.created_at)"), [$start, $end])
        ->orderBy('created_at', 'DESC')
        ->get();

      return response()->json([
        'success' => true,
        'data' => $results,
      ]);
    } catch (\Exception $e) {
      Log::error("Error in getPiReportByDate: " . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Error fetching purchase invoice data',
      ], 500);
    }
  }


  function getPiReport()
  {
    // Define the date range: 7 days ago to now
    $toDate = now(); // Current date and time
    $fromDate = now()->subDays(7); // 7 days before the current date
    try {
      // Fetch records where created_at is within the last 7 days and po_approved is "Approved"
      $results = DB::table('purchase_invoice')
        ->select(
          'purchase_invoice.*',
          'vendor.supplier_name',
          'warehouse.district',
          'warehouse.warehouse_name'
        )
        ->join('warehouse', 'warehouse.id', '=', 'purchase_invoice.warehouse_id')
        ->join("vendor", "vendor.id", "=", "purchase_invoice.supplier_id")
        ->where('purchase_invoice.approval_status', 'Approved')
        ->whereBetween(DB::raw("DATE(purchase_invoice.created_at)"), [$fromDate, $toDate])
        ->orderBy('created_at', 'DESC')
        ->get();

      return response()->json([
        'success' => true,
        'data' => $results,
      ]);
    } catch (\Exception $e) {
      Log::error("Error in getAllPoReport: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'message' => 'Error fetching purchase order data',
      ], 500);
    }
  }

  function getPpReportByDate($startDate, $endDate)
  {
    try {
      // Parse dates using Carbon for start and end of day
      $fromDate = Carbon::parse($startDate)->startOfDay();
      $toDate = Carbon::parse($endDate)->endOfDay();

      // Fetch records where created_at is between the given dates and payment_status is "Paid"
      $results = DB::table('purchase_invoice')
        ->select(
          'purchase_invoice.*',
          'vendor.supplier_name',
          'warehouse.warehouse_name'
        )
        ->join('warehouse', 'warehouse.id', '=', 'purchase_invoice.warehouse_id')
        ->join("vendor", "vendor.id", "=", "purchase_invoice.supplier_id")
        ->where('purchase_invoice.payment_status', 'Paid')
        ->whereBetween(DB::raw("DATE(purchase_invoice.created_at)"), [$fromDate, $toDate])
        ->orderBy('created_at', 'DESC')
        ->get();

      return response()->json([
        'success' => true,
        'data' => $results,
      ]);
    } catch (\Exception $e) {
      Log::error("Error in getPpReportByDate: " . $e->getMessage());
      return response()->json([
        'success' => false,
        'message' => 'Error fetching purchase order data',
      ], 500);
    }
  }


  function getPpReport()
  {
    // Define the date range: 7 days ago to now
    $toDate = now(); // Current date and time
    $fromDate = now()->subDays(7); // 7 days before the current date

    try {
      // Fetch records where created_at is within the last 7 days and po_approved is "Paid"
      $results = DB::table('purchase_invoice')
        ->select(
          'purchase_invoice.*',
          'vendor.supplier_name',
          'warehouse.warehouse_name'
        )
        ->join('warehouse', 'warehouse.id', '=', 'purchase_invoice.warehouse_id')
        ->join("vendor", "vendor.id", "=", "purchase_invoice.supplier_id")
        ->where('purchase_invoice.payment_status', 'Paid')
        ->whereBetween(DB::raw("DATE(purchase_invoice.created_at)"), [$fromDate, $toDate])
        ->orderBy('created_at', 'DESC')
        ->get();

      return response()->json([
        'success' => true,
        'data' => $results,
      ]);
    } catch (\Exception $e) {
      Log::error("Error in getAllPpReport: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'message' => 'Error fetching purchase order data',
      ], 500);
    }
  }

  function getPrReportByDate($startDate, $endDate)
  {
    if (empty($startDate) || empty($endDate)) {
      Log::error("Start date or End date is missing");
      return response()->json([
        'success' => false,
        'message' => 'Start date or End date is required.',
      ], 400);
    }

    try {
      // Parse dates using Carbon for start and end of day
      $start = Carbon::parse($startDate)->startOfDay();
      $end = Carbon::parse($endDate)->endOfDay();

      $query = DB::table("purchase_return")
        ->select(
          'purchase_return.*',
          'purchase_invoice.invoice_amount as pi_amount',
          'vendor.supplier_name',
          'warehouse.warehouse_name'
        )
        ->join('purchase_invoice', 'purchase_return.pi_id', '=', 'purchase_invoice.id')
        ->join('warehouse', 'warehouse.id', '=', 'purchase_return.warehouse_id')
        ->join("vendor", "vendor.id", "=", "purchase_return.supplier_id")
        ->where('purchase_return.pr_status', 'Approved')
        ->whereBetween(DB::raw("DATE(purchase_return.created_at)"), [$start, $end])
        ->orderBy('purchase_return.created_at', 'DESC')
        ->get();

      return response()->json([
        'success' => true,
        'data' => $query,
      ]);
    } catch (\Exception $e) {
      Log::error("Error in getPrReportByDate: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'message' => 'Error fetching purchase return data',
      ], 500);
    }
  }

  function getPrReport()
  {
    $toDate = now();
    $fromDate = now()->subDays(7);
    try {
      $results = DB::table("purchase_return")
        ->select(
          'purchase_return.*',
          'purchase_invoice.invoice_amount as pi_amount',
          'vendor.supplier_name',
          'warehouse.warehouse_name'
        )
        ->join('purchase_invoice', 'purchase_return.pi_id', '=', 'purchase_invoice.id')
        ->join('warehouse', 'warehouse.id', '=', 'purchase_return.warehouse_id')
        ->join("vendor", "vendor.id", "=", "purchase_return.supplier_id")
        ->where('purchase_return.pr_status', 'Approved')
        ->whereBetween(DB::raw("DATE(purchase_return.created_at)"), [$fromDate, $toDate])
        ->orderBy('purchase_return.created_at', 'DESC')
        ->get();

      return response()->json([
        'success' => true,
        'data' => $results,
      ]);
    } catch (\Exception $e) {
      Log::error("Error in getAllPrReport: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'message' => 'Error fetching purchase return data',
      ], 500);
    }
  }

  function getSaReport()
  {
    // Define the date range: 7 days ago to now
    $toDate = now(); // Current date and time
    $fromDate = now()->subDays(7); // 7 days before the current date

    try {
      // Fetch records where created_at is within the last 7 days and po_approved is "Approved"
      $results = DB::table('stock_approval')
        ->select(
          'stock_approval.*',
          'product.title as product_title',
        )
        ->join('product', 'product.id', '=', 'stock_approval.product_id')
        ->where('stock_approval.approval_status', 'Approved')
        ->whereBetween(DB::raw("DATE(stock_approval.created_at)"), [$fromDate, $toDate])
        ->orderBy('created_at', 'DESC')
        ->get();

      return response()->json([
        'success' => true,
        'data' => $results,
      ]);
    } catch (\Exception $e) {
      Log::error("Error in getAllSaReport: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'message' => 'Error fetching purchase order data',
      ], 500);
    }
  }

  function getSaReportByDate($startDate, $endDate)
  {

    if (empty($startDate) || empty($endDate)) {
      Log::error("Start date or End date is missing");
      return response()->json([
        'success' => false,
        'message' => 'Start date or End date is required.',
      ], 400);
    }

    try {
      // Parse dates using Carbon for start and end of day
      $fromDate = Carbon::parse($startDate)->startOfDay();
      $toDate = Carbon::parse($endDate)->endOfDay();

      $results = DB::table('stock_approval')
        ->select(
          'stock_approval.*',
          'product.title as product_title',
        )
        ->join('product', 'product.id', '=', 'stock_approval.product_id')
        ->where('stock_approval.approval_status', 'Approved')
        ->whereBetween(DB::raw("DATE(stock_approval.created_at)"), [$fromDate, $toDate])
        ->orderBy('created_at', 'DESC')
        ->get();

      return response()->json([
        'success' => true,
        'data' => $results,
      ]);
    } catch (\Exception $e) {
      Log::error("Error in getSaReportByDate: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'message' => 'Error fetching purchase order data',
      ], 500);
    }
  }
}
