<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WebAppSettingsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



class WebAppSettingsController extends Controller
{

  function updateData(Request $request)
  {

    $initialCheck = false;
    $validator = Validator::make(request()->all(), [
      'id' => 'required',
    ]);
    if ($validator->fails())
      $initialCheck = true;
    if ($initialCheck)
      return response(["response" => 400], 400);
    else {
      $timeStamp = date("Y-m-d H:i:s");
      try {
        $dataModel = WebAppSettingsModel::where("id", $request->id)->first();
        if (isset($request->value))
          $dataModel->value = $request->value;
        $dataModel->updated_at = $timeStamp;

        $qResponce = $dataModel->save();
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

  function getDataDataById($id)
  {

    $data = DB::table("web_app_settings")
      ->select('web_app_settings.*')
      ->orderBy("web_app_settings.created_at", "ASC")
      ->where("web_app_settings.id", "=", $id)
      ->first();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  function getDataDataByIds($ids)
  {
    try {
      $jsonIds = json_decode($ids);
      $data = DB::table("web_app_settings")
        ->select('web_app_settings.*')
        ->orderBy("web_app_settings.created_at", "ASC")
        ->whereIn("web_app_settings.id", $jsonIds)
        ->orderBy("web_app_settings.id", "ASC")
        ->get();

      $response = [
        "response" => 200,
        'data' => $data,
      ];

      return response($response, 200);
    } catch (Throwable $th) {
      throw $th;
    }
  }

  function getDataDataByTitle($title)
  {
    // Convert the title to lowercase
    $titleLower = strtolower($title);

    $data = DB::table("web_app_settings")
      ->select('web_app_settings.*')
      ->whereRaw("LOWER(web_app_settings.title) = ?", [$titleLower])
      ->orderBy("web_app_settings.created_at", "ASC")
      ->first();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }
  function getDataAllData()
  {

    $data = DB::table("web_app_settings")
      ->select('web_app_settings.*')
      ->orderBy("web_app_settings.id", "ASC")
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }
}
