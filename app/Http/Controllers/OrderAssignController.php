<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderAssignModelModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\WebAppSettingsController;
use Carbon\Carbon;
//

class OrderAssignController extends Controller
{
  function getAllSubAllDelivery()
  {
    $data = DB::table("orders")
    ->select(
      'order_user_assign.id as order_user_assign_id',
      'order_user_assign.user_id  as order_assign_user',
      "orders.id",
      'orders.order_type',
      'orders.order_amount',
      'orders.qty',
      'orders.selected_days_for_weekly',
      'orders.subscription_type',
      'orders.order_type',
        'orders.start_date',
      'orders.created_at',
      'orders.updated_at',
      "orders.user_id",
      'orders.order_number',
     'product.title',
      'images.image as product_image',
     'product.qty_text',
     'user_address.name',
     'user_address.s_phone',
     'user_address.flat_no',
     'user_address.apartment_name',
     'user_address.area',
     'user_address.city',
     'user_address.pincode',
     'users.wallet_amount'
    
    )  
    ->Join('order_user_assign','order_user_assign.order_id', '=','orders.id')
         ->leftJoin('images', function ($join) {
       $join->on('images.table_id', '=', 'orders.product_id')
       ->where('images.table_name','=',"product")
       ->where('images.image_type','=',1);
       })
    ->Join('product','orders.product_id', '=','product.id')
    ->Join('users','users.id', '=','orders.user_id')
     ->Join('user_address','user_address.id', '=','orders.address_id')
     ->where("orders.subscription_type","!=",null)
     ->where("orders.order_status","=",0)
     ->where("orders.status","=",1)
      // ->where("users.wallet_amount",">",250)
     ->orderBy('orders.created_at','DESC')
      ->get();
      
      if(count($data)>0){
        for($i=0;$i<count($data);$i++){
          $data->user_holiday=
          $data[$i]->user_holiday = DB::table("user_holiday")
          ->select(
            'user_holiday.date',
          )  
          ->where("user_holiday.user_id","=",$data[$i]->user_id)
           ->orderBy('user_holiday.created_at','DESC')
            ->get();
       }
      }
          $response = [
              "response"=>200,
              'data'=>$data
          ];
    

    return response($response, 200);
  }

  // function getAllSubAllDeliveryByDate($date)
  // {
  //   $data = DB::table("orders")
  //   ->select(
  //     'order_user_assign.id as order_user_assign_id',
  //     'order_user_assign.user_id  as order_assign_user',
  //     "orders.id",
  //     'orders.order_type',
  //     'orders.order_amount',
  //     'orders.qty',
  //     'orders.selected_days_for_weekly',
  //     'orders.subscription_type',
  //     'orders.order_type',
  //       'orders.start_date',
  //     'orders.created_at',
  //     'orders.updated_at',
  //     "orders.user_id",
  //    'product.title',
  //     'images.image as product_image',
  //    'product.qty_text',
  //    'user_address.name',
  //    'user_address.s_phone',
  //    'user_address.flat_no',
  //    'user_address.apartment_name',
  //    'user_address.area',
  //    'user_address.city',
  //    'user_address.pincode',
  //    'users.wallet_amount',
  //    'subscribed_order_delivery.date as delivered_date'
    
  //   )  
  //   ->Join('order_user_assign','order_user_assign.order_id', '=','orders.id')
  //        ->leftJoin('images', function ($join) {
  //      $join->on('images.table_id', '=', 'orders.product_id')
  //      ->where('images.table_name','=',"product")
  //      ->where('images.image_type','=',1);
  //      })
  //      ->leftJoin('subscribed_order_delivery', function ($join) use($date) {
  //       $join->on('subscribed_order_delivery.order_id', '=', 'orders.id')
  //       ->where('subscribed_order_delivery.date','=',$date);
  //       })

  //   ->Join('product','orders.product_id', '=','product.id')
  //   ->Join('users','users.id', '=','orders.user_id')
  //    ->Join('user_address','user_address.id', '=','orders.address_id')
     
