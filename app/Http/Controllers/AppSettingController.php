<?php

namespace App\Http\Controllers;
use App\Models\AppSettingModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class AppSettingController extends Controller
{
    function getDataBySettingId($id)
    {

      $data = DB::table("app_settings")
      ->select('app_settings.*')
       ->where("app_settings.setting_id","=",$id)
        ->first();
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
    function getDataAllPages()
    {

      $data = DB::table("app_settings")
      ->select('app_settings.*')
        ->get();
      
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
 
    function updateData(Request $request)
    {
       
        
        $validator = Validator::make(request()->all(), [
            'setting_id' => 'required',
            
    ]);
  
    if ($validator->fails())
      return response (["response"=>400],400);
        else{
        
                  try{
                    $timeStamp= date("Y-m-d H:i:s");
                    $dataModel= AppSettingModel::where("setting_id",$request->setting_id)->first();
        
                    if(isset($request->value)){
                      $dataModel->value  = $request->value;
                    }
                                   
                    $dataModel->updated_at=$timeStamp;
                    $qResponce= $dataModel->save();
                       if($qResponce){
                    
                       $response = [
                             "response"=>200,
                             'status'=>true,
                             'message' => "successfully",
                        
                   
                         ];
                        }else 
                         $response = [
                           "response"=>201,
                           'status'=>false,
                           'message' => "error",
                 
                       ];
                       return response($response, 200);
                               
                  }catch(\Exception $e){
              
                    $response = [
                      "response"=>201,
                      'status'=>false,
                      'message' => "error",
              
                  ];
                  return response($response, 200);
                  }
                
            
      
       }
       
      }
}
