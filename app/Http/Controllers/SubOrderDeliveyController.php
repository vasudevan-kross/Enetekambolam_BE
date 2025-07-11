<?php

namespace App\Http\Controllers;

use App\Helpers\notificationHelper;
use Illuminate\Http\Request;
use App\Models\SubOderDeliveyModel;
use App\Models\User;
use App\Models\TransactionsModel;
use App\Models\OrderModel;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
//

class SubOrderDeliveyController extends Controller
{
  function getDataByOrderId($id)
  {
    $data = DB::table("subscribed_order_delivery")
      ->select(
        DB::raw('COALESCE(subscribed_order_delivery.entry_user_id, subscribed_order_delivery.executive_id) as entry_user_id'), // Replace entry_user_id with executive_id if null
        DB::raw('COALESCE(users.name, delivery_executive.name) as name'),
        DB::raw('COALESCE(users.phone, delivery_executive.phn_no1) as phone'),
        DB::raw('COALESCE(users.email, delivery_executive.email) as email'),
        'delivery_executive.executive_id',
        'subscribed_order_delivery.date',
        'subscribed_order_delivery.created_at',
        'subscribed_order_delivery.payment_mode',
        'subscribed_order_delivery.delivery_notes'
      )
      ->leftJoin('users', 'users.id', '=', 'subscribed_order_delivery.entry_user_id')
      ->leftJoin('delivery_executive', 'delivery_executive.id', '=', 'subscribed_order_delivery.executive_id')
      ->where("subscribed_order_delivery.order_id", "=", $id)
      ->orderBy('subscribed_order_delivery.date', 'DESC')
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  function addDataWeekely(Request $request)
  {

    $validator = Validator::make(request()->all(), [
      'entry_user_id' => 'required',
      'order_id' => 'required',
      'qty' => 'required'
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      $todayDate = date("Y-m-d");
      $alreadyExists = SubOderDeliveyModel::where('order_id', '=', $request->order_id)->where('date', '=', $todayDate)->first();
      if ($alreadyExists === null) {

        try {
          $orderData = DB::table("orders")
            ->select(
              'orders.user_id',
              'orders.order_amount',
              'orders.order_type',
              'orders.status',
              'users.wallet_amount',
              'product.title'
            )
            ->where("orders.id", "=", $request->order_id)
            ->Join('users', 'users.id', '=', 'orders.user_id')
            ->Join('product', 'orders.product_id', '=', 'product.id')
            ->first();
          // echo  $orderData->wallet_amount;
          // if($orderData->order_type==1){
          //   if( $orderData->wallet_amount==null){
          //     $response = [
          //       "response"=>201,
          //       'status'=>false,
          //       'message' => "No amount in user wallet",

          //   ];
          //   return response($response, 200);

          //   } else if($orderData->wallet_amount!=null){

          //     if($orderData->wallet_amount==0){
          //       $response = [
          //         "response"=>201,
          //         'status'=>false,
          //         'message' => "No amount in user wallet",

          //     ];
          //     return response($response, 200);
          //     }
          //     else{
          //       $dataModelUser= User::where("id",$orderData->user_id)->first();
          //       $oldAmount=$dataModelUser->wallet_amount;
          //       $finalOrderAmount=($orderData->order_amount)*($request->qty);
          //       $checkAmount=$oldAmount-$finalOrderAmount;

          //       if($oldAmount>=$finalOrderAmount){
          //           $newAmount=$oldAmount-$finalOrderAmount;
          //           $dataModelUser->wallet_amount  = $newAmount;
          //           $dataModelUser->save();

          //            $timeStamp= date("Y-m-d H:i:s");
          //                     $dataModel=new SubOderDeliveyModel;
          //                     $dataModel->order_id = $request->order_id;
          //                     $dataModel->entry_user_id = $request->entry_user_id;
          //                     $dataModel->date=$todayDate;
          //                     $dataModel->payment_mode = 1;
          //                     $dataModel->created_at=$timeStamp;
          //                     $dataModel->updated_at=$timeStamp;
          //                     $qResponce= $dataModel->save();
          //                       if($qResponce){
          //                         $timeStamp= date("Y-m-d H:i:s");
          //                         $dataModelTxn=new TransactionsModel;
          //                         $dataModelTxn->user_id  = $orderData->user_id;
          //                         $dataModelTxn->order_id   = $request->order_id;
          //                         $dataModelTxn->amount = $finalOrderAmount;
          //                         $dataModelTxn->type  = 2;
          //                         $productName=$orderData->title;
          //                         $dataModelTxn->description  = "Amount debited from wallet - $productName";

          //                         $dataModelTxn->created_at=$timeStamp;
          //                         $dataModelTxn->updated_at=$timeStamp;

          //                         $qResponce= $dataModelTxn->save();
          //                       $response = [
          //                             "response"=>200,
          //                             'status'=>true,
          //                             'message' => "successfully",
          //                             'id' => $dataModel->id
          //                         ];
          //                         }else 
          //                         $response = [
          //                           "response"=>201,
          //                           'status'=>false,
          //                           'message' => "error",

          //                       ];
          //                       return response($response, 200);



          //       }else{
          //         $response = [
          //           "response"=>201,
          //           'status'=>false,
          //           'message' => "Less wallet amount",

          //       ];
          //       return response($response, 200);
          //       }

          //     }
          //   }

          // }
          // else
          if ($orderData->order_type == 1 || $orderData->order_type == 2 || $orderData->order_type == 3) {
            $timeStamp = date("Y-m-d H:i:s");
            $dataModel = new SubOderDeliveyModel;
            $dataModel->order_id = $request->order_id;
            $dataModel->entry_user_id = $request->entry_user_id;
            $dataModel->delivery_notes = $request->delivery_notes;
            $dataModel->date = $todayDate;
            $dataModel->created_at = $timeStamp;
            $dataModel->updated_at = $timeStamp;
            $qResponce = $dataModel->save();
            if ($qResponce) {

              $response = [
                "response" => 200,
                'status' => true,
                'message' => "successfully",
                'id' => $dataModel->id
              ];
            } else
              $response = [
                "response" => 201,
                'status' => false,
                'message' => "error",

              ];
            try {
              $notificationResponse = notificationHelper::subscriptionDeliveredNotify($request->order_id, $dataModel->order_number, $dataModel->user_id);
            } catch (Exception $e) {
              // Log the error but do not affect the order processing
              Log::error("Delivery Notification Error: " . $e->getMessage());
            }
            return response($response, 200);
          } else {
            $response = [
              "response" => 201,
              'status' => false,
              'message' => "error",

            ];
            return response($response, 200);
          }
        } catch (\Exception $e) {

          $response = [
            "response" => 201,
            'status' => false,
            'message' => "error",

          ];
          return response($response, 200);
        }
      } else {
        $response = [
          "response" => 201,
          'status' => false,
          'message' => "Today delivery is already done for this order"
        ];
        return response($response, 200);
      }
    }
  }
  function addDataWeekelyManually(Request $request)
  {

    $validator = Validator::make(request()->all(), [
      'entry_user_id' => 'required',
      'order_id' => 'required',
      'qty' => 'required',
      'date' => 'required'
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      $todayDate = $request->date;
      $alreadyExists = SubOderDeliveyModel::where('order_id', '=', $request->order_id)->where('date', '=', $todayDate)->first();
      if ($alreadyExists === null) {
        //       $response = [
        //             "response"=>200,
        //             'status'=>true,
        //             'message' => "successfullx"

        //         ];
        //   return response($response, 200);

        try {
          $orderData = DB::table("orders")
            ->select(
              'orders.user_id',
              'orders.order_amount',
              'orders.order_type',
              'users.wallet_amount',
              'product.title'
            )
            ->where("orders.id", "=", $request->order_id)
            ->Join('users', 'users.id', '=', 'orders.user_id')
            ->Join('product', 'orders.product_id', '=', 'product.id')
            ->first();
          // echo  $orderData->wallet_amount;
          if ($orderData->order_type == 1) {
            if ($orderData->wallet_amount == null) {
              $response = [
                "response" => 201,
                'status' => false,
                'message' => "No amount in user wallet",

              ];
              return response($response, 200);
            } else if ($orderData->wallet_amount != null) {

              if ($orderData->wallet_amount == 0) {
                $response = [
                  "response" => 201,
                  'status' => false,
                  'message' => "No amount in user wallet",

                ];
                return response($response, 200);
              } else {
                $dataModelUser = User::where("id", $orderData->user_id)->first();
                $oldAmount = $dataModelUser->wallet_amount;
                $finalOrderAmount = ($orderData->order_amount) * ($request->qty);
                $checkAmount = $oldAmount - $finalOrderAmount;

                if ($oldAmount >= $finalOrderAmount) {
                  $newAmount = $oldAmount - $finalOrderAmount;
                  $dataModelUser->wallet_amount  = $newAmount;
                  $dataModelUser->save();

                  $timeStamp = date("Y-m-d H:i:s");
                  $dataModel = new SubOderDeliveyModel;
                  $dataModel->order_id = $request->order_id;
                  $dataModel->entry_user_id = $request->entry_user_id;
                  $dataModel->date = $todayDate;
                  $dataModel->payment_mode = 1;
                  $dataModel->created_at = $timeStamp;
                  $dataModel->updated_at = $timeStamp;
                  $qResponce = $dataModel->save();
                  if ($qResponce) {
                    $timeStamp = date("Y-m-d H:i:s");
                    $dataModelTxn = new TransactionsModel;
                    $dataModelTxn->user_id  = $orderData->user_id;
                    $dataModelTxn->order_id   = $request->order_id;
                    $dataModelTxn->amount = $finalOrderAmount;
                    $dataModelTxn->type  = 2;
                    $productName = $orderData->title;
                    $dataModelTxn->description  = "Amount debited from wallet - $productName";

                    $dataModelTxn->created_at = $timeStamp;
                    $dataModelTxn->updated_at = $timeStamp;

                    $qResponce = $dataModelTxn->save();
                    $response = [
                      "response" => 200,
                      'status' => true,
                      'message' => "successfully",
                      'id' => $dataModel->id
                    ];
                  } else
                    $response = [
                      "response" => 201,
                      'status' => false,
                      'message' => "error",

                    ];
                  return response($response, 200);
                } else {
                  $response = [
                    "response" => 201,
                    'status' => false,
                    'message' => "Less wallet amount",

                  ];
                  return response($response, 200);
                }
              }
            }
          } else if ($orderData->order_type == 2) {
            $timeStamp = date("Y-m-d H:i:s");
            $dataModel = new SubOderDeliveyModel;
            $dataModel->order_id = $request->order_id;
            $dataModel->entry_user_id = $request->entry_user_id;
            $dataModel->date = $todayDate;
            $dataModel->created_at = $timeStamp;
            $dataModel->updated_at = $timeStamp;
            $qResponce = $dataModel->save();
            if ($qResponce) {

              $response = [
                "response" => 200,
                'status' => true,
                'message' => "successfully",
                'id' => $dataModel->id
              ];
            } else
              $response = [
                "response" => 201,
                'status' => false,
                'message' => "error",

              ];
            return response($response, 200);
          } else {
            $response = [
              "response" => 201,
              'status' => false,
              'message' => "error",

            ];
            return response($response, 200);
          }
        } catch (\Exception $e) {

          $response = [
            "response" => 201,
            'status' => false,
            'message' => "error",

          ];
          return response($response, 200);
        }
      } else {
        $response = [
          "response" => 201,
          'status' => false,
          'message' => "Today delivery is already done for this order"
        ];
        return response($response, 200);
      }
    }
  }

  public function addOrderDeliveryData(Request $request)
  {
    $request->validate([
      'entry_user_id' => 'required|integer',
      'order_id' => 'required|integer',
      'executive_id' => 'required|integer',
      'assigned_date' => 'required|date_format:d-m-Y',
      'payment_mode' => 'nullable|integer',
      'delivery_notes' => 'nullable|string',
    ]);

    try {
      $assignedDate = Carbon::createFromFormat('d-m-Y', $request->assigned_date)->format('Y-m-d');

      // ✅ Check if delivery already exists for the order on that date
      $existingDelivery = DB::table('subscribed_order_delivery')
        ->where('order_id', $request->order_id)
        ->whereDate('date', $assignedDate)
        ->first();

      if ($existingDelivery) {
        return response()->json([
          'response' => 201,
          'message' => 'Delivery already assigned for this order on the selected date.',
        ]);
      }

      // ✅ Fetch order and executive
      $order = DB::table('orders')->where('id', $request->order_id)->first();
      if (!$order) {
        return response()->json([
          'response' => 404,
          'message' => 'Order not found',
        ]);
      }

      $executive = DB::table('delivery_executive')->where('id', $request->executive_id)->first();
      if (!$executive) {
        return response()->json([
          'response' => 404,
          'message' => 'Delivery Executive not found',
        ]);
      }

      $existingAssignment = DB::table('delivery_executive_orders')
        ->where('order_id', $request->order_id)
        ->whereDate('assigned_date', $assignedDate)
        ->first();

      if ($existingAssignment) {
        DB::table('delivery_executive_orders')
          ->where('order_id', $request->order_id)
          ->update([
            'delivery_executive_id' => $request->executive_id,
            'executive_number' => $executive->executive_id,
            'order_number' => $order->order_number,
            'assigned_date' => $assignedDate,
            'updated_at' => now(),
            'is_admin_reassigned' => 0,
          ]);
      } else {
        DB::table('delivery_executive_orders')->insert([
          'delivery_executive_id' => $request->executive_id,
          'executive_number' => $executive->executive_id,
          'order_id' => $request->order_id,
          'order_number' => $order->order_number,
          'assigned_date' => $assignedDate,
          'is_reassign_requested' => 0,
          'is_conform' => 1,
          'is_admin_reassigned' => 0,
          'created_at' => now(),
          'updated_at' => now(),
        ]);
      }

      // ✅ Insert into subscribed_order_delivery
      DB::table('subscribed_order_delivery')->insert([
        'order_id' => $request->order_id,
        'date' => $assignedDate,
        'payment_mode' => $request->payment_mode ?? 1,
        'delivery_notes' => $request->delivery_notes ?? null,
        'executive_id' => $request->executive_id,
        'created_at' => now(),
        'updated_at' => now(),
      ]);

      return response()->json([
        'response' => 200,
        'message' => 'Delivery assigned successfully',
      ]);
    } catch (Exception $e) {
      return response()->json([
        'response' => 500,
        'message' => 'Something went wrong',
        'error' => $e->getMessage(),
      ], 500);
    }
  }


  function addData(Request $request)
  {

    $validator = Validator::make(request()->all(), [
      'entry_user_id' => 'required',
      'order_id' => 'required'
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      $todayDate = date("Y-m-d");
      $alreadyExists = SubOderDeliveyModel::where('order_id', '=', $request->order_id)->where('date', '=', $todayDate)->first();
      if ($alreadyExists === null) {

        try {
          $orderData = DB::table("orders")
            ->select(
              'orders.user_id',
              'orders.order_amount',
              'orders.order_type',
              'orders.status',
              'users.wallet_amount',
              'product.title'
            )
            ->where("orders.id", "=", $request->order_id)
            ->Join('users', 'users.id', '=', 'orders.user_id')
            ->Join('product', 'orders.product_id', '=', 'product.id')
            ->first();
          // echo  $orderData->wallet_amount;
          // if($orderData->order_type==1){
          //   if( $orderData->wallet_amount==null){
          //     $response = [
          //       "response"=>201,
          //       'status'=>false,
          //       'message' => "No amount in user wallet",

          //   ];
          //   return response($response, 200);

          //   } else if($orderData->wallet_amount!=null){

          //     if($orderData->wallet_amount==0){
          //       $response = [
          //         "response"=>201,
          //         'status'=>false,
          //         'message' => "No amount in user wallet",

          //     ];
          //     return response($response, 200);
          //     }
          //     else{
          //       $dataModelUser= User::where("id",$orderData->user_id)->first();
          //       $oldAmount=$dataModelUser->wallet_amount;
          //       $checkAmount=$oldAmount-$orderData->order_amount;

          //       if($oldAmount>=$orderData->order_amount){
          //           $newAmount=$oldAmount-$orderData->order_amount;
          //           $dataModelUser->wallet_amount  = $newAmount;
          //           $dataModelUser->save();

          //            $timeStamp= date("Y-m-d H:i:s");
          //                     $dataModel=new SubOderDeliveyModel;
          //                     $dataModel->order_id = $request->order_id;
          //                     $dataModel->entry_user_id = $request->entry_user_id;
          //                     $dataModel->date=$todayDate;
          //                     $dataModel->payment_mode = 1;
          //                     $dataModel->created_at=$timeStamp;
          //                     $dataModel->updated_at=$timeStamp;
          //                     $qResponce= $dataModel->save();
          //                       if($qResponce){
          //                         $timeStamp= date("Y-m-d H:i:s");
          //                         $dataModelTxn=new TransactionsModel;
          //                         $dataModelTxn->user_id  = $orderData->user_id;
          //                         $dataModelTxn->order_id   = $request->order_id;
          //                         $dataModelTxn->amount = $orderData->order_amount;
          //                         $dataModelTxn->type  = 2;
          //                         $productName=$orderData->title;
          //                         $dataModelTxn->description  = "Amount debited from wallet - $productName";

          //                         $dataModelTxn->created_at=$timeStamp;
          //                         $dataModelTxn->updated_at=$timeStamp;

          //                         $qResponce= $dataModelTxn->save();
          //                       $response = [
          //                             "response"=>200,
          //                             'status'=>true,
          //                             'message' => "successfully",
          //                             'id' => $dataModel->id
          //                         ];
          //                         }else 
          //                         $response = [
          //                           "response"=>201,
          //                           'status'=>false,
          //                           'message' => "error",

          //                       ];
          //                       return response($response, 200);



          //       }else{
          //         $response = [
          //           "response"=>201,
          //           'status'=>false,
          //           'message' => "Less wallet amount",

          //       ];
          //       return response($response, 200);
          //       }

          //     }
          //   }

          // }else  
          if ($orderData->order_type == 1 || $orderData->order_type == 2) {
            $timeStamp = date("Y-m-d H:i:s");
            $dataModel = new SubOderDeliveyModel;
            $dataModel->order_id = $request->order_id;
            $dataModel->entry_user_id = $request->entry_user_id;
            $dataModel->delivery_notes = $request->delivery_notes;
            $dataModel->date = $todayDate;
            $dataModel->created_at = $timeStamp;
            $dataModel->updated_at = $timeStamp;
            $qResponce = $dataModel->save();
            if ($qResponce) {

              $response = [
                "response" => 200,
                'status' => true,
                'message' => "successfully",
                'id' => $dataModel->id
              ];
            } else
              $response = [
                "response" => 201,
                'status' => false,
                'message' => "error",

              ];
            try {
              $notificationResponse = notificationHelper::subscriptionDeliveredNotify($request->order_id, $dataModel->order_number, $dataModel->user_id);
            } catch (Exception $e) {
              // Log the error but do not affect the order processing
              Log::error("Delivery Notification Error: " . $e->getMessage());
            }
            return response($response, 200);
          } else {
            $response = [
              "response" => 201,
              'status' => false,
              'message' => "error",

            ];
            return response($response, 200);
          }
        } catch (Exception $e) {

          $response = [
            "response" => 201,
            'status' => false,
            'message' => "error",

          ];
          return response($response, 200);
        }
      } else {
        $response = [
          "response" => 201,
          'status' => false,
          'message' => "Today delivery is already done for this order"
        ];
        return response($response, 200);
      }
    }
  }

  function addDataManually(Request $request)
  {

    $validator = Validator::make(request()->all(), [
      'entry_user_id' => 'required',
      'order_id' => 'required',
      'date' => 'required'
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {

      $todayDate = $request->date;
      $alreadyExists = SubOderDeliveyModel::where('order_id', '=', $request->order_id)->where('date', '=', $todayDate)->first();
      if ($alreadyExists === null) {
        //      $response = [
        //             "response"=>200,
        //             'status'=>true,
        //             'message' => "successfullz"

        //         ];
        //   return response($response, 200);

        try {
          $orderData = DB::table("orders")
            ->select(
              'orders.user_id',
              'orders.order_amount',
              'orders.order_type',
              'users.wallet_amount',
              'product.title'
            )
            ->where("orders.id", "=", $request->order_id)
            ->Join('users', 'users.id', '=', 'orders.user_id')
            ->Join('product', 'orders.product_id', '=', 'product.id')
            ->first();
          // echo  $orderData->wallet_amount;
          if ($orderData->order_type == 1) {
            if ($orderData->wallet_amount == null) {
              $response = [
                "response" => 201,
                'status' => false,
                'message' => "No amount in user wallet",

              ];
              return response($response, 200);
            } else if ($orderData->wallet_amount != null) {

              if ($orderData->wallet_amount == 0) {
                $response = [
                  "response" => 201,
                  'status' => false,
                  'message' => "No amount in user wallet",

                ];
                return response($response, 200);
              } else {
                $dataModelUser = User::where("id", $orderData->user_id)->first();
                $oldAmount = $dataModelUser->wallet_amount;
                $checkAmount = $oldAmount - $orderData->order_amount;

                if ($oldAmount >= $orderData->order_amount) {
                  $newAmount = $oldAmount - $orderData->order_amount;
                  $dataModelUser->wallet_amount  = $newAmount;
                  $dataModelUser->save();

                  $timeStamp = date("Y-m-d H:i:s");
                  $dataModel = new SubOderDeliveyModel;
                  $dataModel->order_id = $request->order_id;
                  $dataModel->entry_user_id = $request->entry_user_id;
                  $dataModel->date = $todayDate;
                  $dataModel->payment_mode = 1;
                  $dataModel->created_at = $timeStamp;
                  $dataModel->updated_at = $timeStamp;
                  $qResponce = $dataModel->save();
                  if ($qResponce) {
                    $timeStamp = date("Y-m-d H:i:s");
                    $dataModelTxn = new TransactionsModel;
                    $dataModelTxn->user_id  = $orderData->user_id;
                    $dataModelTxn->order_id   = $request->order_id;
                    $dataModelTxn->amount = $orderData->order_amount;
                    $dataModelTxn->type  = 2;
                    $productName = $orderData->title;
                    $dataModelTxn->description  = "Amount debited from wallet - $productName";

                    $dataModelTxn->created_at = $timeStamp;
                    $dataModelTxn->updated_at = $timeStamp;

                    $qResponce = $dataModelTxn->save();
                    $response = [
                      "response" => 200,
                      'status' => true,
                      'message' => "successfully",
                      'id' => $dataModel->id
                    ];
                  } else
                    $response = [
                      "response" => 201,
                      'status' => false,
                      'message' => "error",

                    ];
                  return response($response, 200);
                } else {
                  $response = [
                    "response" => 201,
                    'status' => false,
                    'message' => "Less wallet amount",

                  ];
                  return response($response, 200);
                }
              }
            }
          } else  if ($orderData->order_type == 2) {
            $timeStamp = date("Y-m-d H:i:s");
            $dataModel = new SubOderDeliveyModel;
            $dataModel->order_id = $request->order_id;
            $dataModel->entry_user_id = $request->entry_user_id;
            $dataModel->date = $todayDate;
            $dataModel->created_at = $timeStamp;
            $dataModel->updated_at = $timeStamp;
            $qResponce = $dataModel->save();
            if ($qResponce) {

              $response = [
                "response" => 200,
                'status' => true,
                'message' => "successfully",
                'id' => $dataModel->id
              ];
            } else
              $response = [
                "response" => 201,
                'status' => false,
                'message' => "error",

              ];
            return response($response, 200);
          } else {
            $response = [
              "response" => 201,
              'status' => false,
              'message' => "error",

            ];
            return response($response, 200);
          }
        } catch (Exception $e) {

          $response = [
            "response" => 201,
            'status' => false,
            'message' => "error",

          ];
          return response($response, 200);
        }
      } else {
        $response = [
          "response" => 201,
          'status' => false,
          'message' => "Today delivery is already done for this order"
        ];
        return response($response, 200);
      }
    }
  }


  function addNormalOrderData(Request $request)
  {

    $validator = Validator::make(request()->all(), [
      'entry_user_id' => 'required',
      'order_id' => 'required',
      'payment_mode' => 'required'
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      $todayDate = date("Y-m-d");
      $alreadyExists = SubOderDeliveyModel::where('order_id', '=', $request->order_id)->first();
      if ($alreadyExists === null) {

        try {

          $timeStamp = date("Y-m-d H:i:s");
          $dataModel = new SubOderDeliveyModel;
          $dataModel->order_id = $request->order_id;
          $dataModel->entry_user_id = $request->entry_user_id;
          $dataModel->date = $todayDate;
          $dataModel->payment_mode = $request->payment_mode;
          $dataModel->delivery_notes = $request->delivery_notes;
          $dataModel->created_at = $timeStamp;
          $dataModel->updated_at = $timeStamp;
          $qResponce = $dataModel->save();
          if ($qResponce) {
            $dataOrderModel = OrderModel::where("id", $request->order_id)->first();

            if ($request->payment_mode == 2) {
              $dataModelTxn = new TransactionsModel;
              $dataModelTxn->user_id  = $dataOrderModel->user_id;
              $dataModelTxn->amount = $dataOrderModel->order_amount;
              $dataModelTxn->type  = 2;
              $dataModelTxn->payment_mode = 2;

              $dataModelTxn->description  = "Cash Payment";

              $dataModelTxn->created_at = $timeStamp;
              $dataModelTxn->updated_at = $timeStamp;

              $dataModelTxn->save();
              $dataOrderModel->trasation_id = $dataModelTxn->id;
            }


            $dataOrderModel->delivery_status = 1;
            $dataOrderModel->save();
            try {
              if ($dataOrderModel->subscription_type) {
                $notificationResponse = notificationHelper::subscriptionDeliveredNotify(
                  $request->order_id,
                  $dataOrderModel->order_number,
                  $dataOrderModel->user_id
                );
              } else {
                $notificationResponse = notificationHelper::buyOnceDeliveredNotify(
                  $request->order_id,
                  $dataOrderModel->order_number,
                  $dataOrderModel->user_id
                );
              }
            } catch (Exception $e) {
              // Log the error but do not affect the order processing
              Log::error("Delivery Notification Error: " . $e->getMessage());
            }

            $response = [
              "response" => 200,
              'status' => true,
              'message' => "successfully",
              'id' => $dataModel->id
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
      } else {
        $response = [
          "response" => 201,
          'status' => false,
          'message' => "Product already delivered"
        ];
        return response($response, 200);
      }
    }
  }
}
