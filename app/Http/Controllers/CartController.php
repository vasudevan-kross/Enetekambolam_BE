<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CartModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;



class CartController extends Controller
{
  function updateQty(Request $request)
  {

    $initialCheck = false;
    $validator = Validator::make(request()->all(), [
      'id' => 'required',
      'qty' => 'required',
      'total_price' => 'required'
    ]);
    if ($validator->fails())
      $initialCheck = true;
    if ($initialCheck)
      return response(["response" => 400], 400);
    else {
      try {
        $dataModel = CartModel::where("id", $request->id)->first();
        $dataModel->qty = $request->qty;
        $dataModel->total_price = $request->total_price;

        $qResponce = $dataModel->save();
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

  function getAllCartDataGroupedByUser(Request $request)
  {
    try {
      $query = DB::table('cart')
        ->select(
          'cart.user_id',
          'cart.id as cart_id',
          'cart.product_id',
          'cart.qty as quantity',
          'product.title as product_title',
          'product.mrp as product_mrp',
          'product.price as product_price',
          'product.tax as product_tax',
          'images.image as product_image',
          'product.qty_text',
          'product.stock_qty',
          'sub_cat.title as subcategory',
          'cat.title as category',
          'users.name',
          'users.email',
          'users.phone',
          'users.wallet_amount',
          'cart.created_at',
          'cart.updated_at'
        )
        ->leftJoin('images', function ($join) {
          $join->on('images.table_id', '=', 'cart.product_id')
            ->where('images.table_name', '=', "product")
            ->where('images.image_type', '=', 1);
        })
        ->join('product', 'cart.product_id', '=', 'product.id')
        ->join('sub_cat', 'sub_cat.id', '=', 'product.sub_cat_id')
        ->join('cat', 'cat.id', '=', 'sub_cat.cat_id')
        ->join('users', 'cart.user_id', '=', 'users.id');

      // Apply date filter if provided
      if ($request->has('date')) {
        $date = Carbon::parse($request->query('date'))->format('Y-m-d');
        $query->whereDate('cart.updated_at', $date);
      }

      $data = $query->orderBy('cart.created_at', 'DESC')->get();

      // Grouping logic remains the same
      $groupedData = [];
      foreach ($data as $item) {
        $userId = $item->user_id;

        if (!isset($groupedData[$userId])) {
          $groupedData[$userId] = [
            'user_id' => $item->user_id,
            'name' => $item->name,
            'email' => $item->email,
            'phone' => $item->phone,
            'wallet_amount' => $item->wallet_amount,
            'cart_items' => [],
            'created_at' => $item->created_at,
            'updated_at' => $item->updated_at,
          ];
        }

        $groupedData[$userId]['created_at'] = max($groupedData[$userId]['created_at'], $item->created_at);

        $groupedData[$userId]['cart_items'][] = [
          'cart_id' => $item->cart_id,
          'product_id' => $item->product_id,
          'quantity' => $item->quantity,
          'product_title' => $item->product_title,
          'product_mrp' => $item->product_mrp,
          'product_price' => $item->product_price,
          'product_tax' => $item->product_tax,
          'product_image' => $item->product_image,
          'qty_text' => $item->qty_text,
          'stock_qty' => $item->stock_qty,
          'subcategory' => $item->subcategory,
          'category' => $item->category,
        ];
      }

      $finalData = array_values($groupedData);

      return response([
        "response" => 200,
        'data' => $finalData,
      ], 200);
    } catch (\Exception $e) {
      return response([
        "response" => 500,
        "message" => "An error occurred while fetching cart data.",
        "error" => $e->getMessage()
      ], 500);
    }
  }


  // function getAllCartDataGroupedByUser()
  // {
  //   try {
  //     // Get all cart items without grouping
  //     $data = DB::table('cart')
  //       ->select(
  //         'cart.user_id',
  //         'cart.id as cart_id',
  //         'cart.product_id',
  //         'cart.qty as quantity',
  //         'product.title as product_title',
  //         'product.mrp as product_mrp',
  //         'product.price as product_price',
  //         'product.tax as product_tax',
  //         'images.image as product_image',
  //         'product.qty_text',
  //         'product.stock_qty',
  //         'sub_cat.title as subcategory',
  //         'cat.title as category',
  //         'users.name',
  //         'users.email',
  //         'users.phone',
  //         'users.wallet_amount',
  //         'cart.created_at',
  //         'cart.updated_at'
  //       )
  //       ->leftJoin('images', function ($join) {
  //         $join->on('images.table_id', '=', 'cart.product_id')
  //           ->where('images.table_name', '=', "product")
  //           ->where('images.image_type', '=', 1);
  //       })
  //       ->join('product', 'cart.product_id', '=', 'product.id')
  //       ->join('sub_cat', 'sub_cat.id', '=', 'product.sub_cat_id')
  //       ->join('cat', 'cat.id', '=', 'sub_cat.cat_id')
  //       ->join('users', 'cart.user_id', '=', 'users.id')
  //       ->orderBy('cart.created_at', 'DESC')
  //       ->get();

  //     // Group data by `user_id` in PHP
  //     $groupedData = [];
  //     foreach ($data as $item) {
  //       $userId = $item->user_id;
  //       // If user does not exist in grouped data, initialize it
  //       if (!isset($groupedData[$userId])) {
  //         $groupedData[$userId] = [
  //           'user_id' => $item->user_id,
  //           'name' => $item->name,
  //           'email' => $item->email,
  //           'phone' => $item->phone,
  //           'wallet_amount' => $item->wallet_amount,
  //           'cart_items' => [],
  //           'created_at' => $item->created_at,
  //           'updated_at' => $item->updated_at,
  //         ];
  //       }

  //       // Update latest creation date if necessary
  //       $groupedData[$userId]['created_at'] = max($groupedData[$userId]['created_at'], $item->created_at);

  //       // Add cart item to the user's cart_items array
  //       $groupedData[$userId]['cart_items'][] = [
  //         'cart_id' => $item->cart_id,
  //         'product_id' => $item->product_id,
  //         'quantity' => $item->quantity,
  //         'product_title' => $item->product_title,
  //         'product_mrp' => $item->product_mrp,
  //         'product_price' => $item->product_price,
  //         'product_tax' => $item->product_tax,
  //         'product_image' => $item->product_image,
  //         'qty_text' => $item->qty_text,
  //         'stock_qty' => $item->stock_qty,
  //         'subcategory' => $item->subcategory,
  //         'category' => $item->category,
  //       ];
  //     }

  //     // Convert grouped data to indexed array
  //     $finalData = array_values($groupedData);

  //     $response = [
  //       "response" => 200,
  //       'data' => $finalData,
  //     ];

  //     return response($response, 200);
  //   } catch (\Exception $e) {
  //     return response([
  //       "response" => 500,
  //       "message" => "An error occurred while fetching cart data.",
  //       "error" => $e->getMessage()
  //     ], 500);
  //   }
  // }


  function getDataByUId($id)
  {

    $data = DB::table("cart")
      ->select(
        'cart.*',
        'product.title',
        'images.image as product_image',
        'product.qty_text',
        'product.stock_qty',
        'product.is_active'

      )
      ->leftJoin('images', function ($join) {
        $join->on('images.table_id', '=', 'cart.product_id')
          ->where('images.table_name', '=', "product")
          ->where('images.image_type', '=', 1);
      })
      ->Join('product', 'cart.product_id', '=', 'product.id')
      ->where("cart.user_id", "=", $id)
      ->orderBy('cart.created_at', 'DESC')
      ->get();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
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
        $dataModel = CartModel::where("id", $request->id)->first();


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
  function addData(Request $request)
  {

    $validator = Validator::make(request()->all(), [
      'product_id' => 'required',
      'user_id' => 'required',
      'qty' => 'required',
      'price' => 'required',
      'mrp' => 'required',
      'total_price' => 'required',
      'tax' => 'required',
      'qty_text' => 'required'

    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      try {

        $alreadyExists = CartModel::where('user_id', '=', $request->user_id)->where('product_id', "=", $request->product_id)->first();
        if ($alreadyExists == null) {
          $timeStamp = date("Y-m-d H:i:s");
          $dataModel = new CartModel;

          $dataModel->user_id = $request->user_id;
          $dataModel->product_id = $request->product_id;

          $dataModel->qty  = $request->qty;
          $dataModel->price = $request->price;
          $dataModel->mrp  = $request->mrp;
          $dataModel->tax = $request->tax;
          $dataModel->qty_text = $request->qty_text;
          $dataModel->total_price = $request->total_price;

          $dataModel->created_at = $timeStamp;
          $dataModel->updated_at = $timeStamp;
          $qResponce = $dataModel->save();
          if ($qResponce) {

            $response = [
              "response" => 200,
              'status' => true,
              'message' => "successfully",
              "id" => $dataModel->id

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
            'message' => "already exists in cart"
          ];
          return response($response, 200);
        }
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
}
