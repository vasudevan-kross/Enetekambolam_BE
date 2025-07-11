<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRetrunProductModel;
use App\Models\PurchasInvoiceModel;
use App\Models\PurchaseReturnModel;
use App\Models\PurchasOrderModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PurchaseReturnController extends Controller
{
  function getApprovedPIData()
  {
    $data = DB::table('purchase_invoice')
    ->select('purchase_invoice.*')
    ->leftJoin('purchase_return', 'purchase_invoice.id', '=', 'purchase_return.pi_id')
    ->where('purchase_invoice.approval_status', 'Approved')
    ->where('purchase_invoice.payment_status', 'New')
    ->where(function ($query) {
      $query->whereNull('purchase_return.pi_id')
            ->orWhere('purchase_return.pr_status', 'Rejected');
      })
    ->orderBy('purchase_invoice.created_at', 'DESC')
    ->distinct()
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
      'pi_id' => 'required',
      'pi_no' => 'required',
      'supplier_id' => 'required',
      'warehouse_id' => 'required',
      'date_of_pr' => 'required',
      'city' => 'required',
      'productData' => 'required',
      'status' => 'required',
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      $timeStamp = date("Y-m-d H:i:s");
      $dataModel = new PurchaseReturnModel();
      $dataModel->pr_no  = $this->generatePRNumber();
      $dataModel->pi_id  = $request->pi_id;
      $dataModel->pi_no  = $request->pi_no;
      $dataModel->supplier_id  = $request->supplier_id;
      $dataModel->warehouse_id  = $request->warehouse_id;
      $dataModel->city  = $request->city;
      $dataModel->date_of_pr  = $request->date_of_pr;
      $dataModel->pr_status  = $request->status;
      $dataModel->created_at = $timeStamp;
      $dataModel->updated_at = $timeStamp;
      $qResponce = $dataModel->save();

      $totalAmount = 0;
      $noOfQty = 0;
      $noOfProduct = count(array_filter($request->productData, function ($product) {
        return $product['returnQuantity'] != 0;
    }));
      try {
        foreach ($request->productData as $product) {
          if($product['returnQuantity'] == 0){
            continue;
          }
          $purchaseProduct = new PurchaseRetrunProductModel();
          $purchaseProduct->pr_id = $dataModel->id;
          $purchaseProduct->product_id = $product['product_id'];
          $purchaseProduct->price = $product['price'];
          $purchaseProduct->tax = $product['tax'];
          $purchaseProduct->quantity = $product['returnQuantity'];
          $purchaseProduct->amount = $product['amount'];
          $purchaseProduct->tax_amount = $product['tax_amount'];
          $purchaseProduct->net_amount = $product['net_amount'];
          $purchaseProduct->comments = $product['comments'];
          $purchaseProduct->pi_quantity = $product['quantity'];
          $purchaseProduct->created_at = $timeStamp;
          $purchaseProduct->updated_at = $timeStamp;
          $purchaseProduct->save();

          $totalAmount += $product['net_amount'];
          $noOfQty += $product['returnQuantity'];
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
      'pi_id' => 'required',
      'pi_no' => 'required',
      'supplier_id' => 'required',
      'warehouse_id' => 'required',
      'date_of_pr' => 'required',
      'city' => 'required',
      'productData' => 'required',
      'status' => 'required',
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else {
      $existingRecord = PurchaseReturnModel::where('id', '=', $request->id)->first();
      $timeStamp = date("Y-m-d H:i:s");
      $existingRecord->pi_id  = $request->pi_id;
      $existingRecord->pi_no  = $request->pi_no;
      $existingRecord->supplier_id = $request->supplier_id;
      $existingRecord->warehouse_id  = $request->warehouse_id;
      $existingRecord->city  = $request->city;
      $existingRecord->date_of_pr  = $request->date_of_pr;
      $existingRecord->pr_status  = $request->status;
      $existingRecord->updated_at = $timeStamp;
      $qResponce = $existingRecord->save();

      $noOfQty = 0;
      $totalAmount = 0;
      $noOfProduct = count(array_filter($request->productData, function ($product) {
        return $product['returnQuantity'] != 0;
    }));
      
      foreach ($request->productData as $product) {
        if($product['returnQuantity'] == 0){
          continue;
        }
        $purchaseProduct = PurchaseRetrunProductModel::where('id','=', $product['id'])
            ->where('product_id', $product['product_id'])
            ->first();

        if ($purchaseProduct) {
            $purchaseProduct->price = $product['price'];
            $purchaseProduct->tax = $product['tax'];
            $purchaseProduct->quantity = $product['returnQuantity'];
            $purchaseProduct->amount = $product['amount'];
            $purchaseProduct->tax_amount = $product['tax_amount'];
            $purchaseProduct->net_amount = $product['net_amount'];
            $purchaseProduct->comments = $product['comments'];
            $purchaseProduct->pi_quantity = $product['quantity'];
            $purchaseProduct->updated_at = now();
            $purchaseProduct->save();
        } 
        $totalAmount += $product['net_amount'];
        $noOfQty += $product['returnQuantity'];
    }

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
    $data = DB::table("purchase_return")
        ->select(
            'purchase_return.*',
            'purchase_invoice.invoice_amount as pi_amount'
        )
        ->join('purchase_invoice', 'purchase_return.pi_id', '=', 'purchase_invoice.id')
        ->orderBy('purchase_return.created_at', 'DESC')
        ->get()
        ->map(function ($item) {
            if (!empty($item->date_of_pr)) {
                $item->date_of_pr = Carbon::parse($item->date_of_pr)->format('d-m-Y');
            }
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
    $data = DB::table("purchase_return")
    ->select(
        'purchase_return.*',
        'purchase_invoice.invoice_amount as pi_amount'
    )
    ->join('purchase_invoice', 'purchase_return.pi_id', '=', 'purchase_invoice.id')
    ->where('purchase_return.pr_status', '!=', 'New')
    ->orderBy('created_at','DESC')
    ->get()
    ->map(function ($item) {
        if (!empty($item->date_of_pr)) {
            $item->date_of_pr = Carbon::parse($item->date_of_pr)->format('d-m-Y');
        }

        return $item;
    });
    
          $response = [
              "response"=>200,
              'data'=>$data,
          ];
      
    return response($response, 200);
  }

  function getDataById($id, Request $request)
  {
    try {
      if ($request->has('isPR') && $request->isPR == "true") {
        $prData = PurchaseReturnModel::where('pi_id', '=', $id)->first();
        $purchaseReturn = PurchaseReturnModel::with('products')->find($prData->id);
      } else {
        $purchaseReturn = PurchaseReturnModel::with('products')->find($id);
      }
      $purchaseInvoice = PurchasInvoiceModel::where('id', '=', $purchaseReturn->pi_id)->first();
      $purchaseOrder = PurchasOrderModel::with('products')->find($purchaseInvoice->purchase_id);
      if (!$purchaseReturn) {
          return response([
              'status' => false,
              'message' => 'Purchase Return not found',
          ], 404);
      }

      $data = [
        'pr_no' => $purchaseReturn->pr_no,
        'pi_id' => $purchaseReturn->pi_id,
        'pi_no' => $purchaseReturn->pi_no,
        'supplier_id' => $purchaseReturn->supplier_id,
        'warehouse_id' => $purchaseReturn->warehouse_id,
        'city' => $purchaseReturn->city,
        'date_of_pr' => $purchaseReturn->date_of_pr,
        'pr_status' => $purchaseReturn->pr_status,
        'total_amount' => $purchaseReturn->total_amount,
        'pi_total_amount' => $purchaseInvoice->total_amount,
        'pi_invoice_amount' =>  $purchaseInvoice->invoice_amount,
        'pi_return_amount' => $purchaseInvoice->return_amount,
        'products' => $purchaseReturn->products->map(function ($product) {
            return [
                'id' => $product->id,
                'pr_id' => $product->pr_id,
                'product_id' => $product->product_id,
                'price' => $product->price,
                'tax' => $product->tax,
                'quantity' => $product->pi_quantity,
                'amount' => $product->amount,
                'tax_amount' => $product->tax_amount,
                'net_amount' => $product->net_amount,
                'comments' => $product->comments,
                'returnQuantity' => $product->quantity,
            ];
        }),
        'PIProducts' => $purchaseOrder->products->map(function ($product) {
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
      $purchaseReturn = PurchaseReturnModel::where('id', '=', $id)->first();
      $timeStamp = date("Y-m-d H:i:s");
      $purchaseReturn->updated_at = $timeStamp;
      $purchaseReturn->pr_status = "Pending";
      $qResponce= $purchaseReturn->save();
 
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
  function updatePRStatus(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'id' => 'required',
      'status' => 'required',
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else{
      $purchaseReturn = PurchaseReturnModel::where('id', '=', $request->id)->first();
      $timeStamp = date("Y-m-d H:i:s");
      $purchaseReturn->updated_at = $timeStamp;
      $purchaseReturn->pr_status = $request->status;
      $purchaseReturn->approved_by = $request->approved_by;
      $qResponce= $purchaseReturn->save();

      if($request->status == "Approved"){
        $dataModel = PurchasInvoiceModel::where('id', '=', $purchaseReturn->pi_id)->first();
        $dataModel->updated_at = $timeStamp;
        $dataModel->payment_status  = "New";
        $dataModel->return_amount = $purchaseReturn->total_amount;
        $dataModel->total_amount  = $dataModel->invoice_amount - $purchaseReturn->total_amount;
        $dataModel->pr_id = $purchaseReturn->id;
        $dataModel->pr_no = $purchaseReturn->pr_no;

        // $dataModel->no_of_products  = $purchaseReturn->no_of_products - $dataModel->no_of_products;
        // $dataModel->no_of_qty  = $dataModel->no_of_qty - $purchaseReturn->no_of_qty;
        $qInvoiceResponce= $dataModel->save();
      }else{
        $qInvoiceResponce=true;
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

  function generatePRNumber()
  {
    $date = date('Ymd');
    $orderCountToday = PurchaseReturnModel::whereDate('created_at', '=', date('Y-m-d'))->count();
    $nextPRNumber = $orderCountToday + 1;
    $PRNumber = "PR" . $date . str_pad($nextPRNumber, 2, '0', STR_PAD_LEFT);
    return "#" . $PRNumber;
  }
}
