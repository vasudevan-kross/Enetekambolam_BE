<?php

namespace App\Http\Controllers;

use App\Models\UserNotificationModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ImageModel;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SpecificNotificationModel;

class UserNotificationController extends Controller
{
  protected $sendNotificationController;

  public function __construct(SendNotificationController $sendNotificationController)
  {
    $this->sendNotificationController = $sendNotificationController;
  }
  function sendLowWalletNotification(Request $request)
  {
    $request->validate([
      'low_amount' => 'required|numeric|min:1', // Ensure low_amount is valid
    ]);

    $lowAmount = $request->low_amount;

    // Fetch users whose wallet amount is below the provided threshold
    $users = User::where('wallet_amount', '<', $lowAmount)
      ->orWhereNull('wallet_amount')
      ->get();
    foreach ($users as $user) {
      if ($user->fcm != null) {
        $title = "Hello, $user->name";
        $body = "Your wallet balance is currently below $lowAmount . Please recharge to ensure uninterrupted access to our services.";

        $timeStamp = date("Y-m-d H:i:s");
        $dataModel = new SpecificNotificationModel;
        $dataModel->title = $title;
        $dataModel->user_id = $user->id;
        $dataModel->body = $body;
        $dataModel->notification_type = "wallet";
        $dataModel->created_at = $timeStamp;
        $dataModel->updated_at = $timeStamp;
        $qResponce = $dataModel->save();
        // if($user->id==5){
        $this->sendNotificationController->sendFirebaseNotificationToLowWallet($title, $body, "", $user->fcm);
        //  }


      }
    }
    $response = [
      "response" => 200,
      'status' => true,
      'message' => "successfully",


    ];
    return response($response, 200);
  }

  function getDataById($id)
  {
    $data = DB::table("user_notification")
      ->select(
        'user_notification.*',
        'images.id as image_id',
        'images.image'
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'user_notification.id')
          ->where('images.table_name', '=', 'user_notification')
          ->where('images.image_type', '=', 1);
      })
      ->where("user_notification.id", ">=", $id)
      ->first();
    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }
  function getDataByDate($date)
  {
    $data = DB::table("user_notification")
      ->select(
        'user_notification.*',
        'images.id as image_id',
        'images.image'
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'user_notification.id')
          ->where('images.table_name', '=', 'user_notification')
          ->where('images.image_type', '=', 1);
      })
      ->where("user_notification.created_at", ">=", $date)
      ->orderBy('user_notification.created_at', 'DESC')
      ->get();
    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  function getDataAllNoti()
  {

    $data = DB::table("user_notification")
      ->select(
        'user_notification.*',
        'images.id as image_id',
        'images.image'
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'user_notification.id')
          ->where('images.table_name', '=', 'user_notification')
          ->where('images.image_type', '=', 1);
      })
      ->orderBy('user_notification.created_at', 'DESC')
      ->get();
    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }
  function addData(Request $request)
  {

    $validator = Validator::make(request()->all(), [
      'title' => 'required',
      'body' => 'required',

    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {

      try {
        $timeStamp = date("Y-m-d H:i:s");
        $dataModel = new UserNotificationModel;
        $dataModel->title = $request->title;
        $dataModel->body = $request->body;
        $dataModel->created_at = $timeStamp;
        $dataModel->updated_at = $timeStamp;
        $qResponce = $dataModel->save();
        if ($qResponce) {
          if (isset($request->image) && isset($request->image_base_url)) {
            $image = $request->image;
            $newName = rand() . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('/uploads/images'), $newName);
            $timeStamp = date("Y-m-d H:i:s");
            $dataImageModel = new ImageModel;
            $dataImageModel->table_name = "user_notification";
            $dataImageModel->table_id = $dataModel->id;
            $dataImageModel->image_type = 1;
            $dataImageModel->image = $newName;
            $dataImageModel->created_at = $timeStamp;
            $dataImageModel->updated_at = $timeStamp;
            $qResponceImage = $dataImageModel->save();
            $newImageUrl = $request->image_base_url . "/" . $newName;

            if ($qResponceImage) {
              $this->sendNotificationController->sendFirebaseNotificationToTopic($request->title, $request->body, $newImageUrl, "all");
            }
          } else {
            $this->sendNotificationController->sendFirebaseNotificationToTopic($request->title, $request->body, "", "all");
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
      } catch (\Exception $e) {

        $response = [
          "response" => 201,
          'status' => false,
          'message' => "errorx",

        ];
        return response($response, 200);
      }
    }
  }
}
