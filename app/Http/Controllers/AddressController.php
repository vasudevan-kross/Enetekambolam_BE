<?php

namespace App\Http\Controllers;

use App\Models\AddressModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class AddressController extends Controller
{
  function getDataById($id)
  {

    $data = DB::table("user_address")
      ->select('user_address.*')
      ->where("user_address.id", "=", $id)
      ->first();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }
  function getDataByUserId(Request $request, $id)
  {
    // Check if the 'include_deleted' flag is set in the request
    $includeDeleted = $request->input('include_deleted', false);

    // Start building the query
    $query = DB::table("user_address")
      ->select('user_address.*')
      ->where("user_address.user_id", "=", $id)
      ->orderBy('user_address.created_at', 'DESC');

    // If 'include_deleted' is false, filter for non-deleted addresses
    if (!$includeDeleted) {
      $query->where("user_address.is_deleted", "=", 0);
    }

    // Execute the query and get the results
    $data = $query->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  public function markAsdeleteAddress(Request $request)
  {
    $validated = $request->validate([
      'id' => 'required|integer|exists:user_address,id',
      'user_id' => 'required|integer|exists:users,id',
    ]);

    $addressId = $validated['id'];
    $userId = $validated['user_id'];

    // Update the `is_deleted` column after checking `user_id` and `id`
    $affected = DB::table('user_address')
      ->where('id', $addressId)
      ->where('user_id', $userId)
      ->update(['is_deleted' => 1]);

    // Prepare the response array
    if ($affected) {
      $response = [
        'response' => 200,  // Successful operation code
        'status' => true,    // Status of the operation
        'message' => 'Address marked as deleted.',
      ];
      return response($response, 200);  // Return response in the requested format
    } else {
      $response = [
        'response' => 201,  // Failure operation code
        'status' => false,   // Status of the operation
        'message' => 'Failed to mark address as deleted. Either address does not belong to the user or an error occurred.',
      ];
      return response($response, 201);  // Return failure response with the appropriate status and message
    }
  }


  function addData(Request $request)
  {

    $validator = Validator::make(request()->all(), [
      'user_id' => 'required',
      'name' => 'required',
      's_phone' => 'required',
      'pincode' => 'required',
      'city' => 'required'

    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {

      try {
        $timeStamp = date("Y-m-d H:i:s");
        $dataModel = new AddressModel;

        $dataModel->user_id = $request->user_id;
        $dataModel->name = $request->name;
        $dataModel->s_phone  = $request->s_phone;
        // $dataModel->landmark = $request->landmark;
        $dataModel->area  = $request->area;
        $dataModel->city  = $request->city;
        if (isset($request->landmark)) {
          $dataModel->landmark = $request->landmark;
        }
        if (isset($request->lat)) {
          $dataModel->lat  = $request->lat;
        }
        if (isset($request->lng)) {
          $dataModel->lng  = $request->lng;
        }

        $dataModel->pincode = $request->pincode;
        if (isset($request->flat_no)) {
          $dataModel->flat_no = $request->flat_no;
        }
        if (isset($request->apartment_name)) {
          $dataModel->apartment_name  = $request->apartment_name;
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
}