  //   ->where("orders.subscription_type","!=",null)
  //    ->where("orders.order_status","=",0)
  //    ->where("orders.status","=",1)
  //     // ->where("users.wallet_amount",">",250)
  //    ->orderBy('orders.created_at','DESC')
  //     ->get();
      
  //     if(count($data)>0){
  //       for($i=0;$i<count($data);$i++){
  //         $data->user_holiday=
  //         $data[$i]->user_holiday = DB::table("user_holiday")
  //         ->select(
  //           'user_holiday.date',
  //         )  
  //         ->where("user_holiday.user_id","=",$data[$i]->user_id)
  //          ->orderBy('user_holiday.created_at','DESC')
  //           ->get();
  //      }
  //     }
  //         $response = [
  //             "response"=>200,
  //             'data'=>$data
  //         ];
    

  //   return response($response, 200);
  // }

    function getAllSubAllDeliveryByDate($date)
  {
      // Get the status data
      $webAppSettingsController = new WebAppSettingsController();
      $statusDataResponse = $webAppSettingsController->getDataDataById(11);
      $statusData = json_decode($statusDataResponse->getContent(), true);

      $date = Carbon::parse($date);

      // Define the date range
      $dayAfterTomorrow = $date->copy()->addDays(2)->toDateString(); // Add 2 days to the date
      $startDate = $date->toDateString(); // Start date as a string

      // Start building the base query
      $query = DB::table("orders")
          ->select(
              "orders.id",
              'orders.order_type',
              'orders.order_amount',
              'orders.qty',
              'orders.selected_days_for_weekly',
              'orders.subscription_type',
              'orders.order_type',
              'orders.start_date',
              'orders.created_at',
              'orders.updated_at',
              "orders.user_id",
              'orders.order_number',
              'product.title',
              'images.image as product_image',
              'product.qty_text',
              'user_address.name',
              'user_address.s_phone',
              'user_address.flat_no',
              'user_address.apartment_name',
              'user_address.area',
              'user_address.city',
              'user_address.pincode',
              'users.wallet_amount',
              'subscribed_order_delivery.date as delivered_date'
          )
          ->leftJoin('images', function ($join) {
              $join->on('images.table_id', '=', 'orders.product_id')
                  ->where('images.table_name', '=', "product")
                  ->where('images.image_type', '=', 1);
          })
          ->leftJoin('subscribed_order_delivery', function ($join) use ($startDate, $dayAfterTomorrow) {
              $join->on('subscribed_order_delivery.order_id', '=', 'orders.id')
                  ->whereBetween('subscribed_order_delivery.date', [$startDate, $dayAfterTomorrow]);
          })
          ->join('product', 'orders.product_id', '=', 'product.id')
          ->join('users', 'users.id', '=', 'orders.user_id')
          ->join('user_address', 'user_address.id', '=', 'orders.address_id')
          ->where("orders.subscription_type", "!=", null)
          ->where("orders.order_status", "=", 0)
          ->where("orders.status", "=", 1)
          ->whereBetween(DB::raw("DATE(orders.created_at)"), [$startDate, $dayAfterTomorrow]) // Filter by created_at date range
          ->orderBy('orders.created_at', 'DESC');

      // Check if the statusData value is "false"
      // if ($statusData['response'] === 200 && isset($statusData['data']) && $statusData['data']['value'] !== "false") {
      //     // Include `order_user_assign` join and select its fields only if the condition is met
      //     $query->join('order_user_assign', 'order_user_assign.order_id', '=', 'orders.id')
      //           ->addSelect(
      //               'order_user_assign.id as order_user_assign_id',
      //               'order_user_assign.user_id as order_assign_user'
      //           );
      // }

      // Execute the query and get the results
      $data = $query->get();

      // Add user holidays for each order
      foreach ($data as $order) {
          $order->user_holiday = DB::table("user_holiday")
              ->select('user_holiday.date')
              ->where("user_holiday.user_id", "=", $order->user_id)
              ->orderBy('user_holiday.created_at', 'DESC')
              ->get();
      }

      // Prepare the response
      $response = [
          "response" => 200,
          'data' => $data
      ];

      return response($response, 200);
  }


