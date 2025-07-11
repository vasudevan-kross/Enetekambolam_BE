<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class OTPController extends Controller
{
    function sendOTP(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'mobile' => 'required',
        ]);

        // Generate a random 6-digit OTP
        $otp = 456789; //rand(100000, 999999);

        $user = User::updateOrCreate(
            ['phone' => $request->mobile],
            [
                'otp' => Hash::make($otp),
                'isotpverified' => false
            ]
        );

        // Prepare the MSG91 API request
        $authKey = env('MSG91_AUTH_KEY');  // Ensure this is set in your .env file
        $senderId = env('MSG91_SENDER_ID'); // Ensure this is set in your .env file
        $route = env('MSG91_ROUTE', '4'); // Default route 4 for transactional messages
        $country = env('MSG91_COUNTRY', '91'); // Default country code (India)

        $message = "Your OTP is $otp. Please do not share it with anyone.";
        $mobile = $request->mobile;

        try {
            // $response = Http::post('https://api.msg91.com/api/v5/otp', [
            //     'authkey' => $authKey,
            //     'mobile' => $country . $mobile,
            //     'message' => $message,
            //     'sender' => $senderId,
            //     'otp' => $otp,
            //     'otp_length' => 6,
            //     'otp_expiry' => 5 // OTP expiry in minutes
            // ]);

            // if ($response->successful()) {
            // OTP sent successfully
            // return response()->json([
            //     'response' => 200,
            //     'status' => true,
            //     'message' => 'OTP sent successfully.'
            // ]);
            $response = [
                "response" => 200,
                'status' => true,
                'message' => 'OTP sent successfully.'
            ];
            return response($response, 200);
            // } else {
            //     // Handle API failure
            //     return response()->json([
            //         'response' => 500,
            //         'status' => false,
            //         'message' => 'Failed to send OTP.'
            //     ], 500);
            // }
        } catch (\Exception $e) {
            // Catch exceptions like network issues, etc.
            return response()->json([
                'response' => 500,
                'status' => false,
                'message' => 'An error occurred while sending the OTP.',
                'error' => $e->getMessage()
            ], 500);
        }
        // Send the OTP via MSG91
        // $response = Http::post('https://api.msg91.com/api/v5/otp', [
        //     'authkey' => $authKey,
        //     'mobile' => $country . $mobile,
        //     'message' => $message,
        //     'sender' => $senderId,
        //     'otp' => $otp,
        //     'otp_length' => 6,
        //     'otp_expiry' => 5 // OTP expiry in minutes
        // ]);

        // $response = Http::post('https://api.msg91.com/api/v5/sms', [
        //     'authkey' => $authKey,
        //     'sender' => $senderId,
        //     'route' => $route,
        //     'country' => $country,
        //     'sms' => [
        //         [
        //             'message' => $message,
        //             'to' => [$mobile]
        //         ]
        //     ]
        // ]);
        // Check response status
        // if ($response->successful()) {
        //   $response = [
        //     "response"=>200,
        //     'status'=>true,
        //     'message' => "OTP sent successfully.",

        // ];
        //      return response($response);
        // } else {
        //     // Log or debug the response for errors
        //     return response()->json(['message' => 'Failed to send OTP.'], 500);
        // }
    }

    // function sendOTP(Request $request)
    // {
    //     $request->validate([
    //         'mobile' => 'required|digits:10',
    //     ]);

    //     $testAccounts = ['9999999999'];
    //     $otp = rand(100000, 999999);

    //     if (in_array($request->mobile, $testAccounts)) {
    //         $tempOtp = 456789;
    //         $user = User::updateOrCreate(
    //             ['phone' => $request->mobile],
    //             [
    //                 'otp' => Hash::make($tempOtp),
    //                 'isotpverified' => false
    //             ]
    //         );
    //         return response([
    //             "response" => 200,
    //             "status" => true,
    //             "message" => "Test OTP generated successfully.",
    //             "otp" => "456789"
    //         ], 200);
    //     }

    //     // Proceed with real OTP flow for other users
    //     $user = User::updateOrCreate(
    //         ['phone' => $request->mobile],
    //         [
    //             'otp' => Hash::make($otp),
    //             'isotpverified' => false
    //         ]
    //     );

    //     $authKey = env('MSG91_AUTH_KEY');
    //     $templateId = env('MSG91_TEMPLATE_ID');
    //     $senderId = env('MSG91_SENDER_ID');
    //     $countryCode = '91';

    //     $apiUrl = 'https://control.msg91.com/api/v5/flow/';

    //     $payload = [
    //         "template_id" => $templateId,
    //         "short_url" => "1",
    //         "recipients" => [
    //             [
    //                 "mobiles" => $countryCode . $request->mobile,
    //                 "number1" => $otp,
    //                 "number2" => "10"
    //             ]
    //         ]
    //     ];

    //     try {
    //         // Send request to MSG91 API
    //         $response = Http::withHeaders([
    //             'authkey' => $authKey,
    //             'Content-Type' => 'application/json'
    //         ])->post($apiUrl, $payload);

    //         if ($response->successful()) {
    //             return response([
    //                 "response" => 200,
    //                 "status" => true,
    //                 "message" => "OTP sent successfully."
    //             ], 200);
    //         } else {
    //             return response([
    //                 "response" => $response->status(),
    //                 "status" => false,
    //                 "message" => "Failed to send OTP."
    //             ], $response->status());
    //         }
    //     } catch (Exception $e) {
    //         return response([
    //             "response" => 500,
    //             "status" => false,
    //             "message" => "An error occurred while sending the OTP.",
    //             "error" => $e->getMessage()
    //         ], 500);
    //     }
    // }


    function verifyOTP(Request $request)
    {
        // Validate input
        $request->validate([
            'mobile' => 'required',
            'otp' => 'required',
        ]);

        // Fetch the user by mobile number
        $user = User::where('phone', $request->mobile)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Verify the OTP
        if (Hash::check($request->otp, $user->otp)) {
            // OTP is correct, update the isotpverified field to true
            $user->isotpverified = true;
            $user->save();

            // Create a request instance to pass to the loginMobile method
            //$loginRequest = new Request();
            //$loginRequest->replace(['phone' => $request->mobile]);
            //$loginRequest->merge(['phone' => $request->mobile]);
            //request()->merge(['phone' => $request->mobile]);

            // Prepare to call the loginMobile method
            //$loginController = new LoginController();
            // Call the loginMobile method
            //return $loginController->loginMobile(request());
            $response = [
                "response" => 200,
                'status' => true,
                'message' => 'OTP verified successfully.'
            ];
            return response($response, 200);
        } else {
            return response()->json(['message' => 'Invalid OTP.'], 401);
        }
    }
}
