<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\WebAppSettingsModel;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;

class SendNotificationController extends Controller
{
  // function sendFirebaseNotificationToLowWallet($title, $body, $imageUrl, $token)
  // {

  //   $dataModel = WebAppSettingsModel::where("id", 10)->first();

  //   if (isset($dataModel->value)) {
  //     $sendData = [
  //       "to" => "$token",
  //       "notification" => [
  //         "sound" => "default",
  //         "body" => $body,
  //         "title" => $title,
  //         "content_available" => true,
  //         "priority" => "high",
  //         "image" => $imageUrl != null && $imageUrl != "" ? $imageUrl : "",
  //       ],
  //       "data" => [
  //         "is_specific" => true,
  //         "sound" => "default",
  //         "body" => $body,
  //         "title" => $title,
  //         "content_available" => true,
  //         "priority" => "high",
  //         "imageUrl" => $imageUrl != null && $imageUrl != "" ? $imageUrl : ""
  //       ]
  //     ];
  //     $sendResponse = Http::withHeaders([
  //       'Authorization' => 'key=' . $dataModel->value
  //     ])->post('https://fcm.googleapis.com/fcm/send', $sendData);

  //     $response = [
  //       "response" => $sendResponse->status(),
  //       "message" => $sendResponse->body(),
  //     ];

  //     return $response;
  //   } else {
  //     $response = [
  //       "response" => 201,
  //       'status' => false,
  //       'message' => "error",

  //     ];
  //     return $response;
  //   }
  // }

  public function sendFirebaseNotificationToLowWallet($title, $body, $imageUrl, $token)
  {
    // Path to your Firebase service account JSON file
    $serviceAccountPath = env('FIREBASE_CREDENTIALS_PATH');

    // Ensure the file exists
    if (!file_exists(base_path($serviceAccountPath))) {
      return [
        'response' => 500,
        'status' => false,
        'message' => 'Firebase credentials file not found at the specified path',
      ];
    }

    try {
      // Initialize Firebase
      $factory = (new Factory)->withServiceAccount(base_path($serviceAccountPath));
      $messaging = $factory->createMessaging();

      // Create a notification payload
      $notification = Notification::create($title, $body);

      // Create a message
      $message = CloudMessage::withTarget('token', $token)
        ->withNotification($notification)
        ->withData([
          'is_specific' => true,
          'sound' => 'default',
          'priority' => 'high',
          'imageUrl' => $imageUrl ?: '',
        ]);

      // Send the message
      $response = $messaging->send($message);

      return [
        'response' => 200,
        'status' => true,
        'message' => 'Notification sent successfully',
        'firebase_response' => $response,
      ];
    } catch (\Exception $e) {
      return [
        'response' => 500,
        'status' => false,
        'message' => $e->getMessage(),
      ];
    }
  }

  public function sendFirebaseNotificationToToken($title, $body, $imageUrl, $token)
  {
    // Path to Firebase service account JSON file, retrieved from .env
    $serviceAccountPath = env('FIREBASE_CREDENTIALS_PATH');

    // Ensure the file exists
    if (!file_exists(base_path($serviceAccountPath))) {
      return [
        'response' => 500,
        'status' => false,
        'message' => 'Firebase credentials file not found at the specified path',
      ];
    }

    try {
      // Initialize Firebase
      $factory = (new Factory)->withServiceAccount(base_path($serviceAccountPath));
      $messaging = $factory->createMessaging();

      // Create the notification payload
      $notification = Notification::create($title, $body);

      // Create the data payload
      $dataPayload = [
        'sound' => 'default',
        'body' => $body,
        'title' => $title,
        'content_available' => true,
        'priority' => 'high',
        'imageUrl' => $imageUrl ?: '', // Use empty string if no image is provided
      ];

      // Create the message
      $message = CloudMessage::withTarget('token', $token)
        ->withNotification($notification)
        ->withData($dataPayload);

      // Send the message
      $response = $messaging->send($message);

      return [
        'response' => 200,
        'status' => true,
        'message' => 'Notification sent successfully',
        'firebase_response' => $response,
      ];
    } catch (\Exception $e) {
      return [
        'response' => 500,
        'status' => false,
        'message' => $e->getMessage(),
      ];
    }
  }