  function getAllSubAllDeliveryByAssignUser($id)
  {
   
    $data = DB::table("order_user_assign")
    ->select(
      'order_user_assign.id as order_user_assign_id',
      'order_user_assign.user_id  as order_assign_user',
      "orders.id",
      'orders.order_type',
      'orders.order_amount',
      'orders.qty',
      'orders.selected_days_for_weekly',
      'orders.subscription_type',
      'orders.order_type',
         'orders.start_date',
      'orders.created_at',
      'orders.updated_at',
      'orders.order_number',
      "orders.user_id",
     'product.title',
      'images.image as product_image',
     'product.qty_text',
     'user_address.name',
     'user_address.s_phone',
     'user_address.flat_no',
     'user_address.apartment_name',
     'user_address.area',
     'user_address.city',
     'user_address.pincode',
     'users.wallet_amount'
    )  
    ->Join('orders','orders.id', '=','order_user_assign.order_id')
         ->leftJoin('images', function ($join) {
       $join->on('images.table_id', '=', 'orders.product_id')
       ->where('images.table_name','=',"product")
       ->where('images.image_type','=',1);
       })
    ->Join('product','orders.product_id', '=','product.id')
    ->Join('users','users.id', '=','orders.user_id')
     ->Join('user_address','user_address.id', '=','orders.address_id')

     ->where("orders.subscription_type","!=",null)
     ->where("orders.order_status","=",0)
     ->where("orders.status","=",1)
      // ->where("users.wallet_amount",">",250)
      ->where('order_user_assign.user_id','=',$id)
     ->orderBy('orders.created_at','DESC')
      ->get();
      if(count($data)>0){
        for($i=0;$i<count($data);$i++){
          $data->user_holiday=
          $data[$i]->user_holiday = DB::table("user_holiday")
          ->select(
            'user_holiday.date',
          )  
          ->where("user_holiday.user_id","=",$data[$i]->user_id)
           ->orderBy('user_holiday.created_at','DESC')
            ->get();
       }
      }
          $response = [
              "response"=>200,
              'data'=>$data,
          ];
    

    return response($response, 200);
  }
  function getAllSubAllDeliveryByAssignUserByDate($id,$date)
  {
   
    $data = DB::table("order_user_assign")
    ->select(
      'order_user_assign.id as order_user_assign_id',
      'order_user_assign.user_id  as order_assign_user',
      "orders.id",
      'orders.order_type',
      'orders.order_amount',
      'orders.qty',
      'orders.selected_days_for_weekly',
      'orders.subscription_type',
      'orders.order_type',
         'orders.start_date',
      'orders.created_at',
      'orders.updated_at',
      'orders.order_number',
      "orders.user_id",
     'product.title',
      'images.image as product_image',
     'product.qty_text',
     'user_address.name',
     'user_address.s_phone',
     'user_address.flat_no',
     'user_address.apartment_name',
     'user_address.area',
     'user_address.city',
     'user_address.pincode',
     'users.wallet_amount',
     'subscribed_order_delivery.date as delivered_date'
    )  
    ->Join('orders','orders.id', '=','order_user_assign.order_id')
         ->leftJoin('images', function ($join) {
       $join->on('images.table_id', '=', 'orders.product_id')
       ->where('images.table_name','=',"product")
       ->where('images.image_type','=',1);
       })
       ->leftJoin('subscribed_order_delivery', function ($join) use($date) {
        $join->on('subscribed_order_delivery.order_id', '=', 'orders.id')
        ->where('subscribed_order_delivery.date','=',$date);
        })
    ->Join('product','orders.product_id', '=','product.id')
    ->Join('users','users.id', '=','orders.user_id')
     ->Join('user_address','user_address.id', '=','orders.address_id')

     ->where("orders.subscription_type","!=",null)
     ->where("orders.order_status","=",0)
     ->where("orders.status","=",1)
      // ->where("users.wallet_amount",">",250)
      ->where('order_user_assign.user_id','=',$id)
     ->orderBy('orders.created_at','DESC')
      ->get();
      if(count($data)>0){
        for($i=0;$i<count($data);$i++){
          $data->user_holiday=
          $data[$i]->user_holiday = DB::table("user_holiday")
          ->select(
            'user_holiday.date',
          )  
          ->where("user_holiday.user_id","=",$data[$i]->user_id)
           ->orderBy('user_holiday.created_at','DESC')
            ->get();
       }
      }
          $response = [
              "response"=>200,
              'data'=>$data,
          ];
    

    return response($response, 200);
  }
  // function getAllNoramlDelivery()
  // {
  //   $webAppSettingsController = new WebAppSettingsController();
  //   $statusDataResponse = $webAppSettingsController->getDataDataById(11);
  //   $statusData = json_decode($statusDataResponse->getContent(), true);
    
