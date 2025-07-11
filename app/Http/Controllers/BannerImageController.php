<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\BannerImageModel;
use Illuminate\Support\Facades\DB;

class BannerImageController extends Controller
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
              $dataModel= BannerImageModel::where("id",$request->id)->first();
          
                           
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
    function getMobileImageBanner()
    {

      $data = DB::table("banner_image")
      ->select('banner_image.*'  
      )->where('image_type','=',1)
       ->orderBy('banner_image.created_at','ASC')
        ->get();
      
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
    function uploadImage(Request $request){
        $validator = Validator::make(request()->all(), [
          'image' => 'required',
          'image_type' => 'required'
    ]);
        
       if ($validator->fails())
        return response (["response"=>400],400);
          else
             {
    
                 try{
                  
                        $image=$request->image;
                        $newName=rand().'.'.$image->getClientOriginalExtension();
                        $image->move(public_path('/uploads/images'),$newName);
                        $timeStamp= date("Y-m-d H:i:s");
                        $dataModel=new BannerImageModel;

                        $dataModel->image_type= $request->image_type;
                        $dataModel->image= $newName;
                        $dataModel->created_at=$timeStamp;
                        $dataModel->updated_at=$timeStamp;
                        $qResponce= $dataModel->save();
                           if($qResponce){
           
                           $response = [
                                 "response"=>200,
                                 'status'=>true,
                                 'file'=>$newName,
                                 'message' => "successfully"
                       
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
                          'message' => "error $e",
                  
                      ];
                      return response($response, 200);
                      }
             
            
            
                }
            
        }
}
