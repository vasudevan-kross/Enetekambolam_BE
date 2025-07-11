<?php

namespace App\Http\Controllers;
use App\Models\TestimonialModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ImageModel;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    function uploadImage(Request $request){
        $validator = Validator::make(request()->all(), [
          'image' => 'required',
          'id' => 'required',
          'image_type' => 'required'
    ]);
        
       if ($validator->fails())
        return response (["response"=>400],400);
          else
             {
               $alreadyExists=null;
              if($request->image_type==1){
                $alreadyExists = ImageModel::where('table_id', '=', $request->id)->where('table_name',"=","testimonials")->where('image_type',"=","1")->first();
              }
          
      
        
         if ($alreadyExists == null)
            {
                 try{
                  
                        $image=$request->image;
                        $newName=rand().'.'.$image->getClientOriginalExtension();
                        $image->move(public_path('/uploads/images'),$newName);
                        $timeStamp= date("Y-m-d H:i:s");
                        $dataModel=new ImageModel;
                        $dataModel->table_name= "testimonials";
                        $dataModel->table_id= $request->id;
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
                          'message' => "error",
                  
                      ];
                      return response($response, 200);
                      }
                    }  else {
                      $response = [
                        "response"=>201,
                        'status'=>false,
                        'message' => "image already exists"];
                        return response($response, 200);
                    }
            
            
                }
            
        }
    function deleteImage(Request $request){
      
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
                  $dataModel= ImageModel::where("id",$request->id)->first();
                               
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
                  $dataModel= TestimonialModel::where("id",$request->id)->first();
                               
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
    function updateDetails(Request $request){
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
                  $dataModel= TestimonialModel::where("id",$request->id)->first();
            
                  if(isset($request->title ))
                  $dataModel->title = $request->title ;
                  if(isset($request->sub_title ))
                  $dataModel->sub_title = $request->sub_title ;
                  if(isset($request->rating ))
                  $dataModel->rating = $request->rating ;
                  if(isset($request->description ))
                  $dataModel->description = $request->description ;
                     $dataModel->updated_at=$timeStamp;
                
               
             $qResponce= $dataModel->save();
       
              if($qResponce)
           {   
      
            
            $response = [
                    "response"=>200,
                    'status'=>true,
                    'message' => "successfully",
          
                ];}
                else 
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
                    'message' => "error $e",
                ];
                return response($response, 200);
                }
          
                   
              } 
              
        
        
          }
    function getData()
    {

      $data = DB::table("testimonials")
      ->select('testimonials.*',
      'images.id as image_id',
      'images.image' 
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'testimonials.id')
        ->where('images.table_name','=',"testimonials")
        ->where('images.image_type','=',1);
        })
        ->orderBy('created_at','DESC')
        ->get();
      
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
        }
    
    function getDataById($id)
    {

      $data = DB::table("testimonials")
      ->select('testimonials.*',
          'images.id as image_id',
      'images.image' 
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'testimonials.id')
        ->where('images.table_name','=',"testimonials")
        ->where('images.image_type','=',1);
        })
       ->where("testimonials.id","=",$id)
        ->first();
      
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
  
    function addData(Request $request)
    {
       
        
        $validator = Validator::make(request()->all(), [
            'title' => 'required',
            'sub_title' => 'required',
            'rating' => 'required',
            'description' => 'required'
            
    ]);
  
    if ($validator->fails())
      return response (["response"=>400],400);
        else{
        
                  try{
                    $timeStamp= date("Y-m-d H:i:s");
                    $dataModel=new TestimonialModel;
                    
                    $dataModel->title = $request->title ;
                    $dataModel->sub_title = $request->sub_title;
                    $dataModel->rating  = $request->rating;
                    $dataModel->description = $request->description;
      
                 
                    $dataModel->created_at=$timeStamp;
                    $dataModel->updated_at=$timeStamp;
                    $qResponce= $dataModel->save();
                       if($qResponce){
                    
                       $response = [
                             "response"=>200,
                             'status'=>true,
                             'message' => "successfully",
                             "id"=>$dataModel->id
                   
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
