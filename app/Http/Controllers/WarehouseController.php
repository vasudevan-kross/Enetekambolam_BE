<?php

namespace App\Http\Controllers;

use App\Models\WarehouseModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ImageModel;
use App\Models\ProductModel;
use App\Http\Controllers\WebAppSettingsController;
use App\Models\User;

class WarehouseController extends Controller
{
  function addData(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'uid' => 'required',
      'warehouse_name' => 'required',
      'email' => 'required',
      'phone_no' => 'required',
      'poc_name' => 'required',
      'poc_ph_no' => 'required',
      'poc_email' => 'required',
      'fssai' => 'required',
      'gst_no' => 'required',
      'billing_address' => 'required',
      'country' => 'required',
      'state' => 'required',
      'district' => 'required',
      'pincode' => 'required',
      'address' => 'required',
      'latitude' => 'required',
      'longitude' => 'required',
      'service_city' => 'required'
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      $timeStamp = date("Y-m-d H:i:s");
      $dataModel = new WarehouseModel();
      $dataModel->uid  = $request->uid;
      $dataModel->warehouse_name  = $request->warehouse_name;
      $dataModel->email  = $request->email;
      $dataModel->phone_no  = $request->phone_no;
      $dataModel->poc_name  = $request->poc_name;
      $dataModel->poc_ph_no  = $request->poc_ph_no;
      $dataModel->poc_email  = $request->poc_email;
      $dataModel->fssai  = $request->fssai;
      $dataModel->gst_no  = $request->gst_no;
      $dataModel->billing_address  = $request->billing_address;
      $dataModel->country  = $request->country;
      $dataModel->state  = $request->state;
      $dataModel->district  = $request->district;
      $dataModel->pincode  = $request->pincode;
      $dataModel->address  = $request->address;
      $dataModel->latitude  = $request->latitude;
      $dataModel->longitude  = $request->longitude;
      $dataModel->service_city  = $request->service_city;
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
      } else {
        $response = [
          "response" => 201,
          'status' => false,
          'message' => "error",

        ];
      }
      return response($response, 200);
    }
  }
  function updateData(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'id' => 'required',
      'uid' => 'required',
      'warehouse_name' => 'required',
      'email' => 'required',
      'phone_no' => 'required',
      'poc_name' => 'required',
      'poc_ph_no' => 'required',
      'poc_email' => 'required',
      'fssai' => 'required',
      'gst_no' => 'required',
      'billing_address' => 'required',
      'country' => 'required',
      'state' => 'required',
      'district' => 'required',
      'pincode' => 'required',
      'address' => 'required',
      'latitude' => 'required',
      'longitude' => 'required',
      'service_city' => 'required',
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      $existingRecord = WarehouseModel::where('id', '=', $request->id)->first();
      $timeStamp = date("Y-m-d H:i:s");
      $existingRecord->uid  = $request->uid;
      $existingRecord->warehouse_name  = $request->warehouse_name;
      $existingRecord->email  = $request->email;
      $existingRecord->phone_no  = $request->phone_no;
      $existingRecord->poc_name  = $request->poc_name;
      $existingRecord->poc_ph_no  = $request->poc_ph_no;
      $existingRecord->poc_email  = $request->poc_email;
      $existingRecord->fssai  = $request->fssai;
      $existingRecord->gst_no  = $request->gst_no;
      $existingRecord->billing_address  = $request->billing_address;
      $existingRecord->country  = $request->country;
      $existingRecord->state  = $request->state;
      $existingRecord->district  = $request->district;
      $existingRecord->pincode  = $request->pincode;
      $existingRecord->address  = $request->address;
      $existingRecord->latitude  = $request->latitude;
      $existingRecord->longitude  = $request->longitude;
      $existingRecord->service_city  = $request->service_city;
      $existingRecord->updated_at = $timeStamp;
      $qResponce = $existingRecord->save();

      if ($qResponce) {
        $response = [
          "response" => 200,
          'status' => true,
          'message' => "successfully",
          'id' => $existingRecord->id

        ];
      } else {
        $response = [
          "response" => 201,
          'status' => false,
          'message' => "error",

        ];
      }
      return response($response, 200);
    }
  }

  function getData()
  {

    $data = DB::table("warehouse")
      ->select(
        'id',
        'warehouse_name',
        'uid',
        'email',
        'phone_no',
        'poc_name',
        'poc_ph_no',
        'poc_email',
        'gst_no',
        'fssai',
        'latitude',
        'longitude',
        'billing_address',
        'address',
        'is_active',
      )
      ->orderBy('created_at', 'DESC')
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  function getDataById($id)
  {
    $data = DB::table("warehouse")
      ->select('warehouse.*')
      ->where("warehouse.id", "=", $id)
      ->first();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  function changeWarehouseStaus($id)
  {
    $existingRecord = WarehouseModel::where('id', '=', $id)->first();
    $timeStamp = date("Y-m-d H:i:s");
    $existingRecord->updated_at = $timeStamp;
    $existingRecord->is_active = !$existingRecord->is_active;
    $qResponce = $existingRecord->save();


    if ($qResponce) {
      $response = [
        "response" => 200,
        'status' => true,
        'message' => "successfully",

      ];
    } else {
      $response = [
        "response" => 201,
        'status' => false,
        'message' => "error",

      ];
    }

    return response($response, 200);
  }

  function getActiveWarehouseList()
  {

    $data = DB::table("warehouse")
      ->select('warehouse.*')
      // ->where("warehouse.is_active","=",1)
      ->orderBy("warehouse.created_at", "DESC")
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }
}
