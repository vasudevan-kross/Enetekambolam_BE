<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\DeliveryExecutive;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;


class LoginController extends Controller
{
    function checkUserRegMobile(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'phone' => 'required'
        ]);

        if ($validator->fails())
            return response(["response" => 400], 400);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response([
                "response" => 201,
                "status" => false,
                'message' => 'These credentials do not match our records.'
            ], 200);
        } else {
            return response([
                "response" => 201,
                "status" => true,
                'message' => 'User exists'
            ], 200);
        }
    }
    function loginMobile(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'phone' => 'required'
        ]);

        if ($validator->fails())
            return response(["response" => 400], 400);

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response([
                "response" => 200,
                "status" => false,
                'message' => 'These credentials do not match our records. please register',
                'data' => null,

            ], 200);
        }

        $token = $user->createToken('my-app-token')->plainTextToken;

        $user->role = DB::table("assign_role")
            ->select(
                'assign_role.id',
                'role.id as role_id',
                'role.title as role_title',
            )
            ->Join('role', 'role.id', '=', 'assign_role.role_id')
            ->where('assign_role.user_id', '=', $user->id)
            ->get();

        $response = [
            "response" => 200,
            "status" => true,
            'message' => "Successfully",
            'data' => $user,
            'token' => $token,
        ];

        return response($response, 200);
    }
    function login(Request $request)
    {
        $user= User::where('email', $request->email)->first();
 
        if ($user && Hash::check($request->password, $user->password)) {
            $token = $user->createToken('my-app-token')->plainTextToken;
     
            $user->role = DB::table("assign_role")
            ->select(
                'assign_role.id',
            'role.id as role_id',
            'role.title as role_title',
            )
            ->Join('role', 'role.id', '=', 'assign_role.role_id')
            ->where('assign_role.user_id', '=', $user->id)
            ->get();
         
                $response = [
                    "response"=>200,
                    "status"=>true,
                    'message' => "Successfully",
                    'data' => $user,
                    'token' => $token,
                ];
            
                 return response($response, 200);
        }

        // Ensure the 'DeliveryExecutive' model is used instead of raw DB query for fetching the executive data
        $executive = DeliveryExecutive::where('email', $request->email)->first();
        if(!$executive) {
            return response([
                "response"=>201,
                "status"=>false,
                'message' => 'This email does not exist.'
            ], 200);
        }
        $executiveData = DB::table('delivery_executive')
        ->select(
            'delivery_executive.*',
            'delivery_executive.phn_no1 as phone' 
        )
        ->where('email', $request->email)
        ->first();
        if($executiveData->is_active !== 1) {
            return response([
                "response"=>403,
                "status"=>false,
                'message' => 'The user is Inactive.'
            ], 200);
        }
        if($executiveData) {
            if($executiveData->password !== null) {
              // Decrypt the password
              $password = Crypt::decryptString($executiveData->password);
              $isExecutive = DB::table('assign_role')
              ->where('executive_id', $executiveData->executive_id)
              ->where('role_id', 4) // role_id = 4 for executive
              ->exists();
              if ($isExecutive) {
                  $roles = DB::table('assign_role')
              ->select(
                  'assign_role.id',
                  'role.id as role_id',
                  'role.title as role_title'
              )
              ->join('role', 'role.id', '=', 'assign_role.role_id')
              ->where('assign_role.executive_id', $executiveData->executive_id)
              ->get();
               if ($executive && ($password === $request->password)) {
                
                    // Generate token for executive
                    $token = $executive->createToken('my-app-token')->plainTextToken;
                    $executiveData->role = $roles;
        
                    $response = [
                        "response" => 200,
                        "status" => true,
                        'message' => "Executive Login Successful",
                        'data' => $executiveData,
                        'token' => $token,
                    ];
        
                    return response($response, 200);
                }
    
            }
            } 
        }
    // If no match is found 
    return response([
        "response"=>201,
        "status"=>false,
        'message' => 'These credentials do not match our records.'
    ], 200);
    }
  
}
