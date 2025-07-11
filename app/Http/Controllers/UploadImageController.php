<?php

namespace App\Http\Controllers;
use App\Models\TestimonialModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ImageModel;
use Illuminate\Http\Request;

class UploadImageController extends Controller
{
    function uploadImageOnly(Request $request){
        $validator = Validator::make(request()->all(), [
          'image' => 'required'
    ]);
        
       if ($validator->fails())
        return response (["response"=>400],400);
          else
             {
     
                 try{
                  
                        $image=$request->image;
                        $newName=rand().'.'.$image->getClientOriginalExtension();
                        $image->move(public_path('/uploads/images'),$newName);
    
                           $response = [
                                 "response"=>200,
                                 'status'=>true,
                                 'file'=>$newName,
                                 'message' => "successfully"
                       
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