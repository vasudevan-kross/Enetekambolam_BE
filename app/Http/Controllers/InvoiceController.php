<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PDF;
use App\Models\OrderModel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\OrderController;
use Carbon\Carbon;
use App\Models\InvoiceSettingModel;

class InvoiceController extends Controller
{
  public function Invoice($id)
  {
    try {
      $order = DB::table("orders")
        ->select('orders.*')
        ->where("orders.id", "=", $id)
        ->first();
      $orderController = new OrderController();
      $response = $orderController->getCartProductDataById($id);
      $taxPrice = ($order->price * $order->tax) / 100;
      $responseData = json_decode($response->getContent(), true);
      $cartProducts = $responseData['data'] ?? [];

      foreach ($cartProducts as &$product) {
        // Perform calculations for each product
        $product['totalAmount'] = ($product['mrp'] ?? 0) * ($product['qty'] ?? 0);
        $product['discount'] = (($product['mrp'] ?? 0) - ($product['price'] ?? 0)) * ($product['qty'] ?? 0);
        $product['totalPrice'] = $product['totalAmount'] - $product['discount'];
        $product['totalTax'] = $product['totalPrice'] * ($product['tax'] ?? 0) / 100;
        $product['netAmount'] = $product['totalPrice'] + $product['totalTax'];
      }

      $userData = DB::table("user_address")
        ->select(
          'user_address.name',
          'user_address.s_phone',
          'user_address.flat_no',
          'user_address.landmark',
          'user_address.apartment_name',
          'user_address.area',
          'user_address.city',
          'user_address.pincode'
        )
        ->where("user_address.id", "=", $order->address_id)
        ->first();

      $dataInvoiceSet1 = DB::table("invoice_setting")
        ->select('invoice_setting.*')
        ->where("invoice_setting.id", "=", 1)
        ->first();
      $dataInvoiceSet2 = DB::table("invoice_setting")
        ->select('invoice_setting.*')
        ->where("invoice_setting.id", "=", 2)
        ->first();
      $dataInvoiceSet3 = DB::table("invoice_setting")
        ->select('invoice_setting.*')
        ->where("invoice_setting.id", "=", 3)
        ->first();
      $dataInvoiceSet4 = DB::table("invoice_setting")
        ->select('invoice_setting.*')
        ->where("invoice_setting.id", "=", 4)
        ->first();
      $dataInvoiceSet5 = DB::table("invoice_setting")
        ->select('invoice_setting.*')
        ->where("invoice_setting.id", "=", 5)
        ->first();
      $dataInvoiceSet6 = DB::table("invoice_setting")
        ->select('invoice_setting.*')
        ->where("invoice_setting.id", "=", 6)
        ->first();
      $paymeny_status = $order->trasation_id != null ? "Paid" : "Pending";
      $default_logo_path = public_path('images/logo.png');
      $logo_path = public_path('uploads/images/' . $dataInvoiceSet1->value);
      try {
        if (file_exists($logo_path) && is_readable($logo_path)) {
          $logo_url = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
        } else {
          $logo_url = 'data:image/png;base64,' . base64_encode(file_get_contents($default_logo_path));
        }
      } catch (Exception $e) {
        $logo_url = 'data:image/png;base64,' . base64_encode(file_get_contents($default_logo_path));
      }
      $invoice_date = date('jS F Y', strtotime($order->created_at));
      $pdf = PDF::loadView('invoice_pdf', array(
        'order' => $order,
        'userData' => $userData,
        'cartProducts' => $cartProducts,
        'taxPrice' => $taxPrice,
        'logo_url' => $logo_url,
        'paymeny_status' => $paymeny_status,
        'lp_1' => $dataInvoiceSet2->value ?? "--",
        'lp_2' => $dataInvoiceSet3->value ?? "--",
        'lp_3' => $dataInvoiceSet4->value ?? "--",
        'lp_4' => $dataInvoiceSet5->value ?? "--",
        'bp' => $dataInvoiceSet6->value ?? "--"
      ));

      return $pdf->download('Invoice_Order_No  ' . $order->order_number . ' Date_' . $invoice_date . '.pdf');
    } catch (\Exception $e) {
      // Log the error and return a response
      Log::error('Error generating invoice: ' . $e->getMessage());
      return response()->json(['error' => 'Failed to generate invoice'], 500);
    }
  }
  public function SubInvoice($id)
  {
    $dataInvoiceSet1 = DB::table("invoice_setting")
      ->select('invoice_setting.*')
      ->where("invoice_setting.id", "=", 1)
      ->first();
    $dataInvoiceSet2 = DB::table("invoice_setting")
      ->select('invoice_setting.*')
      ->where("invoice_setting.id", "=", 2)
      ->first();
    $dataInvoiceSet3 = DB::table("invoice_setting")
      ->select('invoice_setting.*')
      ->where("invoice_setting.id", "=", 3)
      ->first();
    $dataInvoiceSet4 = DB::table("invoice_setting")
      ->select('invoice_setting.*')
      ->where("invoice_setting.id", "=", 4)
      ->first();
    $dataInvoiceSet5 = DB::table("invoice_setting")
      ->select('invoice_setting.*')
      ->where("invoice_setting.id", "=", 5)
      ->first();
    $dataInvoiceSet6 = DB::table("invoice_setting")
      ->select('invoice_setting.*')
      ->where("invoice_setting.id", "=", 6)
      ->first();

    $order = DB::table("orders")
      ->select(
        'orders.*',
        'product.id as product_id',
        'product.qty_text',
        'product.title',
        'user_address.name',
        'user_address.s_phone',
        'user_address.flat_no',
        'user_address.landmark',
        'user_address.apartment_name',
        'user_address.area',
        'user_address.city',
        'user_address.pincode'
      )
      ->Join('product', 'orders.product_id', '=', 'product.id')
      ->Join('user_address', 'user_address.id', '=', 'orders.address_id')
      ->where("orders.id", "=", $id)
      ->first();
    $subName = $order->subscription_type == 1 ? "One Time Order" : ($order->subscription_type == 2 ? "Weekly Subscription" : ($order->subscription_type == 3 ? "Monthly Subscription" : ($order->subscription_type == 4 ? "Alternative Subscription" : "N/A")));

    if ($order) {
      // Calculate quantity based on subscription type
      switch ($order->subscription_type) {
        case 1:
          $order->qty = $order->qty * 1;  // Case 1: qty * 1
          break;
        case 2:
          // Case 2: qty remains unchanged, no need to explicitly reassign
          break;
        case 3:
          $order->qty = $order->qty * 30;  // Case 3: qty * 30
          break;
        case 4:
          $order->qty = $order->qty * 15;  // Case 4: qty * 15
          break;
        default:
          // Default: qty remains unchanged, no need to explicitly reassign
          break;
      }
      $order->totalAmount = $order->mrp * $order->qty;
      $order->discount = ($order->mrp - $order->price) * $order->qty;
      $order->totalPrice = $order->totalAmount - $order->discount;
      $order->totalTax = $order->totalPrice * $order->tax / 100;
      $order->netAmount = $order->totalPrice + $order->totalTax;
    }
    //  $myDate = $month.'/01/2020';
    //  $date = Carbon::createFromFormat('m/d/Y', $myDate);

    // $monthName = $date->format('F');
    $deliveryCount = DB::table("subscribed_order_delivery")
      ->select('subscribed_order_delivery.*')
      ->where('order_id', '=', $order->id)
      //  ->whereMonth('date', '=', $month)
      //  ->whereYear('date', '=', $year)
      ->count();
    $taxPrice = ($order->price * $order->tax) / 100;
    $deliveryDays = $order->subscription_type == 1 ? 1 : ($order->subscription_type == 2 ? "7" : ($order->subscription_type == 3 ? "30" : ($order->subscription_type == 4 ? "15" : $deliveryCount)));
    $default_logo_path = public_path('images/logo.png');
    $logo_path = public_path('uploads/images/' . $dataInvoiceSet1->value);
    try {
      if (file_exists($logo_path) && is_readable($logo_path)) {
        $logo_url = 'data:image/png;base64,' . base64_encode(file_get_contents($logo_path));
      } else {
        $logo_url = 'data:image/png;base64,' . base64_encode(file_get_contents($default_logo_path));
      }
    } catch (Exception $e) {
      $logo_url = 'data:image/png;base64,' . base64_encode(file_get_contents($default_logo_path));
    }

    $invoice_date = date('jS F Y', strtotime($order->created_at));
    $pdf = PDF::loadView('sub_invoice_pdf', array(
      'order' => $order,
      'subName' => $subName,
      'deliveryCount' => $deliveryCount,
      'taxPrice' => $taxPrice,
      'logo_url' => $logo_url,
      'lp_1' => $dataInvoiceSet2->value ?? "--",
      'lp_2' => $dataInvoiceSet3->value ?? "--",
      'lp_3' => $dataInvoiceSet4->value ?? "--",
      'lp_4' => $dataInvoiceSet5->value ?? "--",
      'bp' => $dataInvoiceSet6->value ?? "--",
      'deliveryDays' => $deliveryDays
    ));;

    return $pdf->download('Invoice_Order_No ' . $order->order_number . ' Date_' . $invoice_date . '.pdf');
  }
}