  //   $data = DB::table("orders")
  //   ->select(
  //     'order_user_assign.id as order_user_assign_id',
  //     'order_user_assign.user_id  as order__assign_user',
  //     "orders.id",
  //     'orders.order_type',
  //     'orders.order_amount',
  //     'orders.qty',
  //     'orders.selected_days_for_weekly',
  //     'orders.subscription_type',
  //     'orders.order_type',
  //     'orders.created_at',
  //     'orders.updated_at',
  //     'orders.delivery_status',
  //    'product.title',
  //     'images.image as product_image',
  //    'product.qty_text',
  //    'user_address.name',
  //    'user_address.s_phone',
  //    'user_address.flat_no',
  //    'user_address.apartment_name',
  //    'user_address.area',
  //    'user_address.city',
  //    'user_address.pincode',
  //    'users.wallet_amount'
  //   )  
  //    ->Join('order_user_assign','order_user_assign.order_id', '=','orders.id')
  //        ->leftJoin('images', function ($join) {
  //      $join->on('images.table_id', '=', 'orders.product_id')
  //      ->where('images.table_name','=',"product")
  //      ->where('images.image_type','=',1);
  //      })
  //   ->Join('product','orders.product_id', '=','product.id')
  //   ->Join('users','users.id', '=','orders.user_id')
  //    ->Join('user_address','user_address.id', '=','orders.address_id')
  //     ->where("orders.subscription_type","=",null)
  //    ->where("orders.status","=",1)
  //    ->where("orders.delivery_status",'=',null)
  //    ->orderBy('orders.created_at','DESC')
  //     ->get();
    
  //         $response = [
  //             "response"=>200,
  //             'data'=>$data,
  //         ];
    

  //   return response($response, 200);
  // }

