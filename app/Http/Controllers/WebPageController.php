<?php

namespace App\Http\Controllers;

use App\Models\WebPageModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class WebPageController extends Controller
{
  function getDataByPageId($id)
  {

    $data = DB::table("web_pages")
      ->select('web_pages.*')
      ->where("web_pages.page_id", "=", $id)
      ->first();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  public function getWebAppUrl()
  {
    $webAppUrl = env('WEB_APP_URL', 'https://core.entekambolam.com');


    $response = [
      "response" => 200,
      'data' => $webAppUrl,
    ];
    return response($response, 200);
  }

  function updateData(Request $request)
  {


    $validator = Validator::make(request()->all(), [
      'page_id' => 'required',

    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {

      try {
        $timeStamp = date("Y-m-d H:i:s");
        $dataModel = WebPageModel::where("page_id", $request->page_id)->first();

        if (isset($request->body)) {
          $dataModel->body  = $request->body;
        }

        $dataModel->updated_at = $timeStamp;
        $qResponce = $dataModel->save();
        if ($qResponce) {

          $response = [
            "response" => 200,
            'status' => true,
            'message' => "successfully",


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
