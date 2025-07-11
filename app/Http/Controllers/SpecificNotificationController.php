<?php

namespace App\Http\Controllers;
use App\Models\UserNotificationModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ImageModel;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SpecificNotificationModel;


class SpecificNotificationController extends Controller
{
        
    function getDataByUId($id)
    {
      $data = DB::table("specific_notification")
      ->select('specific_notification.*'
      )
       ->where("specific_notification.user_id","=",$id)
       ->orderBy('specific_notification.created_at','DESC')
       ->get();
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }


    function getDataAllNoti()
    {

      $data = DB::table("specific_notification")
      ->select('specific_notification.*'
      )
        ->orderBy('specific_notification.created_at','DESC')
        ->get();
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
 

 
}