  function getAllNoramlDelivery()
{
    // Get the status data
    $webAppSettingsController = new WebAppSettingsController();
    $statusDataResponse = $webAppSettingsController->getDataDataById(11);
    $statusData = json_decode($statusDataResponse->getContent(), true);

    // Define the date range for today, tomorrow, and the day after tomorrow
    $today = now()->startOfDay()->toDateString(); // Convert to date string
    $dayAfterTomorrow = now()->addDays(2)->toDateString();

    // Start building the base query
    $query = DB::table("orders")
        ->select(
            "orders.id",
            'orders.order_type',
            'orders.order_amount',
            'orders.qty',
            'orders.selected_days_for_weekly',
            'orders.subscription_type',
            'orders.order_type',
            'orders.created_at',
            'orders.updated_at',
            'orders.order_number',
            'orders.delivery_status',
            'orders.product_detail',
            'user_address.name',
            'user_address.s_phone',
            'user_address.flat_no',
            'user_address.apartment_name',
            'user_address.area',
            'user_address.city',
            'user_address.pincode',
            'users.wallet_amount'
        )
        ->join('users', 'users.id', '=', 'orders.user_id')
        ->join('user_address', 'user_address.id', '=', 'orders.address_id')
        ->where("orders.subscription_type", "=", null)
        ->where("orders.status", "=", 1)
        ->where("orders.delivery_status", '=', null)
        // ->whereBetween("orders.created_at", [$today, $dayAfterTomorrow])
        ->whereBetween(DB::raw("DATE(orders.created_at)"), [$today, $dayAfterTomorrow]) // Filter by date only // Filter by created_at date range
        ->orderBy('orders.created_at', 'DESC');

    // Check if the statusData value is "false"
    // if ($statusData['response'] === 200 && isset($statusData['data']) && $statusData['data']['value'] !== "false") {
    //     // Include `order_user_assign` join and select its fields only if the condition is met
    //     $query->join('order_user_assign', 'order_user_assign.order_id', '=', 'orders.id')
    //           ->addSelect(
    //               'order_user_assign.id as order_user_assign_id',
    //               'order_user_assign.user_id as order__assign_user'
    //           );
    // }

    // Execute the query and get the results
    $data = $query->get();

    // Prepare the response
    $response = [
        "response" => 200,
        'data' => $data,
    ];

    return response($response, 200);
}
  function getDeliveryDataByUId($id)
  {
    $data = DB::table("order_user_assign")
    ->select(
      'order_user_assign.id as order_user_assign_id',
      'order_user_assign.user_id  as order__assign_user',
      "orders.id",
      'orders.order_type',
      'orders.order_amount',
      'orders.qty',
      'orders.selected_days_for_weekly',
      'orders.subscription_type',
      'orders.order_type',
      'orders.created_at',
      'orders.updated_at',
      'orders.delivery_status',
      'orders.order_number',
      'subscribed_order_delivery.created_at as delivery_timestamp',
      'subscribed_order_delivery.payment_mode',
     'product.title',
      'images.image as product_image',
     'product.qty_text',
     'user_address.name',
     'user_address.s_phone',
     'user_address.flat_no',
     'user_address.apartment_name',
     'user_address.area',
     'user_address.city',
     'user_address.pincode',
     'users.wallet_amount'
  
    )  ->where('order_user_assign.user_id','=',$id)
    
     ->Join('orders','orders.id', '=','order_user_assign.order_id')
         ->leftJoin('images', function ($join) {
       $join->on('images.table_id', '=', 'orders.product_id')
       ->where('images.table_name','=',"product")
       ->where('images.image_type','=',1);
       })
    ->Join('product','orders.product_id', '=','product.id')
    ->Join('users','users.id', '=','orders.user_id')
     ->Join('user_address','user_address.id', '=','orders.address_id')
       ->Join('subscribed_order_delivery','subscribed_order_delivery.order_id', '=','orders.id')

      ->where("orders.subscription_type","=",null)
     ->where("orders.status","=",1)
     ->where("orders.delivery_status",'=',1)
     ->orderBy('orders.created_at','DESC')
      ->get();
    
          $response = [
              "response"=>200,
              'data'=>$data,
          ];
    

    return response($response, 200);
  }
  function getNormalDataByUId($id)
  {

    $data = DB::table("order_user_assign")
    ->select(
      'order_user_assign.id as order_user_assign_id',
      'order_user_assign.user_id  as order__assign_user',
      "orders.id",
      'orders.order_type',
      'orders.order_amount',
      'orders.qty',
      'orders.selected_days_for_weekly',
      'orders.subscription_type',
      'orders.order_type',
      'orders.created_at',
      'orders.updated_at',
      'orders.delivery_status',
      'orders.order_number',
     'product.title',
      'images.image as product_image',
     'product.qty_text',
     'user_address.name',
     'user_address.s_phone',
     'user_address.flat_no',
     'user_address.apartment_name',
     'user_address.area',
     'user_address.city',
     'user_address.pincode',
     'users.wallet_amount',
  
    )  ->where('order_user_assign.user_id','=',$id)
    
     ->Join('orders','orders.id', '=','order_user_assign.order_id')
         ->leftJoin('images', function ($join) {
       $join->on('images.table_id', '=', 'orders.product_id')
       ->where('images.table_name','=',"product")
       ->where('images.image_type','=',1);
       })
    ->Join('product','orders.product_id', '=','product.id')
    ->Join('users','users.id', '=','orders.user_id')
     ->Join('user_address','user_address.id', '=','orders.address_id')
      ->where("orders.subscription_type","=",null)
     ->where("orders.status","=",1)
     ->where("orders.delivery_status",'=',null)
     ->orderBy('orders.created_at','DESC')
      ->get();
    
          $response = [
              "response"=>200,
              'data'=>$data,
          ];
    

    return response($response, 200);
  }
  function getOrderAssignUserByOrderId($id)
  {

    $data = DB::table("order_user_assign")
    ->select(
      'order_user_assign.*',
      'users.id as user_id',
      'users.email',
      'users.phone',
      'users.name'
    )
    ->where("order_user_assign.order_id","=",$id)
    ->Join('users','users.id', '=','order_user_assign.user_id')
     ->orderBy('order_user_assign.created_at','DESC')
      ->get();
    
          $response = [
              "response"=>200,
              'data'=>$data,
          ];
    

    return response($response, 200);
  }
  function getDataByUIdAndDeliveredSub($id)
  {
   
    $data = DB::table("order_user_assign")
    ->select(
      'order_user_assign.id as order_user_assign_id',
      'order_user_assign.user_id  as order__assign_user',
      "orders.id",
      'orders.order_type',
      'orders.order_amount',
      'orders.qty',
      'orders.selected_days_for_weekly',
      'orders.subscription_type',
      'orders.order_type',
      'orders.created_at',
      'orders.order_number',
      'orders.updated_at',
     'product.title',
      'images.image as product_image',
     'product.qty_text',
     'user_address.name',
     'user_address.s_phone',
     'user_address.flat_no',
     'user_address.apartment_name',
     'user_address.area',
     'user_address.city',
     'user_address.pincode',
     'users.wallet_amount',
     "subscribed_order_delivery.date as deliver_date",
     'subscribed_order_delivery.created_at as delivery_timestamp',
    )  ->where('order_user_assign.user_id','=',$id)
    ->Join('subscribed_order_delivery','subscribed_order_delivery.order_id', '=','order_user_assign.order_id')

     ->Join('orders','orders.id', '=','order_user_assign.order_id')
         ->leftJoin('images', function ($join) {
       $join->on('images.table_id', '=', 'orders.product_id')
       ->where('images.table_name','=',"product")
       ->where('images.image_type','=',1);
       })
    ->Join('product','orders.product_id', '=','product.id')
    ->Join('users','users.id', '=','orders.user_id')
     ->Join('user_address','user_address.id', '=','orders.address_id')
     ->where("orders.subscription_type","!=",null)
     ->where("orders.order_status","=",0)
     ->where("orders.status","=",1)
      // ->where("users.wallet_amount",">",250)
     ->orderBy('orders.created_at','DESC')
      ->get();
    
          $response = [
              "response"=>200,
              'data'=>$data,
          ];
    

    return response($response, 200);
  }
  function getDataByUIdAndDateDeliveredSub($id)
  {
            $timeStamp= date("Y-m-d");
    $data = DB::table("order_user_assign")
    ->select(
      'order_user_assign.id as order_user_assign_id',
      'order_user_assign.user_id  as order__assign_user',
      "orders.id",
      'orders.order_type',
      'orders.order_amount',
      'orders.qty',
      'orders.selected_days_for_weekly',
      'orders.subscription_type',
      'orders.order_type',
      'orders.created_at',
      'orders.updated_at',
      'orders.order_number',
     'product.title',
      'images.image as product_image',
     'product.qty_text',
     'user_address.name',
     'user_address.s_phone',
     'user_address.flat_no',
     'user_address.apartment_name',
     'user_address.area',
     'user_address.city',
     'user_address.pincode',
     'users.wallet_amount',
     "subscribed_order_delivery.date as deliver_date"
    )  ->where('order_user_assign.user_id','=',$id)
    ->Join('subscribed_order_delivery','subscribed_order_delivery.order_id', '=','order_user_assign.order_id')

    ->where('subscribed_order_delivery.date','=',$timeStamp)
     ->Join('orders','orders.id', '=','order_user_assign.order_id')
         ->leftJoin('images', function ($join) {
       $join->on('images.table_id', '=', 'orders.product_id')
       ->where('images.table_name','=',"product")
       ->where('images.image_type','=',1);
       })
    ->Join('product','orders.product_id', '=','product.id')
    ->Join('users','users.id', '=','orders.user_id')
     ->Join('user_address','user_address.id', '=','orders.address_id')
     ->where("orders.subscription_type","!=",null)
     ->where("orders.order_status","=",0)
     ->where("orders.status","=",1)
      // ->where("users.wallet_amount",">",250)
     ->orderBy('orders.created_at','DESC')
      ->get();
    
          $response = [
              "response"=>200,
              'data'=>$data,
          ];
    

    return response($response, 200);
  }
  function getDataByUId($id)
  {
    $timeStamp= date("Y-m-d");
    $data = DB::table("order_user_assign")
    ->select(
      'order_user_assign.id as order_user_assign_id',
      'order_user_assign.user_id  as order__assign_user',
      "orders.id",
      'orders.order_type',
      'orders.order_amount',
      'orders.qty',
      'orders.selected_days_for_weekly',
      'orders.subscription_type',
      'orders.order_type',
      'orders.created_at',
      'orders.order_number',
      'orders.updated_at',
        'orders.start_date',
     'product.title',
      'images.image as product_image',
     'product.qty_text',
     'user_address.name',
     'user_address.s_phone',
     'user_address.flat_no',
     'user_address.apartment_name',
     'user_address.area',
     'user_address.city',
     'user_address.pincode',
     'users.wallet_amount',
     "user_holiday.date as user_holiday_date"
    )  ->where('order_user_assign.user_id','=',$id)
    
     ->Join('orders','orders.id', '=','order_user_assign.order_id')
         ->leftJoin('images', function ($join) {
       $join->on('images.table_id', '=', 'orders.product_id')
       ->where('images.table_name','=',"product")
       ->where('images.image_type','=',1);
       })
    ->Join('product','orders.product_id', '=','product.id')
    ->Join('users','users.id', '=','orders.user_id')
     ->Join('user_address','user_address.id', '=','orders.address_id')
     ->leftJoin('user_holiday', function ($join) use($timeStamp) {
      $join->on('user_holiday.user_id', '=', 'orders.user_id')
      ->where('user_holiday.date','=', $timeStamp);})
     ->where("orders.subscription_type","!=",null)
      ->where('orders.start_date','<=', $timeStamp)
     ->where("orders.order_status","=",0)
     ->where("orders.status","=",1)
      // ->where("users.wallet_amount",">",250)
     ->orderBy('orders.created_at','DESC')
      ->get();
    
          $response = [
              "response"=>200,
              'data'=>$data,
          ];
    
         
    return response($response, 200);
  }
 