  public function sendFirebaseNotificationToTopic($title, $body, $imageUrl, $topic)
  {
    // Path to Firebase service account JSON file, retrieved from .env
    $serviceAccountPath = env('FIREBASE_CREDENTIALS_PATH');

    // Ensure the file exists
    if (!file_exists(base_path($serviceAccountPath))) {
      return [
        'response' => 500,
        'status' => false,
        'message' => 'Firebase credentials file not found at the specified path',
      ];
    }

    try {
      // Initialize Firebase
      $factory = (new Factory)->withServiceAccount(base_path($serviceAccountPath));
      $messaging = $factory->createMessaging();

      // Create the notification payload
      $notification = Notification::create($title, $body);

      // Create the data payload
      $dataPayload = [
        'sound' => 'default',
        'body' => $body,
        'title' => $title,
        'content_available' => true,
        'priority' => 'high',
        'imageUrl' => $imageUrl ?: '', // Use empty string if no image is provided
      ];

      // Create the message targeting a topic
      $message = CloudMessage::withTarget('topic', $topic)
        ->withNotification($notification)
        ->withData($dataPayload);

      // Send the message
      $response = $messaging->send($message);

      return [
        'response' => 200,
        'status' => true,
        'message' => 'Notification sent successfully to topic',
        'firebase_response' => $response,
      ];
    } catch (\Exception $e) {
      return [
        'response' => 500,
        'status' => false,
        'message' => $e->getMessage(),
      ];
    }
  }


  // function sendFirebaseNotificationToToken($title, $body, $imageUrl, $token)
  // {
  //   $dataModel = WebAppSettingsModel::where("id", 10)->first();

  //   if (isset($dataModel->value)) {
  //     $sendData = [
  //       "to" => "$token",
  //       "notification" => [
  //         "sound" => "default",
  //         "body" => $body,
  //         "title" => $title,
  //         "content_available" => true,
  //         "priority" => "high",
  //         "image" => $imageUrl != null && $imageUrl != "" ? $imageUrl : "",
  //       ],
  //       "data" => [
  //         "sound" => "default",
  //         "body" => $body,
  //         "title" => $title,
  //         "content_available" => true,
  //         "priority" => "high",
  //         "imageUrl" => $imageUrl != null && $imageUrl != "" ? $imageUrl : ""
  //       ]
  //     ];
  //     $sendResponse = Http::withHeaders([
  //       'Authorization' => 'key=' . $dataModel->value
  //     ])->post('https://fcm.googleapis.com/fcm/send', $sendData);

  //     $response = [
  //       "response" => $sendResponse->status(),
  //       "message" => $sendResponse->body(),
  //     ];

  //     return $response;
  //   } else {
  //     $response = [
  //       "response" => 201,
  //       'status' => false,
  //       'message' => "error",

  //     ];
  //     return $response;
  //   }
  // }


  // function sendFirebaseNotificationToTopic($title, $body, $imageUrl, $topic)
  // {
  //   $dataModel = WebAppSettingsModel::where("id", 10)->first();

  //   if (isset($dataModel->value)) {
  //     $sendData = [
  //       "to" => "/topics/$topic",
  //       "notification" => [
  //         "sound" => "default",
  //         "body" => $body,
  //         "title" => $title,
  //         "content_available" => true,
  //         "priority" => "high",
  //         "image" => $imageUrl != null && $imageUrl != "" ? $imageUrl : "",
  //       ],
  //       "data" => [
  //         "sound" => "default",
  //         "body" => $body,
  //         "title" => $title,
  //         "content_available" => true,
  //         "priority" => "high",
  //         "imageUrl" => $imageUrl != null && $imageUrl != "" ? $imageUrl : ""
  //       ]
  //     ];
  //     $sendResponse = Http::withHeaders([
  //       'Authorization' => 'key=' . $dataModel->value
  //     ])->post('https://fcm.googleapis.com/fcm/send', $sendData);

  //     $response = [
  //       "response" => $sendResponse->status(),
  //       "message" => $sendResponse->body(),
  //     ];

  //     return $response;
  //   } else {
  //     $response = [
  //       "response" => 201,
  //       'status' => false,
  //       'message' => "error",

  //     ];
  //     return $response;
  //   }
  // }

  function sendReqFirebaseNotificationToTopic(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'title' => 'required',
      'body' => 'required',
      "topic" => 'required'
    ]);

    if ($validator->fails()) {
      return response(["response" => 400], 400);
    } else {
      $sendResponse = $this->sendFirebaseNotificationToTopic($request->title, $request->body, $request->image_Url, $request->topic);


      return response($sendResponse, 200);
    }
  }
  function sendReqFirebaseNotificationToToken(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'title' => 'required',
      'body' => 'required',
      "token" => 'required'
    ]);

    if ($validator->fails()) {
      return response(["response" => 400], 400);
    } else {
      $sendResponse = $this->sendFirebaseNotificationToToken($request->title, $request->body, $request->image_Url, $request->token);


      return response($sendResponse, 200);
    }
  }
}
