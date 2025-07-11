<?php

namespace App\Http\Controllers;

use App\Models\PurchasInvoiceModel;
use App\Models\PurchasOrderModel;
use App\Models\PurchaseProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\ImageModel;
use App\Models\ProductModel;
use App\Http\Controllers\WebAppSettingsController;
use App\Models\User;

class PurchaseOrderController extends Controller
{
  function addData(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'supplier_id' => 'required',
      'warehouse_id' => 'required',
      'city' => 'required',
      'date_of_po' => 'required',
      'date_of_delivery' => 'required',
      'delivery_time' => 'required',
      'productData' => 'required',
      'status' => 'required',
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      $timeStamp = date("Y-m-d H:i:s");
      $dataModel = new PurchasOrderModel();
      $dataModel->po_no  = $this->generatePONumber();
      $dataModel->supplier_id  = $request->supplier_id;
      $dataModel->warehouse_id  = $request->warehouse_id;
      $dataModel->city  = $request->city;
      $dataModel->date_of_po  = $request->date_of_po;
      $dataModel->date_of_delivery  = $request->date_of_delivery;
      $dataModel->delivery_time  = $request->delivery_time;
      $dataModel->po_status  = $request->status;
      $dataModel->created_at = $timeStamp;
      $dataModel->updated_at = $timeStamp;
      $qResponce = $dataModel->save();

      $totalAmount = 0;
      $noOfQty = 0;
      $noOfProduct = count($request->productData);

      try {
        foreach ($request->productData as $product) {
          if($product['quantity'] == 0){
            continue;
          }
          $purchaseProduct = new PurchaseProductModel();
          $purchaseProduct->purchase_id = $dataModel->id;
          $purchaseProduct->product_id = $product['product_id'];
          $purchaseProduct->price = $product['price'];
          $purchaseProduct->tax = $product['tax'];
          $purchaseProduct->quantity = $product['quantity'];
          $purchaseProduct->amount = $product['amount'];
          $purchaseProduct->tax_amount = $product['tax_amount'];
          $purchaseProduct->net_amount = $product['net_amount'];
          $purchaseProduct->comments = $product['comments'];
          $purchaseProduct->created_at = $timeStamp;
          $purchaseProduct->updated_at = $timeStamp;
          $purchaseProduct->save();

          $totalAmount += $product['net_amount'];
          $noOfQty += $product['quantity'];
      }
      $dataModel->total_amount = $totalAmount;
      $dataModel->no_of_qty = $noOfQty;
      $dataModel->no_of_products = $noOfProduct;
      $dataModel->save();
      } catch (\Exception $e) {
        $response = [
          "response"=>201,
          'status'=>false,
          'message' => "error",
  
        ];
        return response($response, 200);
      }
      

      if($qResponce) {
        $response = [
          "response" => 200,
          'status' => true,
          'message' => "successfully"

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
  function updateData(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'id' => 'required',
      'supplier_id' => 'required',
      'warehouse_id' => 'required',
      'city' => 'required',
      'date_of_po' => 'required',
      'date_of_delivery' => 'required',
      'delivery_time' => 'required',
      'productData' => 'required',
      'status' => 'required',
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      $existingRecord = PurchasOrderModel::where('id', '=', $request->id)->first();
      $timeStamp = date("Y-m-d H:i:s");
      $existingRecord->supplier_id = $request->supplier_id;
      $existingRecord->warehouse_id  = $request->warehouse_id;
      $existingRecord->city  = $request->city;
      $existingRecord->date_of_po  = $request->date_of_po;
      $existingRecord->date_of_delivery  = $request->date_of_delivery;
      $existingRecord->delivery_time  = $request->delivery_time;
      $existingRecord->po_status  = $request->status;
      $existingRecord->updated_at = $timeStamp;
      $qResponce = $existingRecord->save();

      $noOfQty = 0;
      $totalAmount = 0;
      $noOfProduct = count($request->productData);

      PurchaseProductModel::where('purchase_id', $existingRecord->id)->delete();
      
      foreach ($request->productData as $product) {
        if($product['quantity'] == 0){
          continue;
        }
        $purchaseProduct = PurchaseProductModel::where('id','=', $product['id'])
            ->where('product_id', $product['product_id'])
            ->first();

        if ($purchaseProduct) {
            // Update existing product if it exists
            $purchaseProduct->price = $product['price'];
            $purchaseProduct->tax = $product['tax'];
            $purchaseProduct->quantity = $product['quantity'];
            $purchaseProduct->amount = $product['amount'];
            $purchaseProduct->tax_amount = $product['tax_amount'];
            $purchaseProduct->net_amount = $product['net_amount'];
            $purchaseProduct->comments = $product['comments'];
            $purchaseProduct->updated_at = now();
            $purchaseProduct->save();
        } else {
            // Otherwise, create a new product entry
            $purchaseProduct = new PurchaseProductModel();
            $purchaseProduct->purchase_id = $existingRecord->id;
            $purchaseProduct->product_id = $product['product_id'];
            $purchaseProduct->price = $product['price'];
            $purchaseProduct->tax = $product['tax'];
            $purchaseProduct->quantity = $product['quantity'];
            $purchaseProduct->amount = $product['amount'];
            $purchaseProduct->tax_amount = $product['tax_amount'];
            $purchaseProduct->net_amount = $product['net_amount'];
            $purchaseProduct->comments = $product['comments'];
            $purchaseProduct->created_at = now();
            $purchaseProduct->updated_at = now();
            $purchaseProduct->save();
        }

        // Add the product's net amount to the total amount
        $totalAmount += $product['net_amount'];
        $noOfQty += $product['quantity'];
    }

    // Update the total amount in the purchase order
    $existingRecord->total_amount = $totalAmount;
    $existingRecord->no_of_qty = $noOfQty;
    $existingRecord->no_of_products = $noOfProduct;
    $existingRecord->save();


      if($qResponce) {
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

  function getData()
  {

    $data = DB::table("purchars_order")
    ->select('po_no', 'city', 'date_of_po', 'date_of_delivery', 'id', 'supplier_id', 'warehouse_id', 'delivery_time','po_status','total_amount','no_of_products','no_of_qty')
    ->orderBy('created_at','DESC')
      ->get()
      ->map(function ($item) {
        $item->date_of_po = Carbon::parse($item->date_of_po)->format('d-m-Y');
        $item->date_of_delivery = Carbon::parse($item->date_of_delivery)->format('d-m-Y');
        return $item;
    });
    
          $response = [
              "response"=>200,
              'data'=>$data,
          ];
      
    return response($response, 200);
  }

  function getPendingData()
  {

    $data = DB::table("purchars_order")
    ->select('purchars_order.*')
    ->where('purchars_order.po_status', '!=', 'New')
    ->orderBy('created_at', 'DESC')
    ->get()
    ->map(function ($item) {
        if (!empty($item->date_of_po)) {
            $item->date_of_po = Carbon::parse($item->date_of_po)->format('d-m-Y');
        }

        if (!empty($item->date_of_delivery)) {
            $item->date_of_delivery = Carbon::parse($item->date_of_delivery)->format('d-m-Y');
        }

        return $item;
    });
    
          $response = [
              "response"=>200,
              'data'=>$data,
          ];
      
    return response($response, 200);
  }

  function getDataById($id)
  {
    try {
      $purchaseOrder = PurchasOrderModel::with('products')->find($id);
      $purchasInvoice = PurchasInvoiceModel::where('purchase_id','=', $id)
      ->first();

      if($purchasInvoice){
        $purchaseOrder->pi_no = $purchasInvoice->pi_no ?? null;
        $purchaseOrder->pi_id = $purchasInvoice->id ?? null;
      }

      if (!$purchaseOrder) {
          return response([
              'status' => false,
              'message' => 'Purchase order not found',
          ], 404);
      }

      $data = [
        'po_no' => $purchaseOrder->po_no,
        'pi_no' => $purchaseOrder->pi_no,
        'pi_id' => $purchaseOrder->pi_id,
        'supplier_id' => $purchaseOrder->supplier_id,
        'warehouse_id' => $purchaseOrder->warehouse_id,
        'city' => $purchaseOrder->city,
        'date_of_po' => $purchaseOrder->date_of_po,
        'date_of_delivery' => $purchaseOrder->date_of_delivery,
        'delivery_time' => $purchaseOrder->delivery_time,
        'po_status' => $purchaseOrder->po_status,
        'total_amount' => $purchaseOrder->total_amount,
        'products' => $purchaseOrder->products->map(function ($product) {
            return [
                'id' => $product->id,
                'product_id' => $product->product_id,
                'price' => $product->price,
                'tax' => $product->tax,
                'quantity' => $product->quantity,
                'amount' => $product->amount,
                'tax_amount' => $product->tax_amount,
                'net_amount' => $product->net_amount,
                'comments' => $product->comments,
            ];
        }),
    ];

    $response = [
        'response' => 200,
        'message' => 'Purchase order retrieved successfully',
        'data' => $data,
    ];


      return response($response, 200);
  } catch (\Exception $e) {
      return response([
          'status' => false,
          'message' => 'Error retrieving purchase order',
          'error' => $e->getMessage(),
      ], 500);
  }
  }

function sendApproval($id)
  {
      $purchaseOrder = PurchasOrderModel::where('id', '=', $id)->first();
      $timeStamp = date("Y-m-d H:i:s");
      $purchaseOrder->updated_at = $timeStamp;
      $purchaseOrder->po_status = "Pending";
      $qResponce= $purchaseOrder->save();
 
      if($qResponce) {
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
  function updatePOStatus(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'id' => 'required',
      'status' => 'required',
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else{
      $purchaseOrder = PurchasOrderModel::where('id', '=', $request->id)->first();
      $timeStamp = date("Y-m-d H:i:s");
      $purchaseOrder->updated_at = $timeStamp;
      $purchaseOrder->po_status = $request->status;
      $purchaseOrder->approved_by = $request->approved_by;
      $qResponce= $purchaseOrder->save();

      if($request->status == "Approved"){
        $dataModel = new PurchasInvoiceModel();
        $dataModel->pi_no  = $this->generatePINumber();
        $dataModel->supplier_id  = $purchaseOrder->supplier_id;
        $dataModel->warehouse_id  = $purchaseOrder->warehouse_id;
        $dataModel->purchase_id  = $purchaseOrder->id;
        $dataModel->po_no  = $purchaseOrder->po_no;
        $dataModel->date_of_po  = $purchaseOrder->date_of_po;
        $dataModel->no_of_products  = $purchaseOrder->no_of_products;
        $dataModel->no_of_qty  = $purchaseOrder->no_of_qty;
        $dataModel->created_at = $timeStamp;
        $dataModel->updated_at = $timeStamp;
        $dataModel->approval_status  = "New";
        $dataModel->invoice_amount = $purchaseOrder->total_amount;
        $dataModel->total_amount  = $purchaseOrder->total_amount;
        $qInvoiceResponce= $dataModel->save();
      }else if($request->status == "Rejected"){
        PurchasInvoiceModel::where('purchase_id', $purchaseOrder->id)->delete();
        $qInvoiceResponce = true;
      }
      
      if($qResponce && $qInvoiceResponce) {
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

  function generatePONumber()
  {
    $date = date('Ymd');
    $orderCountToday = PurchasOrderModel::whereDate('created_at', '=', date('Y-m-d'))->count();
    $nextPONumber = $orderCountToday + 1;
    $PONumber = "PO" . $date . str_pad($nextPONumber, 2, '0', STR_PAD_LEFT);
    return "#" . $PONumber;
  }

  function generatePINumber()
  {
    $date = date('Ymd');
    $orderCountToday = PurchasInvoiceModel::whereDate('created_at', '=', date('Y-m-d'))->count();
    $nextPINumber = $orderCountToday + 1;
    $PINumber = "PI" . $date . str_pad($nextPINumber, 2, '0', STR_PAD_LEFT);
    return "#" . $PINumber;
  }
}
