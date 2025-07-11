<?php

namespace App\Http\Controllers;
use App\Models\AllowPincodeModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class AllowPincodeController extends Controller
{
    function delete(Request $request){
      
        $initialCheck=false;
        $validator = Validator::make(request()->all(), [
          'id'=>'required'
      ]);
      if ($validator->fails())
      $initialCheck=true;
        if ($initialCheck)
            return response (["response"=>400],400);
              else{
               try{
                  $timeStamp= date("Y-m-d H:i:s");
                  $dataModel= AllowPincodeModel::where("id",$request->id)->first();
                               
             $qResponce= $dataModel->delete();
              if($qResponce)
                $response = [
                    "response"=>200,
                    'status'=>true,
                    'message' => "successfully",
          
                ];else 
                $response = [
                  "response"=>201,
                  'status'=>false,
                  'message' => "error",
        
              ];
              return response($response, 200);
                }
                catch(\Exception $e){
                  $response = [
                    "response"=>201,
                    'status'=>false,
                    'message' => "error",
                ];
                return response($response, 200);
                }
          
                   
              }
           
          
    }
    function getDataByPincode($pincode)
    {

      $data = DB::table("allow_pincode")
      ->select('allow_pincode.*')
       ->where("allow_pincode.pin_code","=",$pincode)
        ->first();
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
    function getDataAllPincode()
    {

      $data = DB::table("allow_pincode")
      ->select('allow_pincode.*')
      ->orderBy('created_at','DESC')
        ->get();
      
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
    function addData(Request $request)
    {
        
        $validator = Validator::make(request()->all(), [
            'pin_code' => 'required'
    ]);
        
    if ($validator->fails())
      return response (["response"=>400],400);
        else{
        
               $alreadyExists = AllowPincodeModel::where('pin_code', '=', $request->pin_code)->first();
                if ($alreadyExists === null) {
                  try{
                    $timeStamp= date("Y-m-d H:i:s");
                    $dataModel=new AllowPincodeModel;
                    $dataModel->pin_code = $request->pin_code;
                    $dataModel->created_at=$timeStamp;
                    $dataModel->updated_at=$timeStamp;
                    $qResponce= $dataModel->save();
                       if($qResponce){
                
                       $response = [
                             "response"=>200,
                             'status'=>true,
                             'message' => "successfully",
                             'id' => $dataModel->id
                   
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
                
                else {
                  $response = [
                    "response"=>201,
                    'status'=>false,
                    'message' => "pincode already exists"];
                    return response($response, 200);
                }
      
       }
       
      }

 
}
