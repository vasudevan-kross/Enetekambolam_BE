<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AvailableDeliveryLocationModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class AvailableDeliveryLocationController extends Controller
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
                  $dataModel= AvailableDeliveryLocationModel::where("id",$request->id)->first();
                               
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

    function getDataAllData()
    {

      $data = DB::table("available_delivery_location")
      ->select('available_delivery_location.*')
      ->orderBy("available_delivery_location.created_at","ASC")
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
            'title' => 'required'
    ]);
        
    if ($validator->fails())
      return response (["response"=>400],400);
        else{
        
               $alreadyExists = AvailableDeliveryLocationModel::where('title', '=', $request->title)->first();
                if ($alreadyExists === null) {
                  try{
                    $timeStamp= date("Y-m-d H:i:s");
                    $dataModel=new AvailableDeliveryLocationModel;
                    $dataModel->title = $request->title;
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
                    'message' => "title already exists"];
                    return response($response, 200);
                }
      
       }
       
      }

 
}