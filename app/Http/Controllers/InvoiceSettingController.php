<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InvoiceSettingModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;



class InvoiceSettingController extends Controller
{
  
    function updateData(Request $request){
      
        $initialCheck=false;
        $validator = Validator::make(request()->all(), [
          'id'=>'required',
      ]);
      if ($validator->fails())
      $initialCheck=true;
        if ($initialCheck)
            return response (["response"=>400],400);
              else{
                $timeStamp= date("Y-m-d H:i:s");
               try{
                  $dataModel= InvoiceSettingModel::where("id",$request->id)->first();
                  if(isset($request->value))
                  $dataModel->value=$request->value;
                  $dataModel->updated_at=$timeStamp;
                  
             $qResponce= $dataModel->save();
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

    function getDataDataById($id)
    {

      $data = DB::table("invoice_setting")
      ->select('invoice_setting.*')
      ->orderBy("invoice_setting.created_at","ASC")
      ->where("invoice_setting.id","=",$id)
        ->first();
      
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
    
    function getDataAllData()
    {

      $data = DB::table("invoice_setting")
      ->select('invoice_setting.*')
      ->orderBy("invoice_setting.created_at","ASC")
        ->get();
      
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
}