    function deleteData(Request $request){
      
        $initialCheck=false;
        $validator = Validator::make(request()->all(), [
          'id'=>'required'
      ]);
      if ($validator->fails())
      $initialCheck=true;
      
        
        if ($initialCheck)
            return response (["response"=>400],400);
              else{
               try{
                  $timeStamp= date("Y-m-d H:i:s");
                  $dataModel= OrderAssignModelModel::where("id",$request->id)->first();
              
                               
             $qResponce= $dataModel->delete();
              if($qResponce)
                $response = [
                    "response"=>200,
                    'status'=>true,
                    'message' => "successfully",
          
                ];else 
                $response = [
                  "response"=>201,
                  'status'=>false,
                  'message' => "error",
        
              ];
              return response($response, 200);
            
            
           
                }
                catch(\Exception $e){
                  $response = [
                    "response"=>201,
                    'status'=>false,
                    'message' => "error",
                ];
                return response($response, 200);
                }
          
                   
              }
           
          
    }
    function getDataById($id)
    {      
    
            $data = DB::table("order_user_assign")
            ->select('order_user_assign.*')
            ->leftJoin('images', function ($join) {
              $join->on('images.table_id', '=', 'cat.id')
              ->where('images.table_name','=',"cat")
              ->where('images.image_type','=',1);
              })
              ->where('cat.id','=',$id)
              ->orderBy('cat.created_at','DESC')
              ->first();
              $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
    function getData()
    {      
    
            $data = DB::table("cat")
            ->select('cat.*',
            'images.id as image_id',
            'images.image'  
            )
            ->leftJoin('images', function ($join) {
              $join->on('images.table_id', '=', 'cat.id')
              ->where('images.table_name','=',"cat")
              ->where('images.image_type','=',1);
              })
              ->orderBy('cat.created_at','DESC')
              ->get();
              $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
    function updateDetails(Request $request){
        $initialCheck=false;
        $validator = Validator::make(request()->all(), [
          'id'=>'required'
      ]);
      if ($validator->fails())
      $initialCheck=true;
      
        
        if ($initialCheck)
            return response (["response"=>400],400);
              else{
               try{
                  $timeStamp= date("Y-m-d H:i:s");
                  $dataModel= OrderAssignModelModel::where("id",$request->id)->first();
            
                
                    $alreadyExists = OrderAssignModelModel::where('title', '=', $request->title)->where('id',"!=",$request->id)->first();
                  
  
               if ($alreadyExists == null)
                  {
                  if(isset($request->title ))
                  $dataModel->title = $request->title ;
           
                     $dataModel->updated_at=$timeStamp;
                
               
             $qResponce= $dataModel->save();
       
              if($qResponce)
           {   
      
            // $imageFile=isset($request->image)?$request->image:null;
            // $imageId=isset($request->image_id)?$request->image_id:null;
         
            // app('App\Http\Controllers\ImageCountController')->uploadImage($imageFile, "buses", $userDetailsModel->id,1,$imageId);
            // //1=profile_image  
            
            $response = [
                    "response"=>200,
                    'status'=>true,
                    'message' => "successfully",
          
                ];}
                else 
                $response = [
                  "response"=>201,
                  'status'=>false,
                  'message' => "error",
        
              ];
              return response($response, 200);
                 }
                 else {
                   $response = [
                     "response"=>201,
                     'status'=>false,
                     'message' => "title already exists"];
                     return response($response, 200);
                 }
         
            
           
                }
                catch(\Exception $e){
                  $response = [
                    "response"=>201,
                    'status'=>false,
                    'message' => "error $e",
                ];
                return response($response, 200);
                }
          
                   
              } 
              
        
        
          }
    function addData(Request $request)
    {
        
        $validator = Validator::make(request()->all(), [
          'user_id' => 'required',
          'order_id' => 'required'
    ]);
        
    if ($validator->fails())
      return response (["response"=>400],400);
        else{
        
               $alreadyExists = OrderAssignModelModel::where('order_id', '=', $request->order_id)->where('user_id', '=', $request->user_id)->first();
                if ($alreadyExists === null) {
                  try{
                    $timeStamp= date("Y-m-d H:i:s");
                    $dataModel=new OrderAssignModelModel;
                    $dataModel->order_id = $request->order_id;
                    $dataModel->user_id = $request->user_id;
                    $dataModel->created_at=$timeStamp;
                    $dataModel->updated_at=$timeStamp;
                    $qResponce= $dataModel->save();
                       if($qResponce){
                       $response = [
                             "response"=>200,
                             'status'=>true,
                             'message' => "successfully",
                             'id' => $dataModel->id
                         ];
                        }else 
                         $response = [
                           "response"=>201,
                           'status'=>false,
                           'message' => "error",
                 
                       ];
                       return response($response, 200);
                               
                  }catch(\Exception $e){
              
                    $response = [
                      "response"=>201,
                      'status'=>false,
                      'message' => "error",
              
                  ];
                  return response($response, 200);
                  }
                }
                
                else {
                  $response = [
                    "response"=>201,
                    'status'=>false,
                    'message' => "order already assigned to that user"];
                    return response($response, 200);
                }
      
       }
       
      }
               
}
