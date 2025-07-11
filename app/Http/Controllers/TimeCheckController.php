<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TimeCheckController extends Controller
{
    public function checkTimeAvailability()
    {
       // date_default_timezone_set('YOUR_TIMEZONE'); // Set your timezone here

        $currentTime = now();
        $startTime = $currentTime->copy()->setTime(10, 0, 0);
        $endTime = $currentTime->copy()->setTime(22, 0, 0);

        $isAvailable = $currentTime->between($startTime, $endTime);
       
         $data=[
             'available'=>$isAvailable,
                    'msg'=>"Changes are only allowed between 10 AM to 10 PM."];
                $response = [
                    "response"=>200,
                      'data'=>$data
                    
                ];
              return response($response, 200);
    }
}
