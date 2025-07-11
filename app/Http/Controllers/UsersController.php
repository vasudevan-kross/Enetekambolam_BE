<?php

namespace App\Http\Controllers;

use App\Models\AddressModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AssignModel;
use App\Models\TransactionsModel;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UsersController extends Controller
{
  function addData(Request $request)
  {

    $validator = Validator::make(request()->all(), [
      'name' => 'required'
    ]);

    if ($validator->fails())

      return response(["response" => 400], 400);
    else {
      if (!isset($request->phone) && !isset($request->email) && !isset($request->password)) {
        $response = [
          "response" => 201,
          'status' => false,
          'message' => "phone or email required"
        ];
        return response($response, 200);
      }
      // else if(!isset($request->phone)){
      //   if(!isset($request->email)||!isset($request->password)){
      //     $response = [
      //       "response"=>201,
      //       'status'=>false,
      //       'message' => "email and password required"];
      //       return response($response, 200);

      //   }
      // }
      if (isset($request->phone)) {
        $alreadyAddedModel = User::where("phone", $request->phone)->first();
        if ($alreadyAddedModel) {
          $response = [
            "response" => 201,
            'status' => false,
            'message' => "phone number already exists"
          ];
          return response($response, 200);
        }
      }
      if (isset($request->email)) {
        $alreadyAddedModel = User::where("email", $request->email)->first();
        if ($alreadyAddedModel) {
          $response = [
            "response" => 201,
            'status' => false,
            'message' => "email id already exists"
          ];
          return response($response, 200);
        }
      }

      if (isset($request->phone)) {
        if (!is_numeric($request->phone)) {
          $response = [
            "response" => 201,
            'status' => false,
            'message' => "Please enter valid phone number"
          ];
          return response($response, 200);
        }
      }
      try {
        $timeStamp = date("Y-m-d H:i:s");
        $userModel = new User;
        if (isset($request->phone)) {
          $userModel->phone = $request->phone;
        }
        if (isset($request->email)) {
          $userModel->email = $request->email;
        }


        if (isset($request->password)) {
          $userModel->password = Hash::Make($request->password);
        } else if (!isset($request->password)) {
          $userModel->password = Hash::make(Str::random(8));
        }
        $userModel->name = $request->name;
        $userModel->created_at = $timeStamp;
        $userModel->updated_at = $timeStamp;
        $qResponce = $userModel->save();

        if ($qResponce) {
          if (isset($request->role)) {
            $userId = $userModel->id;
            $dataModelAssign = new AssignModel;
            $dataModelAssign->user_id = $userId;
            $dataModelAssign->role_id = $request->role;
            $dataModelAssign->created_at = $timeStamp;
            $dataModelAssign->updated_at = $timeStamp;
            $dataModelAssign->save();
          }

          if (isset($request->image)) {
            if ($request->hasFile('image'))
              app('App\Http\Controllers\ImageCountController')->uploadImage($request->image, "users", $userId, 1, null);
            //1=profile_image
          }
          $response = [
            "response" => 200,
            'status' => true,
            'message' => "successfully"

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
  // function getDataByRole($id)
  // {

  //   $data = DB::table("assign_role")
  //     ->select(
  //       'assign_role.*',
  //       'users.name',
  //       'users.email',
  //       'users.phone',
  //       'users.fcm',
  //       'users.created_at',
  //       'users.wallet_amount',
  //       'role.title as role_title'
  //     )
  //     ->Join('users', 'users.id', '=', 'assign_role.user_id')
  //     ->where("assign_role.role_id", "=", $id)
  //     ->get();
  //   for ($i = 0; $i < count($data); $i++) {

  //     $data[$i]->role = DB::table("assign_role")
  //       ->select(
  //         'assign_role.id',
  //         'role.id as role_id',
  //         'role.title as role_title'
  //       )
  //       ->Join('role', 'role.id', '=', 'assign_role.role_id')
  //       ->where('assign_role.user_id', '=',  $data[$i]->user_id)
  //       ->get();
  //   }
  //   $response = [
  //     "response" => 200,
  //     'data' => $data,
  //   ];

  //   return response($response, 200);
  // }

  function getDataByRole($id)
  {
    // Check if the role_id exists in the assign_role table
    $roleExists = DB::table('assign_role')->where('role_id', $id)->exists();

    if (!$roleExists) {
      return response([
        "response" => 200,
        "data" => [],
        "message" => "No data found for the provided role ID.",
      ], 200);
    }

    $data = DB::table("assign_role")
      ->select(
        'assign_role.*',
        'users.name',
        'users.email',
        'users.phone',
        'users.fcm',
        'users.created_at',
        'users.wallet_amount',
        'role.title as role_title'
      )
      ->join('users', 'users.id', '=', 'assign_role.user_id')
      ->join('role', 'role.id', '=', 'assign_role.role_id')
      ->where("assign_role.role_id", "=", $id)
      ->get();

    for ($i = 0; $i < count($data); $i++) {
      $data[$i]->role = DB::table("assign_role")
        ->select(
          'assign_role.id',
          'role.id as role_id',
          'role.title as role_title'
        )
        ->join('role', 'role.id', '=', 'assign_role.role_id')
        ->where('assign_role.user_id', '=', $data[$i]->user_id)
        ->get();
    }

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  function getDataById($id)
  {

    $data = DB::table("users")
      ->select(
        'users.name',
        'users.email',
        'users.phone',
        'users.fcm',
        'users.created_at',
        'users.wallet_amount'
      )
      ->where("users.id", "=", $id)
      ->first();


    $data->role = DB::table("assign_role")
      ->select(
        'assign_role.id',
        'role.id as role_id',
        'role.title as role_title',
      )
      ->Join('role', 'role.id', '=', 'assign_role.role_id')
      ->where('assign_role.user_id', '=', $id)
      ->get();


    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }
  // function getData()
  // {
  //   $data = DB::table("users")
  //   ->select(
  //     'users.id','users.email','users.phone',
  //  'users.name',
  //  'users.wallet_amount',
  //   'users.created_at','users.updated_at',
  //   'images.id as image_id',
  //   'images.image'   
  //   )
  //   ->leftJoin('images', function ($join) {
  //     $join->on('images.table_id', '=', 'users.id')
  //     ->where('images.table_name','=','users')
  //     ->where('images.image_type','=',1);
  //     })
  //     ->orderBy('users.created_at', 'ASC')
  //    ->get();
  //    for($i=0;$i<count($data);$i++){

  //     $data[$i]->role= DB::table("assign_role")
  //     ->select(
  //         'assign_role.id',
  //     'role.id as role_id' ,
  //     'role.title as role_title',
  //     )
  //     ->Join('role','role.id','=','assign_role.role_id')
  //     ->where('assign_role.user_id', '=',  $data[$i]->id)
  //     ->get();
  //   }

  //         $response = [
  //             "response"=>200,
  //             'data'=>$data,
  //         ];

  //   return response($response, 200);
  // }

  // function getData()
  // {
  //   $data = DB::table("users")
  //     ->select(
  //       'users.id',
  //       'users.email',
  //       'users.phone',
  //       'users.name',
  //       'users.wallet_amount',
  //       'users.created_at',
  //       'users.updated_at',
  //       'images.id as image_id',
  //       'images.image'
  //     )
  //     ->leftJoin('images', function ($join) {
  //       $join->on('images.table_id', '=', 'users.id')
  //         ->where('images.table_name', '=', 'users')
  //         ->where('images.image_type', '=', 1);
  //     })
  //     ->whereNotNull('users.name')  // Ensure name is not null
  //     ->whereNotNull('users.phone') // Ensure phone is not null
  //     ->orderBy('users.created_at', 'ASC')
  //     ->get();

  //   for ($i = 0; $i < count($data); $i++) {
  //     $data[$i]->role = DB::table("assign_role")
  //       ->select(
  //         'assign_role.id',
  //         'role.id as role_id',
  //         'role.title as role_title'
  //       )
  //       ->join('role', 'role.id', '=', 'assign_role.role_id')
  //       ->where('assign_role.user_id', '=', $data[$i]->id)
  //       ->get();
  //   }

  //   $response = [
  //     "response" => 200,
  //     'data' => $data,
  //   ];

  //   return response($response, 200);
  // }

  function getData()
  {
    $data = DB::table("users")
      ->select(
        'users.id',
        'users.email',
        'users.phone',
        'users.name',
        'users.wallet_amount',
        'users.created_at',
        'users.updated_at',
        'images.id as image_id',
        'images.image'
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'users.id')
          ->where('images.table_name', '=', 'users')
          ->where('images.image_type', '=', 1);
      })
      ->whereNotNull('users.name') // Ensure name is not null
      ->whereNotNull('users.phone') // Ensure phone is not null
      ->orderBy('users.created_at', 'DESC')
      ->get()
      ->toArray(); // Convert Collection to array

    foreach ($data as $key => $user) {
      // Fetch roles for the user
      $userRoles = DB::table("assign_role")
        ->select(
          'assign_role.id',
          'role.id as role_id',
          'role.title as role_title'
        )
        ->join('role', 'role.id', '=', 'assign_role.role_id')
        ->where('assign_role.user_id', '=', $user->id)
        ->get()
        ->toArray();

      // Remove the user if they have roles with IDs 1, 2, or 4
      $roleIds = array_column($userRoles, 'role_id');
      if (array_intersect($roleIds, [1, 2, 4])) {
        unset($data[$key]);
      } else {
        // Assign roles to the user or set an empty array if no roles exist
        $data[$key]->role = $userRoles ? $userRoles : [];
      }
    }

    // Re-index the array to ensure correct JSON structure
    $data = array_values($data);

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  function getCustomersData()
  {
    $filterStartDate = Carbon::parse(now()->subDays(6))->startOfDay();
    $filterEndDate = Carbon::parse(now())->endOfDay();

    $data = DB::table("users")
      ->select(
        'users.id',
        'users.email',
        'users.phone',
        'users.name',
        'users.wallet_amount',
        'users.created_at',
        'users.updated_at',
        'images.id as image_id',
        'images.image'
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'users.id')
          ->where('images.table_name', '=', 'users')
          ->where('images.image_type', '=', 1);
      })
      ->whereNotNull('users.name') // Ensure name is not null
      ->whereNotNull('users.phone') // Ensure phone is not null
      ->orderBy('users.created_at', 'DESC')
      ->get()
      ->toArray(); // Convert Collection to array

    foreach ($data as $key => $user) {
      // Fetch roles for the user
      $userRoles = DB::table("assign_role")
        ->select(
          'assign_role.id',
          'role.id as role_id',
          'role.title as role_title'
        )
        ->join('role', 'role.id', '=', 'assign_role.role_id')
        ->where('assign_role.user_id', '=', $user->id)
        ->get()
        ->toArray();

      // Remove the user if they have roles with IDs 1, 2, or 4
      $roleIds = array_column($userRoles, 'role_id');
      if (array_intersect($roleIds, [1, 2, 4])) {
        unset($data[$key]);
      } else {
        // Assign roles to the user or set an empty array if no roles exist
        $data[$key]->role = $userRoles ? $userRoles : [];
      }
    }

    // Re-index the array to ensure correct JSON structure
    $data = array_values($data);
    $activeUsers = [];
    $inactiveUsers = [];

    foreach ($data as $user) {
      $orders = DB::table('orders')
        ->where('orders.user_id', $user->id)
        ->get()
        ->filter(function ($order) use ($filterStartDate, $filterEndDate) {
          $startDate = $order->start_date ? Carbon::parse($order->start_date) : null;
          $createdAt = Carbon::parse($order->created_at);

          $pauseDates = $order->pause_dates
            ? array_map('trim', explode(',', trim($order->pause_dates, '[]')))
            : [];

          for ($date = $filterStartDate->copy(); $date->lte($filterEndDate); $date->addDay()) {
            $selectedDate = $date->copy();

            if (is_null($order->subscription_type)) {
              if (is_null($order->start_date)) {
                if ($createdAt->copy()->addDay()->isSameDay($selectedDate)) {
                  return true;
                }
              } else {
                if ($startDate->isSameDay($selectedDate)) {
                  return true;
                }
              }
            } else {
              $newEndDate = $this->calculateEndDate($startDate, $order, $pauseDates);

              if ($selectedDate->lte($newEndDate)) {
                switch ($order->subscription_type) {
                  case '1': // One-Time
                  case '2': // Weekly
                  case '3': // Monthly
                  case '4': // Alternate Days
                    return true;
                }
              }
            }
          }

          return false;
        });

      if ($orders->isNotEmpty()) {
        $activeUsers[] = $user;
      } else {
        $inactiveUsers[] = $user;
      }
    }

    return response([
      "response" => 200,
      "data" => [
        "all" => $data,
        "active" => array_values($activeUsers),
        "inactive" => array_values($inactiveUsers),
      ]
    ], 200);
  }


  private static function calculateEndDate($startDate, $order, $pauseDates = [])
  {
    $startDate = Carbon::parse($startDate);
    $pausedaysDifference = count($pauseDates);

    switch ($order->subscription_type) {
      case 2: // Weekly
        $weekdayCount = 0;
        $tempStartDate = $startDate->copy();

        $selectedDaysJson = $order->selected_days_for_weekly;
        $selectedDaysJson = preg_replace('/(\w+):/', '"$1":', $selectedDaysJson);
        $selectedDays = is_string($selectedDaysJson) ? json_decode($selectedDaysJson, true) : $selectedDaysJson;

        $selectedDayCodes = array_map(function ($item) {
          return (string)($item['dayCode'] === 0 ? 7 : $item['dayCode']);
        }, $selectedDays);

        // Add 6 valid delivery days
        while ($weekdayCount < 6) {
          $tempStartDate->addDay();
          $dayCode = $tempStartDate->dayOfWeekIso;
          if (in_array($dayCode, $selectedDayCodes)) {
            $weekdayCount++;
          }
        }

        // Add paused days to the end date
        if ($pausedaysDifference > 0) {
          $additionalDaysAdded = 0;
          while ($additionalDaysAdded < $pausedaysDifference) {
            $tempStartDate->addDay();
            $dayCode = $tempStartDate->dayOfWeekIso;
            if (in_array($dayCode, $selectedDayCodes)) {
              $additionalDaysAdded++;
            }
          }
        }

        return $tempStartDate;

      case 3: // Monthly
        return $startDate->addDays(29 + $pausedaysDifference);
      case 4: // Alternate Days
        return $startDate->addDays(28 + ($pausedaysDifference * 2));

      default: // One-Time or others
        return $startDate;
    }
  }


  function updatePassword(Request $request)
  {
    $initialCheck = false;
    $validator = Validator::make(request()->all(), [
      'user_id' => 'required',
      'password' => 'required'
    ]);
    if ($validator->fails())
      $initialCheck = true;

    if ($initialCheck)
      return response(["response" => 400], 400);
    else {
      try {
        $timeStamp = date("Y-m-d H:i:s");
        $userDetailsModel = User::where("id", $request->user_id)->first();

        $userDetailsModel->password = Hash::Make($request->password);

        $qResponce = $userDetailsModel->save();
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
          'message' => "error $e",
        ];
        return response($response, 200);
      }
    }
  }
  // function updateDetails(Request $request){
  //   $initialCheck=false;
  //   $validator = Validator::make(request()->all(), [
  //     'id'=>'required'
  // ]);
  // if ($validator->fails())
  // $initialCheck=true;
  //   if ($initialCheck)
  //       return response (["response"=>400],400);
  //         else{
  //          try{
  //             $timeStamp= date("Y-m-d H:i:s");
  //             $dataModel= User::where("id",$request->id)->first();
  //             if(isset($request->phone))
  //            {   $alreadyAddedModel= User::where("phone",$request->phone)->where('id',"!=",$request->id)->first();

  //               if($alreadyAddedModel)
  //               {
  //                 $response = [
  //                   "response"=>201,
  //                   'status'=>false,
  //                   'message' => "phone number already exists"];
  //                   return response($response, 200);
  //               }}

  //             if(isset($request->email))
  //             {
  //               $alreadyAddedModel= User::where("email",$request->email)->where('id',"!=",$request->id)->first();
  //               if($alreadyAddedModel)
  //               {
  //                 $response = [
  //                   "response"=>201,
  //                   'status'=>false,
  //                   'message' => "email id already exists"];
  //                   return response($response, 200);
  //               }
  //             }
  //             if(isset($request->name ))
  //             $dataModel->name = $request->name ;
  //             if(isset($request->wallet_amount ))
  //             $dataModel->wallet_amount = $request->wallet_amount ;
  //             if(isset($request->email ))
  //             $dataModel->email = $request->email ;
  //             if(isset($request->phone ))
  //             $dataModel->phone = $request->phone ;
  //             if(isset($request->fcm ))
  //             $dataModel->fcm = $request->fcm ;


  //                $dataModel->updated_at=$timeStamp;


  //        $qResponce= $dataModel->save();

  //         if($qResponce)
  //      {   

  //       // $imageFile=isset($request->image)?$request->image:null;
  //       // $imageId=isset($request->image_id)?$request->image_id:null;

  //       // app('App\Http\Controllers\ImageCountController')->uploadImage($imageFile, "buses", $userDetailsModel->id,1,$imageId);
  //       // //1=profile_image  

  //       $response = [
  //               "response"=>200,
  //               'status'=>true,
  //               'message' => "successfully",

  //           ];}
  //           else 
  //           $response = [
  //             "response"=>201,
  //             'status'=>false,
  //             'message' => "error",

  //         ];
  //         return response($response, 200);





  //           }
  //           catch(\Exception $e){
  //             $response = [
  //               "response"=>201,
  //               'status'=>false,
  //               'message' => "error $e",
  //           ];
  //           return response($response, 200);
  //           }


  //         } 



  //     }

  private function generatePaymentId()
  {
    return 'txn_' . date('YmdHis'); // Format: txn_YYYYMMDDHHMMSS
  }

  function updateDetails(Request $request)
  {
    $initialCheck = false;

    // Validate that `id` is provided
    $validator = Validator::make($request->all(), [
      'id' => 'required'
    ]);

    if ($validator->fails()) {
      $initialCheck = true;
    }

    if ($initialCheck) {
      return response(["response" => 400], 400);
    } else {
      try {
        $timeStamp = date("Y-m-d H:i:s");

        // Retrieve the user
        $dataModel = User::where("id", $request->id)->first();

        // Check for phone uniqueness
        if (isset($request->phone)) {
          $alreadyAddedModel = User::where("phone", $request->phone)
            ->where('id', "!=", $request->id)->first();

          if ($alreadyAddedModel) {
            return response([
              "response" => 201,
              'status' => false,
              'message' => "phone number already exists"
            ], 200);
          }
        }

        // Check for email uniqueness
        if (isset($request->email)) {
          $alreadyAddedModel = User::where("email", $request->email)
            ->where('id', "!=", $request->id)->first();

          if ($alreadyAddedModel) {
            return response([
              "response" => 201,
              'status' => false,
              'message' => "email id already exists"
            ], 200);
          }
        }

        // Update name if provided
        if (isset($request->name)) {
          $dataModel->name = $request->name;
        }

        // Get the previous wallet amount for comparison
        $previousWalletAmount = $dataModel->wallet_amount;

        // Update wallet amount if provided and log the transaction if there's a change
        if (isset($request->wallet_amount) && $request->isFromAdmin) {
          $newWalletAmount = $request->wallet_amount;
          $dataModel->wallet_amount = $newWalletAmount;

          // Calculate the difference and log transaction if there's a change
          if ($newWalletAmount != $previousWalletAmount) {
            $difference = $newWalletAmount - $previousWalletAmount;
            $formatted_difference = number_format(round($difference, 2), 2);

            $description = "Wallet updated from $previousWalletAmount to $newWalletAmount, difference: " . ($difference > 0 ? '+' : '') . $formatted_difference;

            // Create the transaction directly
            $transaction = new TransactionsModel;
            $transaction->user_id = $request->id;
            $transaction->amount = $request->wallet_amount;
            $transaction->type = $difference >= 0 ? 1 : 2;
            $transaction->description = $description;
            $transaction->payment_id = $transaction->payment_id ?? $this->generatePaymentId();
            $transaction->created_at = $timeStamp;
            $transaction->updated_at = $timeStamp;

            // Save transaction and check response
            if (!$transaction->save()) {
              return response([
                "response" => 201,
                'status' => false,
                'message' => "Transaction failed to save"
              ], 200);
            }
          }
        } else {
          if (isset($request->wallet_amount)) {
            $dataModel->wallet_amount = $request->wallet_amount;
          }
        }

        // Update additional fields
        if (isset($request->email)) {
          $dataModel->email = $request->email;
        }
        if (isset($request->phone)) {
          $dataModel->phone = $request->phone;
        }
        if (isset($request->fcm)) {
          $dataModel->fcm = $request->fcm;
        }

        // Update timestamp
        $dataModel->updated_at = $timeStamp;

        // Save user data
        $qResponse = $dataModel->save();

        if ($qResponse) {
          return response([
            "response" => 200,
            'status' => true,
            'message' => "User details updated successfully"
          ], 200);
        } else {
          return response([
            "response" => 201,
            'status' => false,
            'message' => "Failed to update user details"
          ], 200);
        }
      } catch (\Exception $e) {
        return response([
          "response" => 201,
          'status' => false,
          'message' => "error: " . $e->getMessage()
        ], 200);
      }
    }
  }

  function deleteAssignData(Request $request)
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
      try {
        $timeStamp = date("Y-m-d H:i:s");
        $dataModel = AssignModel::where("id", $request->id)->first();


        $qResponce = $dataModel->delete();
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
  function addAssignData(Request $request)
  {

    $validator = Validator::make(request()->all(), [
      'user_id' => 'required',
      'role_id' => 'required'
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {

      $alreadyExists = AssignModel::where('user_id', '=', $request->user_id)->where('role_id', '=', $request->role_id)->first();
      if ($alreadyExists === null) {
        try {
          $timeStamp = date("Y-m-d H:i:s");
          $dataModel = new AssignModel;
          $dataModel->user_id = $request->user_id;
          $dataModel->role_id = $request->role_id;
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
          'message' => "Already assigned to same role"
        ];
        return response($response, 200);
      }
    }
  }

  private function mapStatus($status)
  {
    // Handle null or unexpected status values
    if ($status === null || strtolower($status) === 'active') {
      return 0; // Active
    } elseif (strtolower($status) === 'inactive') {
      return 1; // Inactive
    }

    // Default to inactive if the value is unrecognized
    return 1; // Inactive
  }

  public function importUsers(Request $request)
  {
    // Validate the request for the CSV file
    $validator = Validator::make($request->all(), [
      'csv_file' => 'required|file|mimes:csv,txt,application/csv,text/plain,application/vnd.ms-excel',
    ]);

    if ($validator->fails()) {
      return response()->json(['error' => $validator->errors()], 422);
    }

    $file = $request->file('csv_file');
    $path = $file->getRealPath();
    $csvData = array_map('str_getcsv', file($path));
    $header = array_map('trim', $csvData[0]);
    unset($csvData[0]); // Remove header row

    // Define column mapping
    $columnMapping = [
      'UID' => 'UID',
      'Name' => 'Name',
      'Phone Number' => 'Phone Number',
      'Email' => 'Email',
      'City Name' => 'City Name',
      'Wallet Amount' => 'Wallet Amount',
      'Status' => 'Status',
      'Location' => 'Location', // Added for pincode and landmark extraction
    ];

    $errorMessages = []; // To collect any error messages

    try {
      DB::beginTransaction();

      foreach ($csvData as $row) {
        $data = array_combine($header, $row);

        $statusValue = isset($data[$columnMapping['Status']]) ? $data[$columnMapping['Status']] : null;

        // Check if the phone number already exists
        $existingUserByPhone = User::where('phone', $data[$columnMapping['Phone Number']])->first();

        if ($existingUserByPhone) {
          // If the phone number exists, log an error and skip this entry
          $errorMessages[] = "Phone number {$data[$columnMapping['Phone Number']]} already exists and cannot be duplicated.";
          continue;
        }

        // Check if an email exists with a different phone
        // $existingUserByEmail = User::where('email', $data[$columnMapping['Email']])->first();

        // Create a new user if email doesn't exist
        $user = User::create([
          'uid' => $data[$columnMapping['UID']] ?? null,
          'name' => $data[$columnMapping['Name']] ?? null,
          'phone' => $data[$columnMapping['Phone Number']] ?? null,
          'email' => $data[$columnMapping['Email']] ?? null,
          'wallet_amount' => $data[$columnMapping['Wallet Amount']] ?? 0,
          'status' => $this->mapStatus($statusValue),
          'isotpverified' => 1,
          'created_at' => Carbon::now(),
          'updated_at' => Carbon::now(),
        ]);

        // Extract pincode and landmark from location column
        $location = $data[$columnMapping['Location']] ?? 'null - null';
        [$pincode, $landmark] = array_map('trim', explode('-', $location) + [null, null]);

        // Handle invalid pincode and landmark
        $pincode = ($pincode !== 'null' && $pincode) ? $pincode : null;
        $landmark = ($landmark !== 'null' && $landmark) ? $landmark : null;

        // Insert or update the user_address table
        AddressModel::updateOrCreate(
          ['user_id' => $user->id],
          [
            'name' => $data[$columnMapping['Name']] ?? null,
            's_phone' => $data[$columnMapping['Phone Number']] ?? null,
            'city' => $data[$columnMapping['City Name']] ?? null,
            'landmark' => $landmark,
            'pincode' => $pincode,
            'area' => $data[$columnMapping['City Name']] ?? null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
          ]
        );

        // Insert or update the assign_role table
        AssignModel::updateOrCreate(
          ['user_id' => $user->id],
          [
            'role_id' => 2, // Assuming a default role_id for this example
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
          ]
        );
      }

      DB::commit();

      $responseMessage = ['success' => 'CSV imported successfully'];
      if (!empty($errorMessages)) {
        $responseMessage['errors'] = $errorMessages;
      }

      return response()->json($responseMessage);
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json(['error' => $e->getMessage()], 500);
    }
  }


  public function checkUserExists(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'phone' => 'required',
    ]);

    if ($validator->fails()) {
      return response([
        "response" => 400,
        "status" => false,
        "message" => "Phone number is required"
      ], 400);
    }

    try {
      // Check if the phone number exists with a name already filled
      $existingUser = User::where('phone', $request->phone)
        ->whereNotNull('name')
        ->first();

      if ($existingUser) {
        // Phone number already exists
        return response([
          "response" => 200,
          "status" => false,
          "message" => "Phone number already exists"
        ], 200);
      }

      // Phone number does not exist
      return response([
        "response" => 200,
        "status" => true,
        "message" => "Phone number does not exist"
      ], 200);
    } catch (\Exception $e) {
      return response([
        "response" => 500,
        "status" => false,
        "message" => "An error occurred while checking the phone number"
      ], 500);
    }
  }



  function updateDetailsByPheNumber(Request $request)
  {
    $initialCheck = false;
    $validator = Validator::make(request()->all(), [
      'phone' => 'required'
    ]);
    if ($validator->fails())
      $initialCheck = true;
    if ($initialCheck)
      return response(["response" => 400], 400);
    else {
      try {
        $timeStamp = date("Y-m-d H:i:s");
        $dataModel = User::where("phone", $request->phone)->first();
        if (isset($request->phone)) {
          $alreadyAddedModel = User::where("phone", $request->phone)->where('id', "!=", $request->id)->whereNotNull('name')->first();

          if ($alreadyAddedModel) {
            $response = [
              "response" => 201,
              'status' => false,
              'message' => "phone number already exists"
            ];
            return response($response, 200);
          }
        }

        if (isset($request->email)) {
          $alreadyAddedModel = User::where("email", $request->email)->where('id', "!=", $request->id)->first();
          if ($alreadyAddedModel) {
            $response = [
              "response" => 201,
              'status' => false,
              'message' => "email id already exists"
            ];
            return response($response, 200);
          }
        }
        if (isset($request->name))
          $dataModel->name = $request->name;
        if (isset($request->wallet_amount))
          $dataModel->wallet_amount = $request->wallet_amount;
        if (isset($request->email))
          $dataModel->email = $request->email;
        if (isset($request->phone))
          $dataModel->phone = $request->phone;
        if (isset($request->fcm))
          $dataModel->fcm = $request->fcm;


        $dataModel->updated_at = $timeStamp;


        $qResponce = $dataModel->save();

        if ($qResponce) {

          // $imageFile=isset($request->image)?$request->image:null;
          // $imageId=isset($request->image_id)?$request->image_id:null;

          // app('App\Http\Controllers\ImageCountController')->uploadImage($imageFile, "buses", $userDetailsModel->id,1,$imageId);
          // //1=profile_image  

          $assignRequest = request()->merge([
            'user_id' => $dataModel->id,
            'role_id' => 3   //User Role ID
          ]);

          $assignResponse = $this->addAssignData($assignRequest);

          // Handle the response and throw an error if the role assignment fails
          if ($assignResponse->getStatusCode() !== 200) {
            throw new \Exception("Failed to assign role: " . $assignResponse->getContent());
          }
          if (isset($request->referral_code)) {
            $referralCode = $request->referral_code;
            $result = ReferralController::useReferralCode($referralCode, $dataModel->id);
            if (!$result['success']) {
              Log::warning('Referral not applied: ' . $referralCode . ' ' . $result['message']);
            }
          }
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
          'message' => "error $e",
        ];
        return response($response, 200);
      }
    }
  }
}
