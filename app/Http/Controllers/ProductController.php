<?php

namespace App\Http\Controllers;

use App\Models\StockApprovalModel;
use Illuminate\Http\Request;
use App\Models\ProductModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ImageModel;
use App\Models\SubCategoryModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
  function deleteImage(Request $request)
  {

    $initialCheck = false;
    $validator = Validator::make(request()->all(), [
      'id' => 'required'
    ]);
    if ($validator->fails())
      $initialCheck = true;

    if ($initialCheck)
      return response(["response" => 400], 400);
    else {
      try {
        $timeStamp = date("Y-m-d H:i:s");
        $dataModel = ImageModel::where("id", $request->id)->first();

        $qResponce = $dataModel->delete();
        if ($qResponce)
          $response = [
            "response" => 200,
            'status' => true,
            'message' => "successfully",

          ];
        else
          $response = [
            "response" => 201,
            'status' => false,
            'message' => "error",

          ];
        return response($response, 200);
      } catch (\Exception $e) {
        $response = [
          "response" => 201,
          'status' => false,
          'message' => "error",
        ];
        return response($response, 200);
      }
    }
  }
  // function uploadImage(Request $request)
  // {
  //   $validator = Validator::make(request()->all(), [
  //     'image' => 'required',
  //     'id' => 'required',
  //     'image_type' => 'required'
  //   ]);

  //   if ($validator->fails())
  //     return response(["response" => 400], 400);
  //   else {
  //     $alreadyExists = null;
  //     if ($request->image_type == 1) {
  //       $alreadyExists = ImageModel::where('table_id', '=', $request->id)->where('table_name', "=", "product")->where('image_type', "=", "1")->first();
  //     }
  //     if ($alreadyExists == null) {
  //       try {

  //         $image = $request->image;
  //         $newName = rand() . '.' . $image->getClientOriginalExtension();
  //         $image->move(public_path('/uploads/images'), $newName);
  //         $timeStamp = date("Y-m-d H:i:s");
  //         $dataModel = new ImageModel;
  //         $dataModel->table_name = "product";
  //         $dataModel->table_id = $request->id;
  //         $dataModel->image_type = $request->image_type;
  //         $dataModel->image = $newName;
  //         $dataModel->created_at = $timeStamp;
  //         $dataModel->updated_at = $timeStamp;
  //         $qResponce = $dataModel->save();
  //         if ($qResponce) {

  //           $response = [
  //             "response" => 200,
  //             'status' => true,
  //             'file' => $newName,
  //             'message' => "successfully"

  //           ];
  //         } else
  //           $response = [
  //             "response" => 201,
  //             'status' => false,
  //             'message' => "error",

  //           ];
  //         return response($response, 200);
  //       } catch (\Exception $e) {

  //         $response = [
  //           "response" => 201,
  //           'status' => false,
  //           'message' => "error $e",

  //         ];
  //         return response($response, 200);
  //       }
  //     } else {
  //       $response = [
  //         "response" => 201,
  //         'status' => false,
  //         'message' => "image already exists"
  //       ];
  //       return response($response, 200);
  //     }
  //   }
  // }


  function uploadImage(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'image' => 'required',
      'id' => 'required',
      'image_type' => 'required'
    ]);

    if ($validator->fails()) {
      return response(["response" => 400], 400);
    } else {
      try {
        // Check for existing image reference if image_type is 1
        if ($request->image_type == 1) {
          $alreadyExists = ImageModel::where('table_id', '=', $request->id)
            ->where('table_name', '=', 'product')
            ->where('image_type', '=', '1')
            ->first();

          // Remove the reference from the images table
          if ($alreadyExists) {
            $alreadyExists->delete();
          }
        }

        // Upload the new image
        $image = $request->image;
        $newName = rand() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('/uploads/images'), $newName);

        $timeStamp = date("Y-m-d H:i:s");

        // Save the new image record
        $dataModel = new ImageModel;
        $dataModel->table_name = "product";
        $dataModel->table_id = $request->id;
        $dataModel->image_type = $request->image_type;
        $dataModel->image = $newName;
        $dataModel->created_at = $timeStamp;
        $dataModel->updated_at = $timeStamp;

        $qResponse = $dataModel->save();

        if ($qResponse) {
          $response = [
            "response" => 200,
            'status' => true,
            'file' => $newName,
            'message' => "Image uploaded successfully"
          ];
        } else {
          $response = [
            "response" => 201,
            'status' => false,
            'message' => "Error saving image record"
          ];
        }

        return response($response, 200);
      } catch (\Exception $e) {
        $response = [
          "response" => 500,
          'status' => false,
          'message' => "An error occurred: " . $e->getMessage(),
        ];
        return response($response, 500);
      }
    }
  }


  function deleteData(Request $request)
  {

    $initialCheck = false;
    $validator = Validator::make(request()->all(), [
      'id' => 'required'
    ]);
    if ($validator->fails())
      $initialCheck = true;


    if ($initialCheck)
      return response(["response" => 400], 400);
    else {
      try {
        $timeStamp = date("Y-m-d H:i:s");
        $dataModel = ProductModel::where("id", $request->id)->first();


        $qResponce = $dataModel->delete();
        if ($qResponce)
          $response = [
            "response" => 200,
            'status' => true,
            'message' => "successfully",

          ];
        else
          $response = [
            "response" => 201,
            'status' => false,
            'message' => "error",

          ];
        return response($response, 200);
      } catch (\Exception $e) {
        $response = [
          "response" => 201,
          'status' => false,
          'message' => "error",
        ];
        return response($response, 200);
      }
    }
  }
  function getDataById($id)
  {

    $data = DB::table("product")
      ->select(
        'product.*',
        'sub_cat.title as sub_cat_title',
        'cat.id as cat_id',
        'cat.title as cat_title',
        'images.id as image_id',
        'images.image',
        'vendor.user_name'
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'product.id')
          ->where('images.table_name', '=', "product")
          ->where('images.image_type', '=', 1);
      })
      ->join('sub_cat', 'sub_cat.id', '=', 'product.sub_cat_id')
      ->join('cat', 'cat.id', '=', 'sub_cat.cat_id')
      ->leftJoin('vendor', 'vendor.id', '=', 'product.vendor_id')
      ->where("product.id", "=", $id)
      ->first();
    if ($data != null) {
      $data->slider_image = DB::table("images")
        ->select(
          'images.id',
          'images.image'
        )
        ->where("images.table_id", "=", $id)
        ->where("images.table_name", "=", "product")
        ->where('images.image_type', '=', 2)
        ->get();
    }


    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }
  function getDataBySubCatId($id)
  {

    $data = DB::table("product")
      ->select(
        'product.*',
        'sub_cat.title as sub_cat_title',
        'cat.id as cat_id',
        'cat.title as cat_title',
        'images.id as image_id',
        'images.image'
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'product.id')
          ->where('images.table_name', '=', "product")
          ->where('images.image_type', '=', 1);
      })
      ->join('sub_cat', 'sub_cat.id', '=', 'product.sub_cat_id')
      ->join('cat', 'cat.id', '=', 'sub_cat.cat_id')
      ->where("product.sub_cat_id", "=", $id)
      ->orderBy('product.created_at', 'ASC')
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }
  // function getDatasBySubCatId($id)
  // {

  //   $data = DB::table("product")
  //     ->select(
  //       'product.*',
  //       'sub_cat.title as sub_cat_title',
  //       'cat.id as cat_id',
  //       'cat.title as cat_title',
  //       'images.id as image_id',
  //       'images.image'
  //     )
  //     ->leftJoin('images', function ($join) {
  //       $join->on('images.table_id', '=', 'product.id')
  //         ->where('images.table_name', '=', "product")
  //         ->where('images.image_type', '=', 1);
  //     })
  //     ->join('sub_cat', 'sub_cat.id', '=', 'product.sub_cat_id')
  //     ->join('cat', 'cat.id', '=', 'sub_cat.cat_id')
  //     ->where("product.sub_cat_id", "=", $id)
  //     ->where('product.is_active', '=', 1)
  //     ->whereIn('product.status', ['Approved'])
  //     ->orderBy('product.created_at', 'ASC')
  //     ->get();

  //   $response = [
  //     "response" => 200,
  //     'data' => $data,
  //   ];

  //   return response($response, 200);
  // }

  function getDatasBySubCatId(Request $request, $id)
  {
    $offset = $request->query('offset', 0);
    $limit = $request->query('limit'); // No default here

    $query = DB::table("product")
      ->select(
        'product.*',
        'sub_cat.title as sub_cat_title',
        'cat.id as cat_id',
        'cat.title as cat_title',
        'images.id as image_id',
        'images.image'
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'product.id')
          ->where('images.table_name', '=', "product")
          ->where('images.image_type', '=', 1);
      })
      ->join('sub_cat', 'sub_cat.id', '=', 'product.sub_cat_id')
      ->join('cat', 'cat.id', '=', 'sub_cat.cat_id')
      ->where("product.sub_cat_id", "=", $id)
      ->where('product.is_active', '=', 1)
      ->whereIn('product.status', ['Approved'])
      ->orderBy('product.created_at', 'ASC');

    // Apply limit and offset only if limit is provided
    if (!is_null($limit)) {
      $query->offset((int)$offset)->limit((int)$limit);
    }

    $data = $query->get();

    return response([
      "response" => 200,
      'data' => $data,
    ], 200);
  }


  function getDataList(Request $request)
  {
    $limit = $request->input('limit');
    $offset = $request->input('offset', 0); // Default offset is 0

    // Query to get the total count of products
    $totalCount = DB::table("product")->count();

    // Base query
    $query = DB::table("product")
      ->select(
        'product.*',
        'sub_cat.title as sub_cat_title',
        'cat.id as cat_id',
        'cat.title as cat_title',
        'images.id as image_id',
        'images.image'
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'product.id')
          ->where('images.table_name', '=', "product")
          ->where('images.image_type', '=', 1);
      })
      ->join('sub_cat', 'sub_cat.id', '=', 'product.sub_cat_id')
      ->join('cat', 'cat.id', '=', 'sub_cat.cat_id')
      ->orderBy('product.created_at', 'ASC');

    // Apply pagination only if limit is provided
    if (!is_null($limit)) {
      $query->offset((int)$offset)->limit((int)$limit);
    }

    $data = $query->get();

    // Prepare the response
    $response = [
      "response" => 200,
      "data" => [
        "products" => $data,
        "total_count" => $totalCount
      ]
    ];

    return response($response, 200);
  }


  function getData()
  {
    // Fetching counts as separate queries
    $counts = [
      'total_products' => DB::table('product')->count(),
      'total_active_products' => DB::table('product')->where('is_active', 1)->count(),
      'total_inactive_products' => DB::table('product')->where('is_active', 0)->count(),
      'total_categories' => DB::table('cat')->count(),
      'total_subcategories' => DB::table('sub_cat')->count(),
    ];

    // Fetching product data
    $data = DB::table("product")
      ->select(
        'product.*',
        'sub_cat.title as sub_cat_title',
        'cat.id as cat_id',
        'cat.title as cat_title',
        'images.id as image_id',
        'images.image'
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'product.id')
          ->where('images.table_name', '=', "product")
          ->where('images.image_type', '=', 1);
      })
      ->join('sub_cat', 'sub_cat.id', '=', 'product.sub_cat_id')
      ->join('cat', 'cat.id', '=', 'sub_cat.cat_id')
      ->where('product.is_active', '=', 1)
      // ->whereIn('product.status', ['Approved', 'New'])
      ->orderBy('product.created_at', 'DESC')
      ->get();

    // Response
    $response = [
      "response" => 200,
      'data' => $data,
      'counts' => $counts, // Adding counts as a separate object
    ];

    return response()->json($response);
  }

  // function getDatas()
  // {
  //   // Fetching counts as separate queries
  //   $counts = [
  //     'total_products' => DB::table('product')->count(),
  //     'total_active_products' => DB::table('product')->where('is_active', 1)->count(),
  //     'total_inactive_products' => DB::table('product')->where('is_active', 0)->count(),
  //     'total_categories' => DB::table('cat')->count(),
  //     'total_subcategories' => DB::table('sub_cat')->count(),
  //   ];

  //   // Fetching product data
  //   $data = DB::table("product")
  //     ->select(
  //       'product.*',
  //       'sub_cat.title as sub_cat_title',
  //       'cat.id as cat_id',
  //       'cat.title as cat_title',
  //       'images.id as image_id',
  //       'images.image'
  //     )
  //     ->leftJoin('images', function ($join) {
  //       $join->on('images.table_id', '=', 'product.id')
  //         ->where('images.table_name', '=', "product")
  //         ->where('images.image_type', '=', 1);
  //     })
  //     ->join('sub_cat', 'sub_cat.id', '=', 'product.sub_cat_id')
  //     ->join('cat', 'cat.id', '=', 'sub_cat.cat_id')
  //     ->where('product.is_active', '=', 1)
  //     ->whereIn('product.status', ['Approved'])
  //     ->orderBy('product.created_at', 'ASC')
  //     ->get();

  //   // Response
  //   $response = [
  //     "response" => 200,
  //     'data' => $data,
  //     'counts' => $counts, // Adding counts as a separate object
  //   ];

  //   return response()->json($response);
  // }

  function getDatas(Request $request)
  {
    $limit = $request->input('limit');
    $offset = $request->input('offset');
    $search = $request->input('search');

    $query = DB::table("product")
      ->select(
        'product.*',
        'sub_cat.title as sub_cat_title',
        'cat.id as cat_id',
        'cat.title as cat_title',
        'images.id as image_id',
        'images.image'
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'product.id')
          ->where('images.table_name', '=', "product")
          ->where('images.image_type', '=', 1);
      })
      ->join('sub_cat', 'sub_cat.id', '=', 'product.sub_cat_id')
      ->join('cat', 'cat.id', '=', 'sub_cat.cat_id')
      ->where('product.is_active', '=', 1)
      ->whereIn('product.status', ['Approved']);

    if (!empty($search)) {
      $query->where('product.title', 'like', '%' . $search . '%');
    }

    // Apply pagination only if limit is provided
    if (!is_null($limit)) {
      $query->skip((int)$offset)->take((int)$limit);
    }

    $data = $query->orderBy('product.created_at', 'ASC')->get();

    return response()->json([
      "response" => 200,
      'data' => $data,
    ]);
  }

  function getAllData()
  {
    // Fetching counts as separate queries
    $counts = [
      'total_products' => DB::table('product')->count(),
      'total_active_products' => DB::table('product')->where('is_active', 1)->count(),
      'total_inactive_products' => DB::table('product')->where('is_active', 0)->count(),
      'total_categories' => DB::table('cat')->count(),
      'total_subcategories' => DB::table('sub_cat')->count(),
      'rejected_count' => DB::table('product')->where('status', "Rejected")->count(),
      'pending_count' => DB::table('product')->where('status', "Pending")->count(),
      'approved_count' => DB::table('product')->where('status', "Approved")->count(),
    ];
    // Fetching product data
    $data = DB::table("product")
      ->select(
        'product.*',
        'sub_cat.title as sub_cat_title',
        'cat.id as cat_id',
        'cat.title as cat_title',
        'images.id as image_id',
        'images.image'
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'product.id')
          ->where('images.table_name', '=', "product")
          ->where('images.image_type', '=', 1);
      })
      ->join('sub_cat', 'sub_cat.id', '=', 'product.sub_cat_id')
      ->join('cat', 'cat.id', '=', 'sub_cat.cat_id')
      ->orderBy('product.created_at', 'DESC')
      ->get();

    // Response
    $response = [
      "response" => 200,
      'data' => $data,
      'counts' => $counts, // Adding counts as a separate object
    ];

    return response()->json($response);
  }

  function getApprovalProductsData()
  {
    // Fetching counts as separate queries
    $counts = [
      'rejected_count' => DB::table('product')->where('status', "Rejected")->count(),
      'pending_count' => DB::table('product')->where('status', "Pending")->count(),
      'approved_count' => DB::table('product')->where('status', "Approved")->count(),
    ];

    // Fetching product data
    $data = DB::table("product")
      ->select(
        'product.*',
        'sub_cat.title as sub_cat_title',
        'cat.id as cat_id',
        'cat.title as cat_title',
        'images.id as image_id',
        'images.image'
      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'product.id')
          ->where('images.table_name', '=', "product")
          ->where('images.image_type', '=', 1);
      })
      ->join('sub_cat', 'sub_cat.id', '=', 'product.sub_cat_id')
      ->join('cat', 'cat.id', '=', 'sub_cat.cat_id')
      ->where('product.is_active', '=', 1)
      ->where('product.status', '=', 'Pending')
      ->orderBy('product.created_at', 'ASC')
      ->get();

    // Response
    $response = [
      "response" => 200,
      'data' => $data,
      'counts' => $counts, // Adding counts as a separate object
    ];

    return response()->json($response);
  }


  function updateDetails(Request $request)
  {
    $initialCheck = false;
    $validator = Validator::make(request()->all(), [
      'id' => 'required'
    ]);
    if ($validator->fails())
      $initialCheck = true;


    if ($initialCheck)
      return response(["response" => 400], 400);
    else {
      try {
        $timeStamp = date("Y-m-d H:i:s");
        $dataModel = ProductModel::where("id", $request->id)->first();
        $alreadyExists = ProductModel::where('title', '=', $request->title)->where('id', "!=", $request->id)->first();
        // $skuAlreadyExists = ProductModel::where('sku', '=', $request->sku)->where('id', "!=", $request->id)->first();
        $skuAlreadyExists = null;
        if ($request->has('sku') && $request->sku !== null) {
          $skuAlreadyExists = ProductModel::where('sku', $request->sku)
            ->where('id', '!=', $request->id)
            ->first();
        }

        if ($alreadyExists == null && $skuAlreadyExists === null) {

          if (isset($request->title)) {
            $dataModel->title = $request->title;
          }
          if (isset($request->qty_text)) {
            $dataModel->qty_text = $request->qty_text;
          }
          if (isset($request->sub_cat_id)) {
            $dataModel->sub_cat_id = $request->sub_cat_id;
          }
          if (isset($request->stock_qty)) {
            $dataModel->stock_qty = $request->stock_qty;
          }

          if (isset($request->price)) {
            $dataModel->price = $request->price;
          }
          if (isset($request->mrp)) {
            $dataModel->mrp = $request->mrp;
          }
          if (isset($request->subscription)) {
            $dataModel->subscription = $request->subscription;
          }
          if (isset($request->tax)) {
            $dataModel->tax = $request->tax;
          }
          if (isset($request->approved_by)) {
            $dataModel->approved_by = $request->approved_by;
          }


          $dataModel->offer_text = $request->offer_text ?? "0";
          $dataModel->description = $request->description ?? null;
          $dataModel->disclaimer = $request->disclaimer ?? null;
          if (isset($request->sku)) {
            $dataModel->sku = $request->sku;
          }
          if (isset($request->storage_type)) {
            $dataModel->storage_type = $request->storage_type;
          }
          if (isset($request->min_cart_qty)) {
            $dataModel->min_cart_qty = $request->min_cart_qty;
          }
          if (isset($request->max_cart_qty)) {
            $dataModel->max_cart_qty = $request->max_cart_qty;
          }
          if (isset($request->daily_sales_limit)) {
            $dataModel->daily_sales_limit = $request->daily_sales_limit;
          }
          if (isset($request->is_active)) {
            $dataModel->is_active = $request->is_active;
          }
          if (isset($request->status)) {
            $dataModel->status = $request->status;
          }
          if (isset($request->vendor_id)) {
            $dataModel->vendor_id = $request->vendor_id;
          }
          if (isset($request->purchase_price)) {
            $dataModel->purchase_price = $request->purchase_price;
          }
          if (isset($request->margin_percent)) {
            $dataModel->margin_percent = $request->margin_percent;
          }
          if (isset($request->margin_amt)) {
            $dataModel->margin_amt = $request->margin_amt;
          }
          if (isset($request->margin_type)) {
            $dataModel->margin_type = $request->margin_type;
          }

          if (isset($request->expire_days)) {
            $dataModel->expire_days = $request->expire_days;
          }

          $dataModel->updated_at = $timeStamp;


          $qResponce = $dataModel->save();

          if ($qResponce) {

            // $imageFile=isset($request->image)?$request->image:null;
            // $imageId=isset($request->image_id)?$request->image_id:null;

            // app('App\Http\Controllers\ImageCountController')->uploadImage($imageFile, "buses", $userDetailsModel->id,1,$imageId);
            // //1=profile_image  

            $response = [
              "response" => 200,
              'status' => true,
              'message' => "successfully",

            ];
          } else
            $response = [
              "response" => 201,
              'status' => false,
              'message' => "error",

            ];
          return response($response, 200);
        } else {
          $response = [
            "response" => 201,
            'status' => false,
            'message' => $alreadyExists ? "title already exists" : "SKU already exists"
          ];
          return response($response, 200);
        }
      } catch (\Exception $e) {
        $response = [
          "response" => 201,
          'status' => false,
          'message' => "error $e",
        ];
        return response($response, 200);
      }
    }
  }
  public function addData(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'sub_cat_id' => 'required',
      'title' => 'required',
      'qty_text' => 'required',
      'price' => 'required',
      'mrp' => 'required',
      'subscription' => 'required',
      'stock_qty' => 'required',
      'tax' => 'required',
      'sku' => 'required',
      'storage_type' => 'required',
      'min_cart_qty' => 'required',
      'max_cart_qty' => 'required',
      'daily_sales_limit' => 'required',
      'vendor_id' => 'required',
      'purchase_price' => 'required',
      'margin_percent' => 'required',
      'margin_amt' => 'required',
      'margin_type' => 'required',
    ]);

    // If validation fails, return the errors with detailed messages
    if ($validator->fails()) {
      return response()->json([
        'response' => 400,
        'status' => false,
        'message' => 'Please fill in all required fields.',
        'errors' => $validator->errors()
      ], 400);
    }

    // Check if the title or SKU already exists
    $alreadyExists = ProductModel::where('title', '=', $request->title)->first();
    $skuAlreadyExists = ProductModel::where('sku', '=', $request->sku)->first();

    if ($alreadyExists === null && $skuAlreadyExists === null) {
      try {
        $timeStamp = date("Y-m-d H:i:s");
        $dataModel = new ProductModel;
        $dataModel->title = $request->title;
        $dataModel->qty_text = $request->qty_text;
        $dataModel->sub_cat_id = $request->sub_cat_id;
        $dataModel->price = $request->price;
        $dataModel->mrp = $request->mrp;
        $dataModel->stock_qty = $request->stock_qty;
        $dataModel->tax = $request->tax;
        $dataModel->subscription = $request->subscription;
        $dataModel->sku = $request->sku;
        $dataModel->storage_type = $request->storage_type;
        $dataModel->min_cart_qty = $request->min_cart_qty;
        $dataModel->max_cart_qty = $request->max_cart_qty;
        $dataModel->daily_sales_limit = $request->daily_sales_limit;
        $dataModel->is_active = $request->is_active;
        $dataModel->status = $request->status;
        $dataModel->vendor_id = $request->vendor_id;
        $dataModel->purchase_price = $request->purchase_price;
        $dataModel->margin_percent = $request->margin_percent;
        $dataModel->margin_amt = $request->margin_amt;
        $dataModel->margin_type = $request->margin_type;

        if (isset($request->expire_days)) {
          $dataModel->expire_days = $request->expire_days;
        }
        $dataModel->offer_text = $request->offer_text ?? "0";
        $dataModel->description = $request->description ?? null;
        $dataModel->disclaimer = $request->disclaimer ?? null;

        $dataModel->created_at = $timeStamp;
        $dataModel->updated_at = $timeStamp;
        $qResponce = $dataModel->save();

        if ($qResponce) {
          $response = [
            'response' => 200,
            'status' => true,
            'message' => 'Product added successfully.',
            'id' => $dataModel->id
          ];
        } else {
          $response = [
            'response' => 500,
            'status' => false,
            'message' => 'There was an issue saving the product. Please try again later.',
          ];
        }
        return response()->json($response, 200);
      } catch (\Exception $e) {
        $response = [
          'response' => 500,
          'status' => false,
          'message' => 'An unexpected error occurred: ' . $e->getMessage(),
        ];
        return response()->json($response, 500);
      }
    } else {
      $response = [
        'response' => 409,  // Conflict status code
        'status' => false,
        'message' => $alreadyExists ? 'Product title already exists.' : 'Product SKU already exists.',
      ];
      return response()->json($response, 409);
    }
  }


  // public function importCSV(Request $request)
  // {
  //   $request->validate([
  //     'file' => 'required|mimes:csv,txt|max:2048',
  //   ]);

  //   try {
  //     $file = $request->file('file');
  //     $path = $file->getRealPath();
  //     $csvData = array_map('str_getcsv', file($path));
  //     $header = array_shift($csvData); // Remove and fetch header row

  //     DB::beginTransaction(); // Start a database transaction

  //     $insertData = [];
  //     $insertCount = 0; // Counter for inserted products
  //     $updateCount = 0; // Counter for updated products

  //     foreach ($csvData as $index => $row) {
  //       try {
  //         $row = array_combine($header, $row);

  //         // Normalize CSV and database titles for case-insensitivity and trimming
  //         $subcategoryTitle = strtolower(trim($row['Subcategory']));
  //         $productTitle = strtolower(trim($row['Product']));

  //         // Fetch subcategory ID (case-insensitive match)
  //         $subCat = SubCategoryModel::whereRaw('LOWER(TRIM(title)) = ?', [$subcategoryTitle])->first();
  //         if (!$subCat) {
  //           Log::error("Subcategory '{$row['Subcategory']}' not found for row {$index}."); // Log missing subcategory
  //           throw new \Exception("Subcategory '{$row['Subcategory']}' not found for row {$index}.");
  //         }

  //         // Fetch product (case-insensitive match)
  //         $product = ProductModel::whereRaw('LOWER(TRIM(title)) = ?', [$productTitle])->first();

  //         if ($product) {
  //           // Log when a product is found and will be updated
  //           // Log::info("Updating product: {$productTitle}");

  //           // Update existing product
  //           $product->update([
  //             'qty_text' => $row['Unit'],
  //             'stock_qty' => 100,
  //             'sub_cat_id' => $subCat->id,
  //             'price' => $row['MRP'],
  //             'mrp' => $row['MRP'],
  //             'tax' => 0,
  //             'offer_text' => null,
  //             'description' => null,
  //             'disclaimer' => null,
  //             'subscription' => in_array($subcategoryTitle, ['milk', 'eggs']) ? 1 : 0,
  //             'updated_at' => now(),
  //           ]);

  //           $updateCount++; // Increment the update counter
  //         } else {
  //           // Log when a product is not found and will be inserted
  //           // Log::info("Inserting new product: {$productTitle}");

  //           // Insert new product
  //           $insertData[] = [
  //             'title' => $row['Product'],
  //             'qty_text' => $row['Unit'],
  //             'stock_qty' => 100,
  //             'sub_cat_id' => $subCat->id,
  //             'price' => $row['MRP'],
  //             'mrp' => $row['MRP'],
  //             'tax' => 0,
  //             'offer_text' => null,
  //             'description' => null,
  //             'disclaimer' => null,
  //             'subscription' => in_array($subcategoryTitle, ['milk', 'eggs']) ? 1 : 0,
  //             'created_at' => now(),
  //             'updated_at' => now(),
  //           ];

  //           $insertCount++; // Increment the insert counter
  //         }

  //         // Insert in batches to optimize database performance
  //         if (count($insertData) >= 100) {
  //           ProductModel::insert($insertData);
  //           $insertData = []; // Reset batch
  //         }
  //       } catch (\Exception $e) {
  //         // Log errors for each row without halting the entire process
  //         Log::error("Error processing row {$index}: " . $e->getMessage());
  //       }
  //     }

  //     // Insert remaining batch
  //     if (!empty($insertData)) {
  //       ProductModel::insert($insertData);
  //     }

  //     DB::commit(); // Commit transaction

  //     // Return the response with counts
  //     return response()->json([
  //       'message' => 'Products imported successfully!',
  //       'inserted' => $insertCount,
  //       'updated' => $updateCount
  //     ], 200);
  //   } catch (\Exception $e) {
  //     DB::rollBack(); // Rollback transaction on any error
  //     Log::error("Error importing products: " . $e->getMessage());
  //     return response()->json(['error' => 'An error occurred while importing products.'], 500);
  //   }
  // }


  // public function importCSV(Request $request)
  // {
  //   $request->validate([
  //     'file' => 'required|mimes:csv,txt|max:2048',
  //   ]);

  //   try {
  //     $file = $request->file('file');
  //     $path = $file->getRealPath();
  //     $csvData = array_map('str_getcsv', file($path));
  //     $header = array_shift($csvData); // Remove and fetch header row

  //     DB::beginTransaction(); // Start a database transaction

  //     $insertData = [];
  //     $insertCount = 0; // Counter for inserted products
  //     $updateCount = 0; // Counter for updated products
  //     $imagePath = public_path('/uploads/images'); // Path to your product images directory

  //     foreach ($csvData as $index => $row) {
  //       try {
  //         $row = array_combine($header, $row);

  //         // Normalize CSV and database titles for case-insensitivity and trimming
  //         $subcategoryTitle = strtolower(trim($row['Subcategory']));
  //         $productTitle = strtolower(trim($row['Product']));

  //         // Fetch subcategory ID (case-insensitive match)
  //         $subCat = SubCategoryModel::whereRaw('LOWER(TRIM(title)) = ?', [$subcategoryTitle])->first();
  //         if (!$subCat) {
  //           Log::error("Subcategory '{$row['Subcategory']}' not found for row {$index}.");
  //           continue;
  //         }

  //         // Fetch product (case-insensitive match)
  //         $product = ProductModel::whereRaw('LOWER(TRIM(title)) = ?', [$productTitle])->first();
  //         if ($product) {
  //           // Update existing product
  //           $product->update([
  //             'qty_text' => $row['Unit'],
  //             'stock_qty' => 100,
  //             'sub_cat_id' => $subCat->id,
  //             'price' => $row['MRP'],
  //             'mrp' => $row['MRP'],
  //             'tax' => 0,
  //             'offer_text' => null,
  //             'description' => null,
  //             'disclaimer' => null,
  //             'subscription' => in_array($subcategoryTitle, ['milk', 'eggs']) ? 1 : 0,
  //             'updated_at' => now(),
  //           ]);
  //           $productId = $product->id;
  //           $updateCount++;
  //         } else {
  //           // Insert new product
  //           $newProduct = [
  //             'title' => $row['Product'],
  //             'qty_text' => $row['Unit'],
  //             'stock_qty' => 100,
  //             'sub_cat_id' => $subCat->id,
  //             'price' => $row['MRP'],
  //             'mrp' => $row['MRP'],
  //             'tax' => 0,
  //             'offer_text' => null,
  //             'description' => null,
  //             'disclaimer' => null,
  //             'subscription' => in_array($subcategoryTitle, ['milk', 'eggs']) ? 1 : 0,
  //             'created_at' => now(),
  //             'updated_at' => now(),
  //           ];

  //           $insertData[] = $newProduct;
  //           $insertCount++;
  //           // $productId = null; // Set to null initially for new products
  //         }

  //         // Handle batch insert
  //         if (count($insertData) >= 100) {
  //           ProductModel::insert($insertData);
  //           $insertData = [];
  //         }

  //         // Handle image logic
  //         $imageFileNameBase = pathinfo($row['Product Image'], PATHINFO_FILENAME);
  //         $validExtensions = ['jpg', 'jpeg', 'png', 'webp'];
  //         $imageFileName = null;
  //         if ($imageFileNameBase) {
  //           foreach ($validExtensions as $ext) {
  //             $testPath = "{$imagePath}/{$imageFileNameBase}.{$ext}";
  //             if (file_exists($testPath)) {
  //               $imageFileName = "{$imageFileNameBase}.{$ext}";
  //               break;
  //             }
  //           }

  //           if ($imageFileName) {
  //             // Update the image table with just the file name (no path)
  //             ImageModel::updateOrCreate(
  //               ['table_name' => 'product', 'table_id' => $productId, 'image_type' => 1],
  //               ['image' => $imageFileName, 'updated_at' => now()]
  //             );
  //           } else {
  //             Log::warning("Image not found for product '{$row['Product']}' at row {$index}.");
  //           }
  //         }
  //       } catch (\Exception $e) {
  //         Log::error("Error processing row {$index}: " . $e->getMessage());
  //       }
  //     }

  //     // Insert remaining batch
  //     if (!empty($insertData)) {
  //       ProductModel::insert($insertData);
  //     }

  //     DB::commit(); // Commit transaction

  //     // Return the response with counts
  //     return response()->json([
  //       'message' => 'Products imported successfully!',
  //       'inserted' => $insertCount,
  //       'updated' => $updateCount,
  //     ], 200);
  //   } catch (\Exception $e) {
  //     DB::rollBack(); // Rollback transaction on any error
  //     Log::error("Error importing products: " . $e->getMessage());
  //     return response()->json(['error' => 'An error occurred while importing products.'], 500);
  //   }
  // }

  public function importCSV(Request $request)
  {
    $request->validate([
      'file' => 'required|mimes:csv,txt|max:2048',
    ]);

    try {
      $file = $request->file('file');
      $path = $file->getRealPath();
      $csvData = array_map('str_getcsv', file($path));
      $header = array_shift($csvData); // Remove and fetch header row

      DB::beginTransaction(); // Start a database transaction

      $insertData = [];
      $insertCount = 0; // Counter for inserted products
      $updateCount = 0; // Counter for updated products
      $imagePath = public_path('/uploads/images'); // Path to your product images directory

      foreach ($csvData as $index => $row) {
        try {
          $row = array_combine($header, $row);

          // Normalize CSV and database titles for case-insensitivity and trimming
          $subcategoryTitle = strtolower(trim($row['Subcategory']));
          $productTitle = strtolower(trim($row['Product']));

          // Fetch subcategory ID (case-insensitive match)
          $subCat = SubCategoryModel::whereRaw('LOWER(TRIM(title)) = ?', [$subcategoryTitle])->first();
          if (!$subCat) {
            Log::error("Subcategory '{$row['Subcategory']}' not found for row {$index}.");
            continue;
          }

          // Fetch product (case-insensitive match)
          $product = ProductModel::whereRaw('LOWER(TRIM(title)) = ?', [$productTitle])->first();
          $stockQty = empty($row['Stock in Hand']) ? 0 : (int) $row['Stock in Hand'];
          $isActive = strtolower($row['Active Status'] ?? '') === 'active' ? 1 : 0;
          if ($product) {
            // Update existing product
            $product->update([
              'sku' => $row['SKU'] ?? $product->sku,
              'title' => $row['Product'] ?? $product->title,
              'qty_text' => $row['Unit'] ?? $product->qty_text,
              'stock_qty' => $stockQty,
              'sub_cat_id' => $subCat->id,
              'price' => $row['MRP'] ?? $product->price,
              'tax' => $row['Margin Rate'] ?? $product->tax,
              'mrp' => $row['MRP'] ?? $product->mrp,
              'purchase_price' => $row['Purchase Price'] ?? $product->purchase_price,
              'margin_percent' => $row['Margin Rate'] ?? $product->margin_percent,
              'margin_amt' => $row['Margin Amount'] ?? $product->margin_amt,
              'daily_sales_limit' => $row['Daily Sales Limit'] ?? $product->daily_sales_limit,
              'is_active' => $isActive,
              'offer_text' => $row['Offer Text'] ?? $product->offer_text,
              'description' => $row['Description'] ?? $product->description,
              'disclaimer' => $row['Disclaimer'] ?? $product->disclaimer,
              'subscription' => in_array($subcategoryTitle, ['milk', 'eggs']) ? 1 : 0,
              'expire_days' => $row['Expire Days'] ?? $product->expire_days,
              'storage_type' => $row['Storage Type'] ?? $product->storage_type,
              'min_cart_qty' => $row['Min Cart Qty'] ?? $product->min_cart_qty,
              'max_cart_qty' => $row['Max Cart Qty'] ?? $product->max_cart_qty,
              'status' => $row['Status'] ?? $product->status,
              'vendor_id' => $row['Vendor ID'] ?? $product->vendor_id,
              'approved_by' => $row['Approved By'] ?? $product->approved_by,
              'updated_at' => now(),
            ]);
            $productId = $product->id; // Set the existing product ID
            $updateCount++;
          } else {
            // Insert new product
            $newProduct = [
              'sku' => $row['SKU'] ?? null,
              'title' => $row['Product'],
              'qty_text' => $row['Unit'],
              'stock_qty' => $stockQty,
              'sub_cat_id' => $subCat->id,
              'price' => $row['MRP'],
              'tax' => $row['Margin Rate'] ?? 0,
              'mrp' => $row['MRP'],
              'purchase_price' => $row['Purchase Price'] ?? null,
              'margin_percent' => $row['Margin Rate'] ?? null,
              'margin_amt' => $row['Margin Amount'] ?? null,
              'daily_sales_limit' => $row['Daily Sales Limit'] ?? null,
              'is_active' => $isActive,
              'offer_text' => $row['Offer Text'] ?? null,
              'description' => $row['Description'] ?? null,
              'disclaimer' => $row['Disclaimer'] ?? null,
              'subscription' => in_array($subcategoryTitle, ['milk', 'eggs']) ? 1 : 0,
              'expire_days' => $row['Expire Days'] ?? null,
              'storage_type' => $row['Storage Type'] ?? null,
              'min_cart_qty' => $row['Min Cart Qty'] ?? null,
              'max_cart_qty' => $row['Max Cart Qty'] ?? null,
              'status' => $row['Status'] ?? null,
              'vendor_id' => $row['Vendor ID'] ?? null,
              'approved_by' => $row['Approved By'] ?? null,
              'created_at' => now(),
              'updated_at' => now(),
            ];

            $product = ProductModel::create($newProduct); // Use create() to insert and get the product instance
            $productId = $product->id; // Now set the product ID from the newly inserted product
            $insertCount++;
          }

          // Handle batch insert
          if (count($insertData) >= 100) {
            ProductModel::insert($insertData);
            $insertData = [];
          }

          // Handle image logic
          $imageFileNameBase = pathinfo($row['Product Image'], PATHINFO_FILENAME);
          $validExtensions = ['jpg', 'jpeg', 'png', 'webp'];
          $imageFileName = null;

          if ($imageFileNameBase) {
            foreach ($validExtensions as $ext) {
              $testPath = public_path('uploads' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . "{$imageFileNameBase}.{$ext}");

              // Log for debugging purposes
              Log::info("Checking image path: {$testPath}");
              if (file_exists($testPath)) {
                $imageFileName = "{$imageFileNameBase}.{$ext}";
                break;
              }
            }

            if ($imageFileName) {
              // Ensure that $productId is set before updating the image
              if ($productId) {
                // Update or create the image record in the database
                ImageModel::updateOrCreate(
                  ['table_name' => 'product', 'table_id' => $productId, 'image_type' => 1],
                  ['image' => $imageFileName, 'updated_at' => now()]
                );
              } else {
                // Log error if product ID is null (shouldn't happen after product insert)
                Log::error("Product ID is null for product '{$row['Product']}' at row {$index}, cannot associate image.");
              }
            } else {
              Log::warning("Image not '{$imageFileName}' for product '{$row['Product']}' at row {$index}.");
            }
          }
        } catch (\Exception $e) {
          Log::error("Error processing row {$index}: " . $e->getMessage());
        }
      }

      // Insert remaining batch
      if (!empty($insertData)) {
        ProductModel::insert($insertData);
      }

      DB::commit(); // Commit transaction

      // Return the response with counts
      return response()->json([
        'message' => 'Products imported successfully!',
        'inserted' => $insertCount,
        'updated' => $updateCount,
      ], 200);
    } catch (\Exception $e) {
      DB::rollBack(); // Rollback transaction on any error
      Log::error("Error importing products: " . $e->getMessage());
      return response()->json(['error' => 'An error occurred while importing products.'], 500);
    }
  }

  function addProductStock(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'product_id' => 'required',
      'stock' => 'required',
      'commands' => 'required',
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      $timeStamp = date("Y-m-d H:i:s");
      $dataModel = new StockApprovalModel();
      $dataModel->product_id  = $request->product_id;
      $dataModel->stock  = $request->stock;
      $dataModel->commands  = $request->commands;
      $dataModel->approved_by  = $request->approved_by;
      $dataModel->approval_status  = "Pending";
      $dataModel->created_at = $timeStamp;
      $dataModel->updated_at = $timeStamp;


      // Handle po_no and pi_no only if data is provided 
      if (isset($request->po_pi) && is_array($request->po_pi)) {
        if ($request->po_pi['key'] === 'po_no') {
          $dataModel->po_no = $request->po_pi['value'];
        } elseif ($request->po_pi['key'] === 'pi_no') {
          $dataModel->pi_no = $request->po_pi['value'];
        }
      }

      $qResponce = $dataModel->save();
    }
    if ($qResponce) {
      $response = [
        "response" => 200,
        'status' => true,
        'message' => "successfully",

      ];
    } else {
      $response = [
        "response" => 201,
        'status' => false,
        'message' => "error",

      ];
    }
    return response($response, 200);
  }

  function getStockApprovalData()
  {
    try {
      // Fetch stock approvals
      $stockApprovals = DB::table("stock_approval")
        ->select('stock_approval.*')
        ->orderBy('created_at', 'DESC')
        ->get()
        ->map(function ($item) {
          if (!empty($item->created_at)) {
            $item->created_at = Carbon::parse($item->created_at)->format('d-m-Y');
          }
          return $item;
        });

      // Fetch product titles
      $productTitles = DB::table('product')
        ->select('id', 'title')
        ->get();

      return response()->json([
        'success' => true,
        'data' => [
          'stockApprovals' => $stockApprovals,
          'productTitles' => $productTitles,
        ],
      ]);
    } catch (\Exception $e) {
      Log::error("Error in getDashboardData: " . $e->getMessage());

      return response()->json([
        'success' => false,
        'message' => 'Error fetching dashboard data',
      ], 500);
    }
  }

  function changeStockApprovalStaus(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'id' => 'required',
      'approval_status' => 'required',
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      $existingRecord = StockApprovalModel::where('id', '=', $request->id)->first();
      $timeStamp = date("Y-m-d H:i:s");
      $existingRecord->updated_at = $timeStamp;
      $existingRecord->approval_status = $request->approval_status;
      $existingRecord->approved_by = $request->approved_by;
      $qResponce = $existingRecord->save();
      if ($request->approval_status == "Approved") {
        $product = ProductModel::where('id', '=', $existingRecord->product_id)->first();
        $product->stock_qty += $existingRecord->stock;
        $product->updated_at = $timeStamp;
        $qProductResponce =  $product->save();
      } else {
        $qProductResponce = true;
      }

      if ($qResponce && $qProductResponce) {
        $response = [
          "response" => 200,
          'status' => true,
          'message' => "successfully",

        ];
      } else {
        $response = [
          "response" => 201,
          'status' => false,
          'message' => "error",

        ];
      }
      return response($response, 200);
    }
  }

  function getPiAndPoNumber()
  {
    try {
      // Fetch data from purchase_order table
      $purchaseOrders = DB::table('purchars_order')
        ->select('id', 'po_no')
        ->where('po_status', 'Approved')
        ->get();

      // Fetch data from purchase_invoice table
      $purchaseInvoices = DB::table('purchase_invoice')
        ->select('id', 'pi_no')
        ->where('approval_status', 'Approved')
        ->get();

      return response()->json([
        'status' => 200,
        'data' => [
          'purchaseOrders' => $purchaseOrders,
          'purchaseInvoices' => $purchaseInvoices,
        ],
      ]);
    } catch (\Exception $e) {
      Log::error("Error in getPiAndPoNumber: " . $e->getMessage());

      return response()->json([
        'status' => 404,
        'message' => 'Error fetching purchase orders and invoices data',
      ], 500);
    }
  }
}
