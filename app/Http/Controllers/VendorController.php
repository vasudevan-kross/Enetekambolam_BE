<?php

namespace App\Http\Controllers;

use App\Models\VendorModel;
use Illuminate\Http\Request;
use App\Models\OrderModel;
use App\Models\CartModel;
use App\Models\TransactionsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ImageModel;
use App\Models\ProductModel;
use App\Http\Controllers\WebAppSettingsController;
use App\Models\User;

class VendorController extends Controller
{
  function addData(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'supplier_name' => 'required',
      'user_name' => 'required',
      'office_ph_no' => 'required',
      'poc_name' => 'required',
      'poc_ph_no' => 'required',
      'poc_email' => 'required',
      'country' => 'required',
      'state' => 'required',
      'district' => 'required',
      'pincode' => 'required',
      'uid' => 'required',
      'bankName' => 'required',
      'ac_no' => 'required',
      'ifsc' => 'required',
      'branch_name' => 'required',
      'branch_address' => 'required'
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      $timeStamp = date("Y-m-d H:i:s");
      $dataModel = new VendorModel();
      $dataModel->supplier_name  = $request->supplier_name;
      $dataModel->user_name  = $request->user_name;
      $dataModel->office_ph_no  = $request->office_ph_no;
      $dataModel->poc_name  = $request->poc_name;
      $dataModel->poc_ph_no  = $request->poc_ph_no;
      $dataModel->poc_email  = $request->poc_email;
      $dataModel->fssai  = $request->fssai;
      $dataModel->arn  = $request->arn;
      $dataModel->pan  = $request->pan;
      $dataModel->gst_no  = $request->gst_no;
      $dataModel->gst_state_code  = $request->gst_state_code;
      $dataModel->country  = $request->country;
      $dataModel->state  = $request->state;
      $dataModel->district  = $request->district;
      $dataModel->pincode  = $request->pincode;
      $dataModel->address  = $request->address;
      $dataModel->uid  = $request->uid;
      $dataModel->is_price_edit  = $request->is_price_edit;
      $dataModel->outlet  = $request->outlet;
      $dataModel->bankName  = $request->bankName;
      $dataModel->ac_no  = $request->ac_no;
      $dataModel->ifsc  = $request->ifsc;
      $dataModel->branch_name  = $request->branch_name;
      $dataModel->branch_address  = $request->branch_address;
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

  function uploadImages(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'imgData' => 'required|array',
      'id' => 'required',
    ]);

    if ($validator->fails()) {
      return response(["response" => 400], 400);
    }

    $responses = [];
    $timeStamp = date("Y-m-d H:i:s");

