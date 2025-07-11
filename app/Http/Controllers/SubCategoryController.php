<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubCategoryModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ImageModel;

class SubCategoryController extends Controller
{
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
            $alreadyExists = ImageModel::where('table_id', '=', $request->id)->where('table_name',"=","sub_cat")->where('image_type',"=","1")->first();
          }
      
  
    
     if ($alreadyExists == null)
        {
             try{
              
                    $image=$request->image;
                    $newName=rand().'.'.$image->getClientOriginalExtension();
                    $image->move(public_path('/uploads/images'),$newName);
                    $timeStamp= date("Y-m-d H:i:s");
                    $dataModel=new ImageModel;
                    $dataModel->table_name= "sub_cat";
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
                      'message' => "error $e",
              
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
                  $dataModel= SubCategoryModel::where("id",$request->id)->first();
              
                               
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
    function getDataByCatId($id)
    {

      $data = DB::table("sub_cat")
      ->select('sub_cat.*','cat.title as cat_title',
      'images.id as image_id',
      'images.image' 
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'sub_cat.id')
        ->where('images.table_name','=',"sub_cat")
        ->where('images.image_type','=',1);
        })
       ->join('cat','cat.id','=','sub_cat.cat_id')
       ->where("cat.id","=",$id)
       ->orderBy('sub_cat.created_at','ASC')
        ->get();
      
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
    function getDataById($id)
    {

      $data = DB::table("sub_cat")
      ->select('sub_cat.*','cat.title as cat_title',
      'images.id as image_id',
      'images.image' 
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'sub_cat.id')
        ->where('images.table_name','=',"sub_cat")
        ->where('images.image_type','=',1);
        })
       ->join('cat','cat.id','=','sub_cat.cat_id')
       ->where('sub_cat.id','=',$id)
       ->orderBy('sub_cat.created_at','ASC')
        ->first();
        if( $data!=null){
          $data->slider_image=DB::table("images")
          ->select('images.id',
          'images.image'
          )
          ->where("images.table_id","=",$id)
          ->where("images.table_name","=","sub_cat")
          ->where('images.image_type','=',2)
          ->get();
        }
      
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
      return response($response, 200);
    }

    function getData()
    {

      $data = DB::table("sub_cat")
      ->select('sub_cat.*','cat.title as cat_title',
      'images.id as image_id',
      'images.image' 
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'sub_cat.id')
        ->where('images.table_name','=',"sub_cat")
        ->where('images.image_type','=',1);
        })
       ->join('cat','cat.id','=','sub_cat.cat_id')
       ->orderBy('sub_cat.created_at','DESC')
        ->get();
      
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
      return response($response, 200);
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
                  $dataModel= SubCategoryModel::where("id",$request->id)->first();
            
                
                    $alreadyExists = SubCategoryModel::where('title', '=', $request->title)->where('id',"!=",$request->id)->first();
                  
  
               if ($alreadyExists == null)
                  {
                  if(isset($request->title ))
                  $dataModel->title = $request->title ;
           
                     $dataModel->updated_at=$timeStamp;
                
               
             $qResponce= $dataModel->save();
       
              if($qResponce)
           {   
      
            // $imageFile=isset($request->image)?$request->image:null;
            // $imageId=isset($request->image_id)?$request->image_id:null;
         
            // app('App\Http\Controllers\ImageCountController')->uploadImage($imageFile, "buses", $userDetailsModel->id,1,$imageId);
            // //1=profile_image  
            
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
                 else {
                   $response = [
                     "response"=>201,
                     'status'=>false,
                     'message' => "title already exists"];
                     return response($response, 200);
                 }
         
            
           
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
    function addData(Request $request)
    {
        
        $validator = Validator::make(request()->all(), [
            'cat_id' => 'required',
          'title' => 'required'
    ]);
        
    if ($validator->fails())
      return response (["response"=>400],400);
        else{
        
               $alreadyExists = SubCategoryModel::where('title', '=', $request->title)->first();
                if ($alreadyExists === null) {
                  try{
                    $timeStamp= date("Y-m-d H:i:s");
                    $dataModel=new SubCategoryModel;
                    $dataModel->title = $request->title;
                    $dataModel->cat_id = $request->cat_id; 
                    $dataModel->created_at=$timeStamp;
                    $dataModel->updated_at=$timeStamp;
                    $qResponce= $dataModel->save();
                       if($qResponce){
                        if(isset($request->image)){
                          if($request->hasFile('image'))
                          app('App\Http\Controllers\ImageCountController')->uploadImage($request->image, "cat", $dataModel->id,1,null);
                       //1=profile_image
                        }
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
