<?php

namespace App\Http\Controllers;

use App\Models\PurchaseReturnModel;
use App\Models\PurchasInvoiceModel;
use App\Models\PurchasOrderModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\ImageModel;
use App\Models\ProductModel;
use App\Http\Controllers\WebAppSettingsController;
use App\Models\User;

class PurchaseInvoiceController extends Controller
{
  function getData()
  {

    $data = DB::table("purchase_invoice")
    ->select(
      'id',
      'pi_no',
      'po_no',
      'supplier_id',
      'warehouse_id',
      'purchase_id',
      'date_of_po',
      'approval_status',
      'invoice_amount',
      'no_of_products',
      'no_of_qty'
  )
  ->orderBy('created_at', 'DESC')
  ->get()
  ->map(function ($item) {
      $item->date_of_po = Carbon::parse($item->date_of_po)->format('d-m-Y');
      return $item;
  });
    
          $response = [
              "response"=>200,
              'data'=>$data,
          ];
      
    return response($response, 200);
  }
  function updatePIStatus(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'id' => 'required',
      'status' => 'required',
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else{
      $purchaseInvoice = PurchasInvoiceModel::where('id', '=', $request->id)->first();
      $timeStamp = date("Y-m-d H:i:s");
      $purchaseInvoice->updated_at = $timeStamp;
      $purchaseInvoice->approval_status = $request->status;
      $purchaseInvoice->approved_by = $request->approved_by;
      if($request->status == 'Approved'){
        $purchaseInvoice->payment_status = "New";
      }
      $qResponce= $purchaseInvoice->save();
      
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
  }

  function sendApproval($id)
  {
      $purchaseInvoice = PurchasInvoiceModel::where('id', '=', $id)->first();
      $timeStamp = date("Y-m-d H:i:s");
      $purchaseInvoice->updated_at = $timeStamp;
      $purchaseInvoice->approval_status = "Pending";
      $qResponce= $purchaseInvoice->save();
 
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

  function getPendingData()
  {

    $data = DB::table("purchase_invoice")
    ->select('purchase_invoice.*')
    ->where('purchase_invoice.approval_status', '!=', 'New')
    ->orderBy('created_at', 'DESC')
    ->get()
    ->map(function ($item) {
        if (!empty($item->date_of_po)) {
            $item->date_of_po = Carbon::parse($item->date_of_po)->format('d-m-Y');
        }

        return $item;
    });
    
          $response = [
              "response"=>200,
              'data'=>$data,
          ];
      
    return response($response, 200);
  }

  function getPurchasePayment()
  {

    $data = DB::table("purchase_invoice")
        ->select('purchase_invoice.*')
        ->where('purchase_invoice.approval_status', '=', 'Approved')
        ->orderBy('created_at', 'DESC')
        ->get()
        ->map(function ($item) {
            $item->date_of_po = Carbon::parse($item->date_of_po)->format('d-m-Y');
            return $item;
        });
    
          $response = [
              "response"=>200,
              'data'=>$data,
          ];
      
    return response($response, 200);
  }
  
  function sendpyamentApprovalRejectPR($id)
  {
    $timeStamp = date("Y-m-d H:i:s");
    $purchaseReturn = PurchaseReturnModel::where('pi_id', '=', $id)->first();
    $purchaseReturn->updated_at = $timeStamp;
      $purchaseReturn->pr_status = "Rejected";
      $qPRResponce= $purchaseReturn->save();
      $purchaseInvoice = PurchasInvoiceModel::where('id', '=', $id)->first();
      $purchaseInvoice->updated_at = $timeStamp;
      $purchaseInvoice->payment_status = "Pending";
      $qPIResponce= $purchaseInvoice->save();
      if($qPRResponce && $qPIResponce) {
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

  function sendPaymentApproval($id)
  {
    $purchaseReturn = PurchaseReturnModel::where('pi_id', '=', $id)
    ->where('pr_status', '!=', 'Rejected')
    ->where('pr_status', '!=', 'Approved')
    ->first();
    if($purchaseReturn){
      $response = [
        "response" => 202,
        'status' => false,
        'message' => "PR is already avilable for this PP",
      ];
      return response($response, 200);
    }
      $purchaseInvoice = PurchasInvoiceModel::where('id', '=', $id)->first();
      $timeStamp = date("Y-m-d H:i:s");
      $purchaseInvoice->updated_at = $timeStamp;
      $purchaseInvoice->payment_status = "Pending";
      $qResponce= $purchaseInvoice->save();
 
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

  function getPaymentPendingData()
  {

    $data = DB::table("purchase_invoice")
        ->select('purchase_invoice.*')
        ->where('purchase_invoice.payment_status', '!=', 'New')
        ->orderBy('created_at', 'DESC')
        ->get()
        ->map(function ($item) {
            if (!empty($item->date_of_po)) {
                $item->date_of_po = Carbon::parse($item->date_of_po)->format('d-m-Y');
            }

            return $item;
        });
    
          $response = [
              "response"=>200,
              'data'=>$data,
          ];
      
    return response($response, 200);
  }

  function updatePPStatus(Request $request)
  {
    $validator = Validator::make(request()->all(), [
      'id' => 'required',
      'status' => 'required',
    ]);

    if ($validator->fails())
      return response(["response" => 400], 400);
    else{
      $purchaseInvoice = PurchasInvoiceModel::where('id', '=', $request->id)->first();
      $timeStamp = date("Y-m-d H:i:s");
      $purchaseInvoice->updated_at = $timeStamp;
      $purchaseInvoice->payment_status = $request->status;
      $qResponce= $purchaseInvoice->save();

      if($request->status == 'Paid'){
        $purchaseOrder = PurchasOrderModel::where('id', '=', $purchaseInvoice->purchase_id)->first();
        $purchaseOrder->pi_status = $request->status;
        $purchaseOrder->updated_at = $timeStamp;
        $qPurchaseOrderResponce= $purchaseOrder->save();
      }
      else{
        $qPurchaseOrderResponce=true;
      }
      
      if($qResponce && $qPurchaseOrderResponce) {
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

}
