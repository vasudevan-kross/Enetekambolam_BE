<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentGetWayModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



class PaymentGetwayController extends Controller
{
  function updateData(Request $request)
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
      $timeStamp = date("Y-m-d H:i:s");
      try {
        $dataModel = PaymentGetWayModel::where("id", $request->id)->first();
        if (isset($request->key_id))
          $dataModel->key_id = $request->key_id;
        if (isset($request->secret_id))
          $dataModel->secret_id = $request->secret_id;
        $dataModel->updated_at = $timeStamp;
        if (isset($request->active)) {
          $dataModel->active = $request->active;
        }


        $qResponce = $dataModel->save();
        if (isset($request->active)) {
          if ($request->active == 1) {

            PaymentGetWayModel::where('id', '!=', $request->id)->update(['active' => 0]);
          }
        }
        if ($qResponce)
          $response = [
            "response" => 200,
            'status' => true,
            'message' => "successfully",

          ];
        else
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
  function getDataById($id)
  {

    $data = DB::table("payment_gateway")
      ->select('payment_gateway.*')
      ->orderBy("payment_gateway.created_at", "ASC")
      ->where("payment_gateway.id", "=", $id)
      ->first();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }
  function getDataAcive()
  {

    $data = DB::table("payment_gateway")
      ->select('payment_gateway.*')
      ->orderBy("payment_gateway.created_at", "ASC")
      ->where("payment_gateway.active", "=", 1)
      ->first();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }
  function getDataAllData()
  {

    $data = DB::table("payment_gateway")
      ->select('payment_gateway.*')
      ->orderBy("payment_gateway.created_at", "ASC")
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }
}
