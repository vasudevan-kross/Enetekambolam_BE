<?php

namespace App\Http\Controllers;
use App\Models\UserHolidayModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class UserHolyDayController extends Controller
{
    function deleteData(Request $request){
      
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
                  $dataModel= UserHolidayModel::where("id",$request->id)->first();
              
                               
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
    function getDataByUserId($id)
    {

      $data = DB::table("user_holiday")
      ->select('user_holiday.*'
      )
       ->where('user_holiday.user_id','=',$id)
 
       ->orderBy('user_holiday.created_at','DESC')
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
            'user_id' => 'required',
            'date' => 'required'
            
    ]);
        
    if ($validator->fails())
      return response (["response"=>400],400);
        else{
        
                           $alreadyExists = UserHolidayModel::where('date', '=', $request->date)->where('user_id', '=', $request->user_id)->first();

                if ($alreadyExists === null) {
                  try{
                    $timeStamp= date("Y-m-d H:i:s");
                    $dataModel=new UserHolidayModel;
                    $dataModel->user_id  = $request->user_id;
                    $dataModel->date = $request->date;                
                
                    $dataModel->created_at=$timeStamp;
                    $dataModel->updated_at=$timeStamp;
                    $qResponce= $dataModel->save();
                       if($qResponce){
                
                       $response = [
                             "response"=>200,
                             'status'=>true,
                             'message' => "successfully",
                             "id"=> $dataModel->id
                   
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
                    'message' => "date already exists"];
                    return response($response, 200);
                }
      
       }
       
      }
}
