<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SocialMediaModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class SocialMediaController extends Controller
{

    function getDataAllData()
    {

      $data = DB::table("social_media")
      ->select('social_media.*')
      ->orderBy("social_media.created_at","DESC")
        ->get();
      
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }

    function delete(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:social_media,id',
        ]);

        try {
            DB::table('social_media')->where('id', $validated['id'])->delete();

            return response([
                'response' => 200,
                'message' => 'Record deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response([
                'response' => 500,
                'message' => 'Failed to delete the record. Please try again.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    
    function addData(Request $request)
    {
        
        $validator = Validator::make(request()->all(), [
            'title' => 'required',
            'image' => 'required',
            'url' => 'required'
    ]);
        
    if ($validator->fails())
      return response (["response"=>400],400);
        else{
        
               $alreadyExists = SocialMediaModel::where('title', '=', $request->title)->first();
                if ($alreadyExists === null) {
                  try{
                    $timeStamp= date("Y-m-d H:i:s");
                    $dataModel=new SocialMediaModel;
                    $dataModel->title = $request->title;
                    $dataModel->image = $request->image;
                    $dataModel->url = $request->url;
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