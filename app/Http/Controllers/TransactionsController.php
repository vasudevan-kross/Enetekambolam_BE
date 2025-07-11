<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TransactionsModel;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class TransactionsController extends Controller
{

  function addDirectData(Request $request)
  {

    $validator = Validator::make(request()->all(), [
      'user_id' => 'required',
      'amount' => 'required',
      'type' => 'required',
      'description' => 'required'

    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {

      try {
        $timeStamp = date("Y-m-d H:i:s");
        $dataModel = new TransactionsModel;
        $dataModel->user_id  = $request->user_id;
        if (isset($request->payment_id)) {
          $dataModel->payment_id  = $request->payment_id;
        } else {
          $dataModel->payment_id = $this->generatePaymentId();
        }
        $dataModel->amount = $request->amount;
        $dataModel->type  = $request->type;
        $dataModel->description  = $request->description;

        if (isset($request->order_id)) {
          $dataModel->order_id = $request->order_id;
        }

        if (isset($request->payment_mode)) {
          $dataModel->payment_mode  = $request->payment_mode;
        }

        $dataModel->created_at = $timeStamp;
        $dataModel->updated_at = $timeStamp;
        $qResponce = $dataModel->save();
        if ($qResponce) {

          $response = [
            "response" => 200,
            'status' => true,
            'message' => "successfully",
            "id" => $dataModel->id

          ];
        } else
          $response = [
            "response" => 201,
            'status' => false,
            'message' => "error",

          ];
        return response($response, 200);
      } catch (\Exception $e) {

        $response = [
          "response" => 201,
          'status' => false,
          'message' => "error",

        ];
        return response($response, 200);
      }
    }
  }
  function getData()
  {
    $data = DB::table("transactions")
      ->select(
        'transactions.*',
        'users.name',
        'users.phone',
        'orders.order_number' // Add order_number from orders table
      )
      ->join('users', 'users.id', '=', 'transactions.user_id')
      ->leftJoin('orders', 'orders.id', '=', 'transactions.order_id') // Left join with orders table
      ->orderBy('transactions.created_at', 'DESC')
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }


  function getDataByDateRange($startDate, $endDate)
  {
    // Convert to correct date format
    $startDate = Carbon::parse($startDate)->startOfDay()->toDateTimeString();
    $endDate = Carbon::parse($endDate)->endOfDay()->toDateTimeString();

    $data = DB::table("transactions")
      ->select(
        'transactions.*',
        'users.name',
        'users.phone',
        'orders.order_number' // <-- Add this line to select order_number
      )
      ->join('users', 'users.id', '=', 'transactions.user_id')
      ->leftJoin('orders', 'orders.id', '=', 'transactions.order_id') // <-- Join with orders
      ->whereBetween('transactions.created_at', [$startDate, $endDate])
      ->orderBy('transactions.created_at', 'DESC')
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }


  // function getDataByDateRange($startDate, $endDate)
  // {
  //     $data = DB::table("transactions")
  //         ->select('transactions.*',
  //             "users.name",
  //             "users.phone")
  //         ->join('users', 'users.id', '=', 'transactions.user_id')
  //         ->whereBetween('transactions.created_at', [$startDate, $endDate])
  //         ->orderBy('transactions.created_at', 'DESC')
  //         ->get();

  //     $response = [
  //         "response" => 200,
  //         'data' => $data,
  //     ];

  //     return response($response, 200);
  // }

  function getDataById($id)
  {

    $data = DB::table("transactions")
      ->select(
        'transactions.*',
        "users.name",
        "users.phone"
      )
      ->join('users', 'users.id', '=', 'transactions.user_id')
      ->where("transactions.id", "=", $id)
      ->orderBy('transactions.created_at', 'DESC')
      ->first();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }
  // function getDataByUId($id)
  // {

  //   $data = DB::table("transactions")
  //     ->select('transactions.*')
  //     ->where("user_id", "=", $id)
  //     ->orderBy('transactions.created_at', 'DESC')
  //     ->get();

  //   $response = [
  //     "response" => 200,
  //     'data' => $data,
  //   ];

  //   return response($response, 200);
  // }

  function getDataByUId($id)
  {
    // Get data from the transactions table and left join with the orders table to get order_number
    $data = DB::table('transactions')
      ->leftJoin('orders', 'transactions.order_id', '=', 'orders.id')  // Use leftJoin to include transactions even without matching orders
      ->select('transactions.*', 'orders.order_number')  // Select all columns from transactions and order_number from orders
      ->where('transactions.user_id', '=', $id)  // Filter by user_id
      ->orderBy('transactions.created_at', 'DESC')  // Order by creation date in descending order
      ->get();

    // Prepare the response
    $response = [
      "response" => 200,
      "data" => $data,
    ];

    return response($response, 200);  // Return response with data and status 200
  }


  // function getDataByOrderId($id)
  // {

  //   $data = DB::table("transactions")
  //   ->select('transactions.*')
  //   ->join('orders','orders.trasation_id','=','transactions.id')
  //    ->where("orders.id","=",$id)
  //    ->orderBy('transactions.created_at','DESC')
  //     ->get();

  //         $response = [
  //             "response"=>200,
  //             'data'=>$data,
  //         ];

  //   return response($response, 200);
  // }

  function getDataByOrderId($id)
  {
    // Use distinct to get unique transactions by order ID
    $data = DB::table('transactions')
      ->select('transactions.*')
      ->distinct()
      ->leftJoin('orders', 'orders.trasation_id', '=', 'transactions.id')
      ->where('transactions.order_id', '=', $id) // Filter by order ID in transactions
      ->orderBy('transactions.created_at', 'DESC')
      ->get();

    // Prepare the response
    $response = [
      'response' => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }


  function getDataBySubOrderId($id)
  {

    $data = DB::table("transactions")
      ->select('transactions.*')
      ->where("transactions.order_id", "=", $id)
      ->orderBy('transactions.created_at', 'DESC')
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  // Helper function to generate payment_id
  private function generatePaymentId()
  {
    return 'txn_' . date('YmdHis'); // Format: txn_YYYYMMDDHHMMSS
  }
  function addData(Request $request)
  {

    $validator = Validator::make($request->all(), [
      'user_id' => 'required',
      'amount' => 'required',
      'type' => 'required',
      'description' => 'required'

    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      try {

        $timeStamp = date("Y-m-d H:i:s");
        $dataModel = new TransactionsModel;
        $dataModel->user_id  = $request->user_id;
        if (isset($request->payment_id)) {
          $dataModel->payment_id  = $request->payment_id;
        } else {
          $dataModel->payment_id = $this->generatePaymentId();
        }
        if (isset($request->order_id)) {
          $dataModel->order_id = $request->order_id;
        }
        $dataModel->amount = $request->amount;
        $dataModel->type  = $request->type;
        $dataModel->description  = $request->description;

        $dataModel->created_at = $timeStamp;
        $dataModel->updated_at = $timeStamp;

        $qResponce = $dataModel->save();
        if ($qResponce) {

          if (isset($request->type)) {

            $dataModelUser = User::where("id", $request->user_id)->first();

            if ($dataModelUser) {
              if ($request->type == 1 || $request->type == 3) {

                if ($dataModelUser->wallet_amount == null) {
                  $dataModelUser->wallet_amount  = $request->amount;
                  $dataModelUser->save();
                } else if ($dataModelUser->wallet_amount != null) {

                  $oldAmount = $dataModelUser->wallet_amount;
                  $newAmount = $oldAmount + $request->amount;
                  $dataModelUser->wallet_amount  = $newAmount;
                  $dataModelUser->save();

                  // echo $newAmount;
                  // $dataModelUser->walle

                }
              } else  if ($request->type == 2) {

                if ($dataModelUser->wallet_amount != null) {

                  $oldAmount = $dataModelUser->wallet_amount;
                  if ($oldAmount >= $request->amount) {
                    $newAmount = $oldAmount - $request->amount;
                    $dataModelUser->wallet_amount  = $newAmount;
                    $dataModelUser->save();
                  }
                }
              }
            }

            $response = [
              "response" => 200,
              'status' => true,
              'message' => "successfully",
              'id' => $dataModel->id,

            ];
          } else
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
    }
  }
}
