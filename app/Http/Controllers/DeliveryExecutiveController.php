<?php

namespace App\Http\Controllers;

use App\Helpers\notificationHelper;
use App\Models\DeliveryExecutive;
use App\Models\AssignModel;
use App\Models\SubOderDeliveyModel;
use App\Models\DeliveryExecutiveOrderModal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ImageModel;
use Illuminate\Http\Request;
use DateTime;
use Illuminate\Support\Facades\Crypt;
use App\Models\Order;
use Illuminate\Validation\ValidationException;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DeliveryExecutiveController extends Controller
{

  // Helper function to generate payment_id
  private function generateExecutiveId()
  {
    return 'dxe_' . date('YmdHis'); // Format: dxe_YYYYMMDDHHMMSS
  }

  public function addExecutiveDetails(Request $request)
  {
    try {
      $data = $request->validate([
        'name' => 'required|string',
        'email' => 'required|string',
        'phn_no1' => 'required',
        'dob' => 'required|date',
      ]);

      $deliveryExecutive = new DeliveryExecutive();
      $deliveryExecutive->executive_id = $this->generateExecutiveId();
      $deliveryExecutive->name = $request->name;
      $deliveryExecutive->email = $request->email;
      $deliveryExecutive->phn_no1 = $request->phn_no1;
      $deliveryExecutive->phn_no2 = $request->phn_no2;
      $deliveryExecutive->dob = $request->dob;
      $deliveryExecutive->country = $request->country;
      $deliveryExecutive->state = $request->state;
      $deliveryExecutive->district = $request->district;
      $deliveryExecutive->address = $request->address;
      $deliveryExecutive->doc_type = $request->doc_type;
      $deliveryExecutive->doc_no = $request->doc_no;
      $deliveryExecutive->vehicle_no = $request->vehicle_no;
      $deliveryExecutive->vehicle_ins_no = $request->vehicle_ins_no;
      $deliveryExecutive->vehicle_ins_exp_date = $request->vehicle_ins_exp_date;
      $deliveryExecutive->personal_ins_no = $request->personal_ins_no;
      $deliveryExecutive->deposit_amt = $request->deposit_amt;
      $deliveryExecutive->deposit_date = $request->deposit_date;
      $deliveryExecutive->deposit_receipt_no = $request->deposit_receipt_no;
      $deliveryExecutive->bank = $request->bank;
      $deliveryExecutive->account_no = $request->account_no;
      $deliveryExecutive->branch_name = $request->branch_name;
      $deliveryExecutive->branch_address = $request->branch_address;
      $deliveryExecutive->ifsc = $request->ifsc;
      $deliveryExecutive->upi = $request->upi;
      $deliveryExecutive->city = $request->city;
      $deliveryExecutive->doj = $request->doj;
      $deliveryExecutive->uid = $request->uid;
      $deliveryExecutive->remuneration_model = json_encode($request->renumeration_model);
      $deliveryExecutive->referred_by = $request->referred_by;
      $deliveryExecutive->specific_product_inclusion = $request->spcl_product_inclusion;
      $deliveryExecutive->comments = $request->comments;

      $deliveryExecutive->save();

      // Create a new AssignRole record
      $assignRole = new AssignModel();
      $assignRole->executive_id = $deliveryExecutive->executive_id;
      $assignRole->role_id = 4;
      $assignRole->save();

      return response()->json([
        'message' => 'Delivery executive details saved successfully!',
        'status' => 200,
        'executiveId' => $deliveryExecutive->id,
      ]);
    } catch (Exception $e) {
      Log::error('Error saving delivery executive details: ' . $e->getMessage());

      return response()->json([
        'message' => 'Failed to save delivery executive details.',
        'error' => $e->getMessage()
      ], 500);
    }
  }




  function uploadImages(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'imgData' => 'required|array',
      'id' => 'required',
    ]);

    if ($validator->fails()) {
      return response(["response" => 400], 400);
    }

    $responses = [];
    $timeStamp = date("Y-m-d H:i:s");

    foreach ($request->imgData as $imageData) {
      if (!isset($imageData['image'])) {
        continue;
      }

      $imageType = $imageData['image_type'];
      $image = $imageData['image'];

      try {
        $existingRecord = ImageModel::where('table_id', '=', $request->id)
          ->where('table_name', '=', 'delivery_executive')
          ->where('image_type', '=', $imageType)
          ->first();
        if (!is_object($image)) {
          continue;
        }
        $newName = rand() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('/uploads/images'), $newName);

        if ($existingRecord) {
          $existingRecord->image = $newName;
          $existingRecord->updated_at = $timeStamp;

          if ($existingRecord->save()) {
            $responses[] = [
              'status' => true,
              'image_type' => $imageType,
              'file' => $newName,
              'message' => "Image of type $imageType updated successfully",
            ];
          } else {
            $responses[] = [
              'status' => false,
              'image_type' => $imageType,
              'message' => "Failed to update image of type $imageType",
            ];
          }
        } else {
          $dataModel = new ImageModel();
          $dataModel->table_name = "delivery_executive";
          $dataModel->table_id = $request->id;
          $dataModel->image_type = $imageType;
          $dataModel->image = $newName;
          $dataModel->created_at = $timeStamp;
          $dataModel->updated_at = $timeStamp;

          if ($dataModel->save()) {
            $responses[] = [
              'status' => true,
              'image_type' => $imageType,
              'file' => $newName,
              'message' => "Image of type $imageType uploaded successfully",
            ];
          } else {
            $responses[] = [
              'status' => false,
              'image_type' => $imageType,
              'message' => "Failed to save image of type $imageType",
            ];
          }
        }
      } catch (Exception $e) {
        $responses[] = [
          'status' => false,
          'image_type' => $imageType,
          'message' => "Error: $e",
        ];
      }
    }

    return response(['response' => 200, 'results' => $responses], 200);
  }

  public function getExecutive()
  {
    try {
      // Fetch data from the delivery_executive table
      $deliveryExecutives = DeliveryExecutive::select('delivery_executive.id', 'delivery_executive.executive_id', 'delivery_executive.name', 'delivery_executive.email', 'delivery_executive.phn_no1', 'delivery_executive.phn_no2', 'delivery_executive.address', 'delivery_executive.city', 'delivery_executive.vehicle_no', 'delivery_executive.vehicle_ins_no', 'delivery_executive.vehicle_ins_exp_date', 'delivery_executive.is_active', 'delivery_executive.updated_at', 'images.image')
        ->leftJoin('images', function ($join) {
          $join->on('images.table_id', '=', 'delivery_executive.id')
            ->where('images.table_name', '=', "delivery_executive")
            ->where('images.image_type', '=', 8);
        })
        ->orderBy('delivery_executive.created_at', 'DESC')
        ->get();

      $deliveryExecutives = $deliveryExecutives->map(function ($executive) {
        $executive['is_active'] = $executive['is_active'] == 1 ? "true" : "false";
        // Format the vehicle_ins_exp_date
        if (!empty($executive['vehicle_ins_exp_date'])) {
          $date = new DateTime($executive['vehicle_ins_exp_date']);
          $executive['vehicle_ins_exp_date'] = $date->format('d-m-Y');
        }
        return $executive;
      });

      return response()->json([
        'status' => 200,
        'message' => 'Data retrieved successfully!',
        'data' => $deliveryExecutives,
      ]);
    } catch (Exception $e) {
      Log::error('Error fetching delivery executive data: ' . $e->getMessage());

      return response()->json([
        'message' => 'Failed to retrieve data.',
        'error' => $e->getMessage(),
      ], 500);
    }
  }


  function getExecutiveById($id)
  {
    $data = DB::table("delivery_executive")
      ->select(
        'delivery_executive.*',
        'images1.image as executive_image',
        'images2.image as doc_image',
      )
      ->leftJoin('images as images1', function ($join) {
        $join->on('images1.table_id', '=', 'delivery_executive.id')
          ->where('images1.table_name', '=', "delivery_executive")
          ->where('images1.image_type', '=', 8);
      })
      ->leftJoin('images as images2', function ($join) {
        $join->on('images2.table_id', '=', 'delivery_executive.id')
          ->where('images2.table_name', '=', "delivery_executive")
          ->where('images2.image_type', '=', 9);
      })
      ->where("delivery_executive.id", "=", $id)
      ->first();

    $response = [
      "response" => 200,
      'data' => $data,
    ];

    return response($response, 200);
  }

  function updateExecutive(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'id' => 'required',
      'email' => 'required',
      'phn_no1' => 'required',
      'dob' => 'required',
      'country' => 'required',
      'state' => 'required',
      'district' => 'required',
      'address' => 'required',
      'doc_type' => 'required',
      'doc_no' => 'required',
      'vehicle_no' => 'required',
      'vehicle_ins_no' => 'required',
      'vehicle_ins_exp_date' => 'required',
      'bank' => 'required',
      'account_no' => 'required',
      'ifsc' => 'required',
      'city' => 'required',
      'doj' => 'required',
      'uid' => 'required',
      'renumeration_model' => 'required',
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      $existingRecord = DeliveryExecutive::where('id', '=', $request->id)->first();
      $timeStamp = date("Y-m-d H:i:s");
      $existingRecord->name  = $request->name;
      $existingRecord->email  = $request->email;
      $existingRecord->phn_no1  = $request->phn_no1;
      $existingRecord->phn_no2  = $request->phn_no2;
      $existingRecord->dob  = $request->dob;
      $existingRecord->country  = $request->country;
      $existingRecord->state  = $request->state;
      $existingRecord->district  = $request->district;
      $existingRecord->address  = $request->address;
      $existingRecord->doc_type  = $request->doc_type;
      $existingRecord->doc_no  = $request->doc_no;
      $existingRecord->vehicle_no  = $request->vehicle_no;
      $existingRecord->vehicle_ins_no  = $request->vehicle_ins_no;
      $existingRecord->vehicle_ins_exp_date  = $request->vehicle_ins_exp_date;
      $existingRecord->personal_ins_no  = $request->personal_ins_no;
      $existingRecord->deposit_amt  = $request->deposit_amt;
      $existingRecord->deposit_date  = $request->deposit_date;
      $existingRecord->deposit_receipt_no  = $request->deposit_receipt_no;
      $existingRecord->bank  = $request->bank;
      $existingRecord->account_no  = $request->account_no;
      $existingRecord->branch_name  = $request->branch_name;
      $existingRecord->branch_address  = $request->branch_address;
      $existingRecord->ifsc  = $request->ifsc;
      $existingRecord->upi  = $request->upi;
      $existingRecord->city  = $request->city;
      $existingRecord->doj  = $request->doj;
      $existingRecord->uid  = $request->uid;
      $existingRecord->remuneration_model  = $request->renumeration_model;
      $existingRecord->referred_by  = $request->referred_by;
      $existingRecord->specific_product_inclusion  = $request->spcl_product_inclusion;
      $existingRecord->comments  = $request->comments;
      $existingRecord->updated_at = $timeStamp;
      $qResponce = $existingRecord->save();

      if ($qResponce) {
        $response = [
          "response" => 200,
          'status' => true,
          'message' => "successfully",
          'id' => $existingRecord->id

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

  function changeExecutiveStaus($id)
  {
    $existingRecord = DeliveryExecutive::where('id', '=', $id)->first();
    $timeStamp = date("Y-m-d H:i:s");
    $existingRecord->updated_at = $timeStamp;
    $existingRecord->is_active = !$existingRecord->is_active;
    $qResponce = $existingRecord->save();


    if ($qResponce) {
      $response = [
        "response" => 200,
        'status' => true,
        'message' => $existingRecord->is_active ? "Executive has been activated successfully"
          : "Executive has been de-activated successfully",

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

  function storePassword(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'newPassword' => 'required|string|min:6',
    ]);

    // Log the incoming password for debugging (remove in production)  
    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      // Find the delivery executive by id
      $existingRecord = DeliveryExecutive::where('id', '=', $id)->first();
      if (!$existingRecord) {
        return response()->json([
          'message' => 'Delivery executive not found.'
        ], 404);
      }
      // Encrypt the password before saving
      $encryptedPassword = Crypt::encryptString($request->newPassword);
      $existingRecord->password = $encryptedPassword;
      $qResponce = $existingRecord->save();

      if ($qResponce) {
        $response = [
          "response" => 200,
          'status' => true,
          'message' => "Password saved successfully",
        ];
      } else {
        $response = [
          "response" => 201,
          'status' => false,
          'message' => "Failed to save password",

        ];
      }
      return response($response, 200);
    }
  }

  public function getPassword($id)
  {

    $deliveryExecutive = DeliveryExecutive::find($id);
    if (!$deliveryExecutive) {
      return response()->json([
        'status' => 404,
        'message' => 'Delivery executive not found.',
      ], 404);
    }
    $encryptedPassword   = $deliveryExecutive->password;
    if (!$encryptedPassword) {
      return response()->json([
        'status' => 200,
        'message' => 'Password is not set.',
        'password' => null,
      ], 200);
    }
    try {
      // Decrypt the password
      $password = Crypt::decryptString($encryptedPassword);

      return response()->json([
        'status' => 200,
        'message' => 'Password retrieved successfully!',
        'password' => $password,
      ]);
    } catch (Exception $e) {
      Log::error('Error fetching password: ' . $e->getMessage());

      return response()->json([
        'message' => 'Failed to retrieve password.',
        'error' => $e->getMessage(),
      ], 500);
    }
  }

  public function getAllDeliveryExecutives()
  {
    try {
      // Fetch all delivery executives and sort by 'created_at' in descending order
      $executives = DeliveryExecutive::orderBy('created_at', 'desc')->get([
        'id',
        'name',
        'email',
        'phn_no1',
        'phn_no2',
        'executive_id',
        'is_active',
        'created_at'
      ]);

      $response = [
        "response" => 200,
        "status" => true,
        "message" => "Delivery executives fetched successfully",
        "data" => $executives
      ];

      return response($response, 200);
    } catch (Exception $e) {
      $response = [
        "response" => 500,
        "status" => false,
        "message" => "An error occurred while fetching the delivery executives: " . $e->getMessage()
      ];
      return response($response, 500);
    }
  }

  // public function getOrdersByDate($date, $exe_id)
  // {
  //   try {
  //     // Step 1: Fetch order IDs for the given delivery executive and assigned date
  //     $orderIds = DB::table('delivery_executive_orders')
  //       ->where('delivery_executive_id', $exe_id)
  //       ->whereDate('assigned_date', $date)
  //       ->pluck('order_id')
  //       ->toArray();

  //     // Step 2: Fetch all the orders using the retrieved orderIds
  //     $orders = DB::table('orders')
  //       ->leftJoin('user_address', 'orders.address_id', '=', 'user_address.id')
  //       ->leftJoin('subscribed_order_delivery', function ($join) use ($exe_id, $date) {
  //         $join->on('subscribed_order_delivery.order_id', '=', 'orders.id')
  //           ->whereDate('subscribed_order_delivery.date', $date)
  //           ->where('subscribed_order_delivery.executive_id', '=', $exe_id);
  //       })
  //       ->whereIn('orders.id', $orderIds)
  //       ->where('orders.status', 1)
  //       ->select(
  //         'orders.order_number',
  //         'orders.id',
  //         'user_address.s_phone',
  //         'user_address.name',
  //         'user_address.city',
  //         'user_address.flat_no',
  //         'user_address.apartment_name',
  //         'user_address.landmark',
  //         'user_address.area',
  //         'user_address.pincode',
  //         'orders.pause_dates' // Include pause_dates field for checking
  //       )
  //       ->get()
  //       ->map(function ($order) use ($date, $exe_id) {
  //         // Step 3: Decode pause_dates if present
  //         $pauseDates = $order->pause_dates ? array_map(function ($date) {
  //           return Carbon::parse(trim($date));
  //         }, explode(',', trim($order->pause_dates, '[]'))) : [];

  //         // Step 4: Check if the assigned_date falls within the pause_dates range
  //         if (!empty($pauseDates)) {
  //           $assignedDate = Carbon::parse($date);
  //           foreach ($pauseDates as $pauseDate) {
  //             if ($assignedDate->equalTo($pauseDate)) {
  //               return null; // Exclude this order if it matches a pause date
  //             }
  //           }
  //         }

  //         // Fetch additional data from the delivery_executive_orders table
  //         $deliveryExecutiveOrder = DB::table('delivery_executive_orders')
  //           ->where('order_id', $order->id)
  //           ->where('delivery_executive_id', $exe_id)
  //           ->whereDate('assigned_date', $date)
  //           ->orderBy('updated_at', 'desc')
  //           ->select('is_reassign_requested', 'updated_stock', 'id')
  //           ->limit(1)
  //           ->first();

  //         // Decode the updated_stock JSON string
  //         $updatedStock = json_decode($deliveryExecutiveOrder->updated_stock, true) ?: [];

  //         // Fetch the subscription ID
  //         $subscription_id = DB::table('subscribed_order_delivery')
  //           ->where('order_id', $order->id)
  //           ->where('executive_id', $exe_id)
  //           ->whereDate('date', $date)
  //           ->value('id');

  //         // Add additional properties to the order
  //         $order->is_reassigned = $deliveryExecutiveOrder->is_reassign_requested == 1;
  //         $order->updated_stock = $updatedStock;
  //         $order->subs_id = $subscription_id;
  //         $order->delivery_executive_orders_id = $deliveryExecutiveOrder->id;

  //         return $order;
  //       });

  //     // Step 5: Filter out null results (orders that should be excluded due to pause_dates)
  //     $orders = $orders->filter(function ($order) {
  //       return $order !== null;
  //     });

  //     // Return the response
  //     return response()->json([
  //       'status' => true,
  //       'message' => 'Orders fetched successfully',
  //       'data' => $orders,
  //     ]);
  //   } catch (ValidationException $e) {
  //     return response()->json([
  //       'status' => false,
  //       'message' => 'Validation failed',
  //       'errors' => $e->errors(),
  //     ], 400);
  //   } catch (Exception $e) {
  //     return response()->json([
  //       'status' => false,
  //       'message' => 'An error occurred while fetching the orders',
  //       'error' => $e->getMessage(),
  //     ], 500);
  //   }
  // }

  public function getOrdersByDate($date, $exe_id)
  {
    try {
      // Ensure consistent date format
      $parsedDate = Carbon::parse($date)->toDateString();

      // Step 1: Fetch relevant order IDs
      $orderIds = DB::table('delivery_executive_orders')
        ->where('delivery_executive_id', $exe_id)
        ->whereDate('assigned_date', $parsedDate)
        ->pluck('order_id')
        ->toArray();

      // Step 2: Prepare subquery for subscribed_order_delivery
      $subDeliveryQuery = DB::table('subscribed_order_delivery')
        ->select('id as subs_id', 'order_id')
        ->whereDate('date', $parsedDate)
        ->where('executive_id', $exe_id);

      // Step 3: Main query with joins and subquery
      $orders = DB::table('orders')
        ->leftJoin('user_address', 'orders.address_id', '=', 'user_address.id')
        ->leftJoinSub($subDeliveryQuery, 'sod', function ($join) {
          $join->on('sod.order_id', '=', 'orders.id');
        })
        ->whereIn('orders.id', $orderIds)
        ->where('orders.status', 1)
        ->select(
          'orders.order_number',
          'orders.id',
          'user_address.s_phone',
          'user_address.name',
          'user_address.city',
          'user_address.flat_no',
          'user_address.apartment_name',
          'user_address.landmark',
          'user_address.area',
          'user_address.pincode',
          'orders.pause_dates',
          'sod.subs_id'
        )
        ->get()
        ->map(function ($order) use ($parsedDate, $exe_id) {
          // Step 4: Handle pause_dates
          $pauseDates = $order->pause_dates ? array_map(function ($d) {
            return Carbon::parse(trim($d))->toDateString();
          }, explode(',', trim($order->pause_dates, '[]'))) : [];

          if (in_array($parsedDate, $pauseDates)) {
            return null; // Skip this order
          }

          // Step 5: Get delivery_executive_orders data
          $deliveryExecutiveOrder = DB::table('delivery_executive_orders')
            ->where('order_id', $order->id)
            ->where('delivery_executive_id', $exe_id)
            ->whereDate('assigned_date', $parsedDate)
            ->orderBy('updated_at', 'desc')
            ->select('is_reassign_requested', 'updated_stock', 'id')
            ->first();

          if (!$deliveryExecutiveOrder) {
            return null;
          }

          $updatedStock = json_decode($deliveryExecutiveOrder->updated_stock, true) ?: [];

          // Attach extra fields
          $order->is_reassigned = $deliveryExecutiveOrder->is_reassign_requested == 1;
          $order->updated_stock = $updatedStock;
          $order->delivery_executive_orders_id = $deliveryExecutiveOrder->id;

          return $order;
        })
        ->filter()        // Remove nulls
        ->unique('id')    // Prevent duplicates
        ->values();       // Reset collection keys

      return response()->json([
        'status' => true,
        'message' => 'Orders fetched successfully',
        'data' => $orders,
      ]);
    } catch (ValidationException $e) {
      return response()->json([
        'status' => false,
        'message' => 'Validation failed',
        'errors' => $e->errors(),
      ], 400);
    } catch (Exception $e) {
      return response()->json([
        'status' => false,
        'message' => 'An error occurred while fetching the orders',
        'error' => $e->getMessage(),
      ], 500);
    }
  }

  public function getStockOrdersByDate($date, $exe_id)
  {
    try {
      // Step 1: Normalize date input using Carbon
      $parsedDate = Carbon::parse($date)->toDateString();

      // Step 2: Fetch orders NOT already delivered (i.e., not in subscribed_order_delivery)
      $orderDetails = DB::table('delivery_executive_orders')
        ->join('orders', 'delivery_executive_orders.order_id', '=', 'orders.id')
        ->where('orders.status', 1)
        ->where('delivery_executive_orders.delivery_executive_id', $exe_id)
        ->whereDate('delivery_executive_orders.assigned_date', $parsedDate)
        ->where('delivery_executive_orders.is_reassign_requested', 0)
        ->whereNotExists(function ($query) use ($parsedDate, $exe_id) {
          $query->select(DB::raw(1))
            ->from('subscribed_order_delivery')
            ->whereColumn('subscribed_order_delivery.order_id', 'delivery_executive_orders.order_id')
            ->whereDate('subscribed_order_delivery.date', $parsedDate)
            ->where('subscribed_order_delivery.executive_id', $exe_id);
        })
        ->select(
          'orders.*',
          'delivery_executive_orders.id as executive_assign_id',
          'delivery_executive_orders.updated_stock',
          'orders.pause_dates'
        )
        ->get();

      // Step 3: Transform each order
      $orderDetails->transform(function ($order) use ($parsedDate) {
        // Decode pause_dates and skip if date matches any pause
        $pauseDates = $order->pause_dates ? array_map(function ($d) {
          return Carbon::parse(trim($d))->toDateString();
        }, explode(',', trim($order->pause_dates, '[]'))) : [];

        if (in_array($parsedDate, $pauseDates)) {
          return null;
        }

        // Process product details
        if ($order->subscription_type !== null) {
          // Subscribed order: fetch single product
          $product = DB::table('product')
            ->where('id', $order->product_id)
            ->select('id as product_id', 'title as product_title')
            ->first();

          $image = DB::table('images')
            ->where('table_id', $product->product_id)
            ->where('table_name', 'product')
            ->where('image_type', 1)
            ->value('image');

          $order->product_detail = json_encode([[
            'product_id' => $product->product_id,
            'product_title' => $product->product_title,
            'qty' => $order->qty,
            'tax' => $order->tax,
            'mrp' => $order->mrp,
            'price' => $order->price,
            'img_src' => $image,
          ]]);
        } else {
          // Non-subscription order with multiple products
          $productDetails = json_decode($order->product_detail ?? '[]', true);

          foreach ($productDetails as &$product) {
            $image = DB::table('images')
              ->where('table_id', $product['product_id'])
              ->where('table_name', 'product')
              ->where('image_type', 1)
              ->value('image');

            $product['img_src'] = $image ?? null;
          }

          $order->product_detail = json_encode($productDetails);
        }

        return $order;
      });

      // Step 4: Remove null entries
      $orderDetails = $orderDetails->filter()->values();

      return response()->json([
        'status' => true,
        'message' => 'Stock Orders fetched successfully',
        'data' => $orderDetails,
      ]);
    } catch (Exception $e) {
      return response()->json([
        'status' => false,
        'message' => 'Error fetching stock orders: ' . $e->getMessage(),
      ], 500);
    }
  }


  // public function getStockOrdersByDate($date, $exe_id)
  // {
  //   try {
  //     // Fetch order details excluding orders already in subscribed_order_delivery
  //     $orderDetails = DB::table('delivery_executive_orders')
  //       ->join('orders', 'delivery_executive_orders.order_id', '=', 'orders.id')
  //       ->where('orders.status', 1)
  //       ->where('delivery_executive_orders.delivery_executive_id', $exe_id)
  //       ->whereDate('delivery_executive_orders.assigned_date', $date)
  //       ->where('delivery_executive_orders.is_reassign_requested', 0)
  //       ->whereNotExists(function ($query) use ($date, $exe_id) {
  //         $query->select(DB::raw(1))
  //           ->from('subscribed_order_delivery')
  //           ->whereColumn('subscribed_order_delivery.order_id', 'delivery_executive_orders.order_id')
  //           ->whereDate('subscribed_order_delivery.date', $date)
  //           ->where('subscribed_order_delivery.executive_id', $exe_id);
  //       })
  //       ->select(
  //         'orders.*',
  //         'delivery_executive_orders.id as executive_assign_id',
  //         'delivery_executive_orders.updated_stock',
  //         'orders.pause_dates' // Include pause_dates field for checking
  //       )
  //       ->get();

  //     // Process the orders to include product details and updated stock
  //     $orderDetails->transform(function ($order) use ($date) {
  //       // Step 1: Decode pause_dates if present
  //       $pauseDates = $order->pause_dates ? array_map(function ($date) {
  //         return Carbon::parse(trim($date));
  //       }, explode(',', trim($order->pause_dates, '[]'))) : [];

  //       // Step 2: Check if the assigned_date falls within the pause_dates range
  //       if (!empty($pauseDates)) {
  //         $assignedDate = Carbon::parse($date);
  //         foreach ($pauseDates as $pauseDate) {
  //           if ($assignedDate->equalTo($pauseDate)) {
  //             return null; // Exclude this order if it matches a pause date
  //           }
  //         }
  //       }

  //       // If the order is not excluded, process the product details and updated stock
  //       if ($order->subscription_type !== null) {
  //         $product = DB::table('product')
  //           ->where('id', $order->product_id)
  //           ->select('id as product_id', 'title as product_title')
  //           ->first();

  //         $image = DB::table('images')
  //           ->where('table_id', $product->product_id)
  //           ->where('table_name', 'product')
  //           ->where('image_type', '=', 1)
  //           ->select('image')
  //           ->first();

  //         $order->product_detail = json_encode([[
  //           'product_id' => $product->product_id,
  //           'product_title' => $product->product_title,
  //           'qty' => $order->qty,
  //           'tax' => $order->tax,
  //           'mrp' => $order->mrp,
  //           'price' => $order->price,
  //           'img_src' => $image->image ?? null,
  //         ]]);
  //       } else {
  //         $productDetails = json_decode($order->product_detail, true) ?: [];

  //         foreach ($productDetails as &$product) {
  //           $image = DB::table('images')
  //             ->where('table_id', $product['product_id'])
  //             ->where('table_name', 'product')
  //             ->where('image_type', '=', 1)
  //             ->select('image')
  //             ->first();

  //           $product['img_src'] = $image->image ?? null;
  //         }

  //         $order->product_detail = json_encode($productDetails);
  //       }

  //       return $order;
  //     });

  //     // Step 3: Filter out null results (orders that should be excluded due to pause_dates)
  //     $orderDetails = $orderDetails->filter(function ($order) {
  //       return $order !== null;
  //     });

  //     return response()->json([
  //       'status' => true,
  //       'message' => 'Stock Orders fetched successfully',
  //       'data' => $orderDetails,
  //     ]);
  //   } catch (Exception $e) {
  //     return response()->json([
  //       'status' => false,
  //       'message' => 'Error fetching stock orders: ' . $e->getMessage(),
  //     ], 500);
  //   }
  // }

  public function UpdateStocks(Request $request, $id, $exe_id, $date, $executive_assign_id)
  {
    try {
      $validatedData = $request->validate([
        'confirmed_quantity' => 'required',
        'product_id' => 'required',
        'product_title' => 'required',
      ]);

      // Find the delivery_executive_order entry by order_id
      $deliveryOrder = DeliveryExecutiveOrderModal::where('delivery_executive_id', $exe_id)
        ->where('id', $executive_assign_id)
        ->where('order_id', $id)
        ->whereDate('assigned_date', $date)
        ->where('is_reassign_requested', false)
        ->firstOrFail();

      // Decode the existing updated_stock JSON data
      $existingStock = json_decode($deliveryOrder->updated_stock, true) ?: [];

      // Check if the product_id already exists in the updated_stock array
      $productFound = false;
      foreach ($existingStock as &$stockItem) {
        if ($stockItem['product_id'] == $validatedData['product_id']) {
          // Update the existing product details
          $stockItem['confirmed_quantity'] = $validatedData['confirmed_quantity'];
          $stockItem['product_title'] = $validatedData['product_title'];
          $productFound = true;
          break;
        }
      }

      // If product_id does not exist, append the new data
      if (!$productFound) {
        $existingStock[] = $validatedData;
      }

      // Encode the updated stock data as JSON
      $updatedStock = json_encode($existingStock);

      // Update the delivery_executive_order's updated_stock column
      $deliveryOrder->updated_stock = $updatedStock;
      $deliveryOrder->save();

      return response()->json([
        'status' => true,
        'message' => 'Stock updated successfully',
        'data' => $deliveryOrder,
      ]);
    } catch (ValidationException $e) {
      return response()->json([
        'status' => false,
        'message' => 'Validation failed',
        'errors' => $e->errors(),
      ], 400);
    } catch (Exception $e) {
      return response()->json([
        'status' => false,
        'message' => 'An error occurred while updating the stock',
        'error' => $e->getMessage(),
      ], 500);
    }
  }


  public function getDeliveryOrdersByDate($date, $exe_id)
  {
    try {
      // Step 1: Parse the date using Carbon
      $parsedDate = Carbon::parse($date)->toDateString();

      // Step 2: Get order IDs assigned to this executive on this date
      $orderIds = DB::table('delivery_executive_orders')
        ->where('delivery_executive_id', $exe_id)
        ->whereDate('assigned_date', $parsedDate)
        ->where('is_reassign_requested', false)
        ->whereNotNull('updated_stock')
        ->pluck('order_id')
        ->toArray();

      // Step 3: Subquery to get subscribed delivery info
      $subscribedDelivery = DB::table('subscribed_order_delivery')
        ->select('id as subs_id', 'order_id', 'delivery_notes', 'updated_at as subs_updated_at')
        ->where('executive_id', $exe_id)
        ->whereDate('date', $parsedDate);

      // Step 4: Fetch order details with address and subscribed delivery info
      $orders = DB::table('orders')
        ->leftJoin('user_address', 'orders.address_id', '=', 'user_address.id')
        ->leftJoinSub($subscribedDelivery, 'sod', function ($join) {
          $join->on('orders.id', '=', 'sod.order_id');
        })
        ->whereIn('orders.id', $orderIds)
        ->where('orders.status', 1)
        ->select(
          'orders.*',
          'user_address.name',
          'user_address.s_phone',
          'user_address.pincode',
          'user_address.area',
          'user_address.landmark',
          'user_address.flat_no',
          'user_address.city',
          'user_address.apartment_name',
          'sod.subs_id',
          'sod.delivery_notes',
          'sod.subs_updated_at'
        )
        ->distinct()
        ->get();

      // Step 5: Fetch updated stock separately
      $updatedStocks = DB::table('delivery_executive_orders')
        ->whereIn('order_id', $orderIds)
        ->where('delivery_executive_id', $exe_id)
        ->whereDate('assigned_date', $parsedDate)
        ->where('is_reassign_requested', false)
        ->whereNotNull('updated_stock')
        ->select('order_id', 'updated_stock')
        ->get();

      // Step 6: Attach updated stock and product images to each order
      $orders->transform(function ($order) use ($updatedStocks) {
        $updatedStockData = $updatedStocks->firstWhere('order_id', $order->id);
        $updatedStockItems = json_decode($updatedStockData->updated_stock ?? '[]', true);
        $productDetails = json_decode($order->product_detail ?? '[]', true);

        // If not a subscription, check that all product_ids match
        if ($order->subscription_type === null) {
          $allMatch = collect($productDetails)->every(function ($product) use ($updatedStockItems) {
            return collect($updatedStockItems)->contains('product_id', $product['product_id']);
          });

          if (!$allMatch) {
            return null;
          }
        }

        // Attach product image to each updated stock item
        foreach ($updatedStockItems as &$item) {
          $item['img_src'] = null;
          if (!empty($item['product_id'])) {
            $item['img_src'] = DB::table('images')
              ->where('table_id', $item['product_id'])
              ->where('table_name', 'product')
              ->where('image_type', 1)
              ->value('image');
          }
        }

        $order->updated_stock = json_encode($updatedStockItems);
        return $order;
      });

      // Step 7: Remove null orders (due to mismatch filtering)
      $orders = $orders->filter()->unique('id')->values();

      return response()->json([
        'status' => true,
        'message' => 'Orders fetched successfully',
        'data' => $orders
      ]);
    } catch (ValidationException $e) {
      return response()->json([
        'status' => false,
        'message' => 'Validation failed',
        'errors' => $e->errors(),
      ], 400);
    } catch (Exception $e) {
      return response()->json([
        'status' => false,
        'message' => 'An error occurred while fetching the orders',
        'error' => $e->getMessage(),
      ], 500);
    }
  }



  // public function getDeliveryOrdersByDate($date, $exe_id)
  // {
  //   try {
  //     // Fetch order IDs from delivery_executive_orders
  //     $orderIds = DB::table('delivery_executive_orders')
  //       ->where('delivery_executive_id', $exe_id)
  //       ->whereDate('assigned_date', $date)
  //       ->where('is_reassign_requested', false)
  //       ->whereNotNull('updated_stock') // Ensure updated_stock is not null
  //       ->pluck('order_id')
  //       ->toArray();

  //     // Fetch orders with user addresses and subscription details
  //     $orders = DB::table('orders')
  //       ->leftJoin('user_address', 'orders.address_id', '=', 'user_address.id')
  //       ->leftJoin('subscribed_order_delivery', function ($join) use ($exe_id, $date) {
  //         $join->on('subscribed_order_delivery.order_id', '=', 'orders.id')
  //           ->whereDate('subscribed_order_delivery.date', $date)
  //           ->where('subscribed_order_delivery.executive_id', '=', $exe_id);
  //       })
  //       ->whereIn('orders.id', $orderIds)
  //       ->where('orders.status', '=', 1)
  //       ->select(
  //         'orders.*',
  //         'user_address.name',
  //         'user_address.s_phone',
  //         'user_address.pincode',
  //         'user_address.area',
  //         'user_address.landmark',
  //         'user_address.flat_no',
  //         'user_address.city',
  //         'user_address.apartment_name',
  //         'subscribed_order_delivery.order_id as subs_id',
  //         'subscribed_order_delivery.delivery_notes',
  //         'subscribed_order_delivery.updated_at as subs_updated_at'
  //       )
  //       ->get();

  //     // Fetch updated_stock from delivery_executive_orders
  //     $updatedStocks = DB::table('delivery_executive_orders')
  //       ->whereIn('order_id', $orderIds)
  //       ->where('delivery_executive_id', $exe_id)
  //       ->whereDate('assigned_date', $date)
  //       ->where('is_reassign_requested', false)
  //       ->whereNotNull('updated_stock')
  //       ->select('order_id', 'updated_stock')
  //       ->get();

  //     // Process the orders to include updated_stock and images
  //     $orders->transform(function ($order) use ($updatedStocks) {
  //       // Retrieve the updated stock for the current order
  //       $updatedStockData = $updatedStocks->firstWhere('order_id', $order->id);

  //       // Decode the updated_stock JSON and product_detail JSON
  //       $updatedStockItems = json_decode($updatedStockData->updated_stock ?? '[]', true);
  //       $productDetails = json_decode($order->product_detail ?? '[]', true);

  //       // If subscription_type is null, filter based on product_id match
  //       if ($order->subscription_type === null) {
  //         $allProductsMatch = collect($productDetails)->every(function ($product) use ($updatedStockItems) {
  //           return collect($updatedStockItems)->contains('product_id', $product['product_id']);
  //         });

  //         // If not all product IDs match, exclude this order
  //         if (!$allProductsMatch) {
  //           return null; // Skip this order
  //         }
  //       }

  //       // Process each stock item to include images
  //       foreach ($updatedStockItems as &$stockItem) {
  //         if (!empty($stockItem['product_id'])) {
  //           $image = DB::table('images')
  //             ->where('table_id', $stockItem['product_id'])
  //             ->where('table_name', 'product')
  //             ->where('image_type', '=', 1)
  //             ->select('image')
  //             ->first();

  //           // Add image source to the stock item
  //           $stockItem['img_src'] = $image->image ?? null;
  //         } else {
  //           // Handle missing product_id
  //           $stockItem['img_src'] = null;
  //         }
  //       }

  //       // Encode the processed updated stock back to JSON
  //       $order->updated_stock = json_encode($updatedStockItems);

  //       return $order;
  //     });

  //     // Remove null values from the collection (excluded orders)
  //     $orders = $orders->filter();

  //     return response()->json([
  //       'status' => true,
  //       'message' => 'Orders fetched successfully',
  //       'data' => $orders
  //     ]);
  //   } catch (ValidationException $e) {
  //     return response()->json([
  //       'status' => false,
  //       'message' => 'Validation failed',
  //       'errors' => $e->errors(),
  //     ], 400);
  //   } catch (Exception $e) {
  //     return response()->json([
  //       'status' => false,
  //       'message' => 'An error occurred while fetching the orders',
  //       'error' => $e->getMessage(),
  //     ], 500);
  //   }
  // }

  // public function getDeliveryOrdersByDate($date, $exe_id)
  // {
  //   try {
  //     $orderIds = DB::table('delivery_executive_orders')
  //       ->where('delivery_executive_id', $exe_id)
  //       ->whereDate('assigned_date', $date)
  //       ->where('is_reassign_requested', false)
  //       ->whereNotNull('updated_stock') // Ensure updated_stock is not null
  //       ->pluck('order_id')
  //       ->toArray();

  //     $orders = DB::table('orders')
  //       ->leftJoin('user_address', 'orders.address_id', '=', 'user_address.id')
  //       ->leftJoin('subscribed_order_delivery', function ($join) use ($exe_id, $date) {
  //         $join->on('subscribed_order_delivery.order_id', '=', 'orders.id')
  //           ->whereDate('subscribed_order_delivery.date', $date)
  //           ->where('subscribed_order_delivery.executive_id', '=', $exe_id);
  //       })
  //       ->whereIn('orders.id', $orderIds)
  //       ->select(
  //         'orders.*',
  //         'user_address.name',
  //         'user_address.s_phone',
  //         'user_address.pincode',
  //         'user_address.area',
  //         'user_address.landmark',
  //         'user_address.flat_no',
  //         'user_address.city',
  //         'user_address.apartment_name',
  //         'subscribed_order_delivery.order_id as subs_id',
  //         'subscribed_order_delivery.delivery_notes'
  //       )
  //       ->get();

  //     // Fetch updated_stock from delivery_executive_orders
  //     $updatedStocks = DB::table('delivery_executive_orders')
  //       ->whereIn('order_id', $orderIds)
  //       ->where('delivery_executive_id', $exe_id)
  //       ->whereDate('assigned_date', $date)
  //       ->where('is_reassign_requested', false)
  //       ->whereNotNull('updated_stock')
  //       ->select('order_id', 'updated_stock')
  //       ->get();
  //     // Process the orders to include updated_stock and images
  //     $orders->transform(function ($order) use ($updatedStocks) {
  //       // Retrieve the updated stock for the current order
  //       $updatedStockData = $updatedStocks->firstWhere('order_id', $order->id);

  //       // Decode the updated_stock JSON, or set it to an empty array if null
  //       $updatedStockItems = json_decode($updatedStockData->updated_stock ?? '[]', true);

  //       // Process each stock item to include images
  //       foreach ($updatedStockItems as &$stockItem) {
  //         if (!empty($stockItem['product_id'])) {
  //           $image = DB::table('images')
  //             ->where('table_id', $stockItem['product_id'])
  //             ->where('table_name', 'product')
  //             ->where('image_type', '=', 1)
  //             ->select('image')
  //             ->first();

  //           // Add image source to the stock item
  //           $stockItem['img_src'] = $image->image ?? null;
  //         } else {
  //           // Handle missing product_id
  //           $stockItem['img_src'] = null;
  //         }
  //       }

  //       // Encode the processed updated stock back to JSON
  //       $order->updated_stock = json_encode($updatedStockItems);

  //       return $order;
  //     });


  //     return response()->json([
  //       'status' => true,
  //       'message' => 'Orders fetched successfully',
  //       'data' => $orders
  //     ]);
  //   } catch (ValidationException $e) {
  //     return response()->json([
  //       'status' => false,
  //       'message' => 'Validation failed',
  //       'errors' => $e->errors(),
  //     ], 400);
  //   } catch (Exception $e) {
  //     return response()->json([
  //       'status' => false,
  //       'message' => 'An error occurred while fetching the orders',
  //       'error' => $e->getMessage(),
  //     ], 500);
  //   }
  // }

  public function storeDeliveredInfo(Request $request, $exe_id)
  {
    try {
      // Validate input
      $validatedData = $request->validate([
        'order_id' => 'required',
        'entry_userId' => 'required',
        'comments' => 'nullable|string',
      ]);

      $deliveryExecutiveOrder = DB::table('delivery_executive_orders')
        ->where('delivery_executive_id', $exe_id)
        ->where('order_id', $validatedData['order_id'])
        ->first();

      if (!$deliveryExecutiveOrder) {
        return response()->json([
          'status' => false,
          'message' => 'Delivery executive or order not found',
        ], 404);
      }

      // Fetch the order
      $order = DB::table('orders')
        ->where('id', $deliveryExecutiveOrder->order_id)
        ->first();

      if (!$order) {
        return response()->json([
          'status' => false,
          'message' => 'Order not found',
        ], 404);
      }

      // Check if subscription_type is null and update delivery_status
      if (is_null($order->subscription_type)) {
        DB::table('orders')
          ->where('id', $validatedData['order_id'])
          ->update(['delivery_status' => 1]);
      }

      // Store delivery info
      $deliveryInfo = SubOderDeliveyModel::create([
        'order_id' => $validatedData['order_id'],
        'executive_id' => $validatedData['entry_userId'],
        'date' => Carbon::now()->toDateString(),
        'payment_mode' => 1, // Set payment_mode as 1
        'delivery_notes' => $validatedData['comments'] ?? "",
        'created_at' => Carbon::now(),
        'updated_at' => Carbon::now(),
      ]);
      try {
        if ($order->subscription_type) {
          $notificationResponse = notificationHelper::subscriptionDeliveredNotify(
            $request->order_id,
            $order->order_number,
            $order->user_id
          );
        } else {
          $notificationResponse = notificationHelper::buyOnceDeliveredNotify(
            $request->order_id,
            $order->order_number,
            $order->user_id
          );
        }
      } catch (Exception $e) {
        // Log the error but do not affect the order processing
        Log::error("Delivery Notification Error: " . $e->getMessage());
      }

      return response()->json([
        'status' => true,
        'message' => 'Delivery info stored successfully',
        'data' => $deliveryInfo,
      ]);
    } catch (ValidationException $e) {
      return response()->json([
        'status' => false,
        'message' => 'Validation failed',
        'errors' => $e->errors(),
      ], 400);
    } catch (Exception $e) {
      return response()->json([
        'status' => false,
        'message' => 'An error occurred while storing the delivery info',
        'error' => $e->getMessage(),
      ], 500);
    }
  }

  function getExecutiveEmails()
  {
    try {
      $emails = DB::table('delivery_executive')
        ->join('assign_role', function ($join) {
          $join->on(
            DB::raw('CONVERT(delivery_executive.executive_id USING utf8mb4)'),
            '=',
            DB::raw('CONVERT(assign_role.executive_id USING utf8mb4)')
          );
        })
        ->where('assign_role.role_id', 4)
        ->pluck('delivery_executive.email')
        ->toArray();
      return [
        'status' => true,
        'message' => 'Emails fetched successfully.',
        'data' => $emails
      ];
    } catch (Exception $e) {
      return [
        'status' => false,
        'error' => 'Error fetching emails: ' . $e->getMessage()
      ];
    }
  }

  public function reAssignExecutive(Request $request)
  {
    try {
      $request->validate([
        'order_id' => 'required|integer',
      ]);

      $exe_id = $request->input('exe_id');
      $isFrom = $request->input('isFrom');
      $order_id = $request->input('order_id');
      $assignDate = $request->input('date');
      $executive_assign_id = $request->input('executive_assign_id');

      if ($isFrom === 'submit' && $exe_id) {
        DB::table('delivery_executive_orders')
          ->where('id', $executive_assign_id)
          ->whereDate('assigned_date', $assignDate)
          ->where('order_id', $order_id)
          ->update([
            'reassigned_executive_id' => $exe_id,
            'is_reassign_requested' => 1,
          ]);
      } else {
        DB::table('delivery_executive_orders')
          ->where('id', $executive_assign_id)
          ->whereDate('assigned_date', $assignDate)
          ->where('order_id', $order_id)
          ->update([
            'is_reassign_requested' => 1,
          ]);
      }

      return response()->json([
        'status' => true,
        'message' => 'Executive reassigned successfully',
      ]);
    } catch (Exception $e) {
      return response()->json([
        'status' => false,
        'message' => 'Error reassigning executive: ' . $e->getMessage(),
      ], 500);
    }
  }

  public function getExecutivesByPincode($pincode, $exe_id)
  {
    try {
      $routeId = DB::table('delivery_routes')
        ->where('pincode', $pincode)
        ->value('id');

      $executiveIds = DB::table('delivery_executive_route')
        ->where('delivery_route_id', $routeId)
        ->where('delivery_executive_id', '!=', $exe_id)
        ->pluck('delivery_executive_id')
        ->toArray();

      $executives = DB::table('delivery_executive')
        ->whereIn('id', $executiveIds)
        ->where('is_active', 1)
        ->select('id as executive_id', 'name', 'email')
        ->get()
        ->map(function ($executive) {
          return [
            'executive_id' => $executive->executive_id,
            'name' => $executive->name,
            'email' => $executive->email,
          ];
        })
        ->toArray();

      return response()->json([
        'status' => true,
        'message' => 'Executives fetched successfully',
        'data' => $executives,
      ]);
    } catch (Exception $e) {
      return response()->json([
        'status' => false,
        'message' => 'Error fetching executives: ' . $e->getMessage(),
      ], 500);
    }
  }
}