    foreach ($request->imgData as $imageData) {
      if (!isset($imageData['image'])) {
        continue;
      }

      $imageType = $imageData['image_type'];
      $image = $imageData['image'];

      try {
        $existingRecord = ImageModel::where('table_id', '=', $request->id)
          ->where('table_name', '=', 'vendor')
          ->where('image_type', '=', $imageType)
          ->first();

        if (!is_object($image)) {
          continue;
        }
        $newName = rand() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('/uploads/images'), $newName);

        if ($existingRecord) {
          $existingRecord->image = $newName;
          $existingRecord->updated_at = $timeStamp;

          if ($existingRecord->save()) {
            $responses[] = [
              'status' => true,
              'image_type' => $imageType,
              'file' => $newName,
              'message' => "Image of type $imageType updated successfully",
            ];
          } else {
            $responses[] = [
              'status' => false,
              'image_type' => $imageType,
              'message' => "Failed to update image of type $imageType",
            ];
          }
        } else {
          $dataModel = new ImageModel();
          $dataModel->table_name = "vendor";
          $dataModel->table_id = $request->id;
          $dataModel->image_type = $imageType;
          $dataModel->image = $newName;
          $dataModel->created_at = $timeStamp;
          $dataModel->updated_at = $timeStamp;

          if ($dataModel->save()) {
            $responses[] = [
              'status' => true,
              'image_type' => $imageType,
              'file' => $newName,
              'message' => "Image of type $imageType uploaded successfully",
            ];
          } else {
            $responses[] = [
              'status' => false,
              'image_type' => $imageType,
              'message' => "Failed to save image of type $imageType",
            ];
          }
        }
      } catch (\Exception $e) {
        $responses[] = [
          'status' => false,
          'image_type' => $imageType,
          'message' => "Error: $e",
        ];
      }
    }

    return response(['response' => 200, 'results' => $responses], 200);
  }

  function updateData(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'id' => 'required',
      'supplier_name' => 'required',
      'user_name' => 'required',
      'office_ph_no' => 'required',
      'poc_name' => 'required',
      'poc_ph_no' => 'required',
      'poc_email' => 'required',
      'country' => 'required',
      'state' => 'required',
      'district' => 'required',
      'pincode' => 'required',
      'uid' => 'required',
      'bankName' => 'required',
      'ac_no' => 'required',
      'ifsc' => 'required',
      'branch_name' => 'required',
      'branch_address' => 'required'
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      $existingRecord = VendorModel::where('id', '=', $request->id)->first();
      $timeStamp = date("Y-m-d H:i:s");
      $existingRecord->supplier_name  = $request->supplier_name;
      $existingRecord->user_name  = $request->user_name;
      $existingRecord->office_ph_no  = $request->office_ph_no;
      $existingRecord->poc_name  = $request->poc_name;
      $existingRecord->poc_ph_no  = $request->poc_ph_no;
      $existingRecord->poc_email  = $request->poc_email;
      $existingRecord->fssai  = $request->fssai;
      $existingRecord->arn  = $request->arn;
      $existingRecord->pan  = $request->pan;
      $existingRecord->gst_no  = $request->gst_no;
      $existingRecord->gst_state_code  = $request->gst_state_code;
      $existingRecord->country  = $request->country;
      $existingRecord->state  = $request->state;
      $existingRecord->district  = $request->district;
      $existingRecord->pincode  = $request->pincode;
      $existingRecord->address  = $request->address;
      $existingRecord->uid  = $request->uid;
      $existingRecord->is_price_edit  = $request->is_price_edit;
      $existingRecord->outlet  = $request->outlet;
      $existingRecord->bankName  = $request->bankName;
      $existingRecord->ac_no  = $request->ac_no;
      $existingRecord->ifsc  = $request->ifsc;
      $existingRecord->branch_name  = $request->branch_name;
      $existingRecord->branch_address  = $request->branch_address;
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

    $data = DB::table("vendor")
      ->select(
        'id',
        'supplier_name',
        'user_name',
        'office_ph_no',
        'poc_name',
        'poc_ph_no',
        'poc_email',
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
    $data = DB::table("vendor")
      ->select(
        'vendor.*',
        'images1.image as check_book_image',
        'images2.image as agreement_image',
        'images3.image as pan_card_image',
        'images4.image as gst_certificate_image',
        'images5.image as fssiCertificateImage'
      )
      ->leftJoin('images as images1', function ($join) {
        $join->on('images1.table_id', '=', 'vendor.id')
          ->where('images1.table_name', '=', "vendor")
          ->where('images1.image_type', '=', 3);
      })
      ->leftJoin('images as images2', function ($join) {
        $join->on('images2.table_id', '=', 'vendor.id')
          ->where('images2.table_name', '=', "vendor")
          ->where('images2.image_type', '=', 4);
      })
      ->leftJoin('images as images3', function ($join) {
        $join->on('images3.table_id', '=', 'vendor.id')
          ->where('images3.table_name', '=', "vendor")
          ->where('images3.image_type', '=', 5);
      })
      ->leftJoin('images as images4', function ($join) {
        $join->on('images4.table_id', '=', 'vendor.id')
          ->where('images4.table_name', '=', "vendor")
          ->where('images4.image_type', '=', 6);
      })
      ->leftJoin('images as images5', function ($join) {
        $join->on('images5.table_id', '=', 'vendor.id')
          ->where('images5.table_name', '=', "vendor")
          ->where('images5.image_type', '=', 7);
      })
      ->where("vendor.id", "=", $id)
      ->first();

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
      // ->where("vendor.is_active","=",1)
      ->orderBy("vendor.created_at", "DESC")
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  function changeVendorStaus($id)
  {
    $existingRecord = VendorModel::where('id', '=', $id)->first();
    $timeStamp = date("Y-m-d H:i:s");
    $existingRecord->updated_at = $timeStamp;
    $existingRecord->is_active = !$existingRecord->is_active;
    $qResponce = $existingRecord->save();


    if ($qResponce) {
      $response = [
        "response" => 200,
        'status' => true,
        'message' => $existingRecord->is_active ? "Supplier has been activated successfully"
                      : "Supplier has been de-activated successfully",

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
