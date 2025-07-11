<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class ForgotPasswordController extends Controller
{
   
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    // use SendsPasswordResetEmails;

      public function ForgetPasswordStore(Request $request) {
          $request->validate([
              'email' => 'required|email',
              'base_url'=>'required'
          ]);

   $dataUser = DB::table("users")
    ->select(
      'users.email'
    )
    ->where("users.email","=",$request->email)
      ->first();
    
      if($dataUser!=null){
        $token = Str::random(64);
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);
        Mail::send('auth.forget-password-email', ['token' => $token,'base_url'=>$request->base_url], function($message) use($request){
            $message->to($request->email);
            $message->from(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
            $message->subject('Reset Password');
        });
        return response([
          "response"=>200,
          "status"=>true,
          'token' => $token,
          'message' => 'We have emailed your password reset link!'
      ], 200);
      }
      else{
        return response([
            "response"=>201,
            'status'=>false,
            'message' => 'No user found!'
        ], 200);
      }
       
    }
       
  
      
      public function ResetPasswordStore(Request $request) {
          $request->validate([
            //   'email' => 'required|email',
              'password' => 'required|string|min:8|confirmed',
              'password_confirmation' => 'required',
              'token' => 'required'
          ]);
          try{
            $update = DB::table('password_resets')->where(['token' => $request->token])->first();
  
            if(!$update){
              return response([
                  "response"=>201,
                  'status'=>false,
                  'message' => 'Something went wrong'
              ], 200);
            }
    
            $user = User::where('email', $update->email)->update(['password' => Hash::make($request->password)]);
    
            // Delete password_resets record
            DB::table('password_resets')->where(['email'=> $request->email])->delete();
    
                return response([
              "response"=>200,
              'status'=>true,
              'message' => 'Password updated successfully!'
          ], 200);
          }catch(\Exception $e){
            return response([
                "response"=>201,
                'status'=>false,
                'message' => 'Something went wrong'
            ], 200);
          }
        }
        
  }