<?php

use App\Http\Controllers\CouponController;
use App\Http\Controllers\DeliveryExecutiveOrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PurchaseInvoiceController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\RazorpayWebhookController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\TransactionsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\BannerImageController;
use App\Http\Controllers\UserHolyDayController;
use App\Http\Controllers\OrderAssignController;
use App\Http\Controllers\SubOrderDeliveyController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\WebPageController;
use App\Http\Controllers\AppSettingController;
use App\Http\Controllers\AllowPincodeController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\SendNotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\PaymentGetwayController;
use App\Http\Controllers\WebAppSettingsController;
use App\Http\Controllers\AvailableDeliveryLocationController;
use App\Http\Controllers\SocialMediaController;
use App\Http\Controllers\UploadImageController;
use App\Http\Controllers\SpecificNotificationController;
use App\Http\Controllers\TimeCheckController;
use App\Http\Controllers\InvoiceSettingController;
use App\Http\Controllers\OTPController;
use App\Http\Controllers\DeliveryExecutiveController;
use App\Http\Controllers\DeliveryExecutiveRouteController;
use App\Http\Controllers\DeliveryRoutesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::options('{any}', function () {
    return response()->json(['status' => 'OK'], 200);
})->where('any', '.*');



Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get("get_check_time", [TimeCheckController::class, 'checkTimeAvailability']);


    Route::get("get_user", [UsersController::class, 'getData']);
    Route::get("get_user/{id}", [UsersController::class, 'getDataById']);
    Route::get("get_user/role/{id}", [UsersController::class, 'getDataByRole']);
    Route::post("get_user/role/{id}", [UsersController::class, 'getDataByRole']);
    Route::post("update_user", [UsersController::class, 'updateDetails']);
    Route::post("delete_assign_user", [UsersController::class, 'deleteAssignData']);
    Route::post("add_assign_user", [UsersController::class, 'addAssignData']);
    Route::post("import_users", [UsersController::class, 'importUsers']);
    Route::get("get_customers", [UsersController::class, 'getCustomersData']);

    Route::post("add_cat", [CategoryController::class, 'addData']);
    Route::post("update_cat", [CategoryController::class, 'updateDetails']);
    Route::get("gegt_cat", [CategoryController::class, 'getData']);

    Route::get("get_cat/{id}", [CategoryController::class, 'getDataById']);
    Route::post("delete_cat", [CategoryController::class, 'deleteData']);
    Route::post("cat/upload_image", [CategoryController::class, 'uploadImage']);
    Route::post("cat/delete_image", [CategoryController::class, 'deleteImage']);

    Route::post("add_sub_cat", [SubCategoryController::class, 'addData']);
    Route::post("update_sub_cat", [SubCategoryController::class, 'updateDetails']);
    Route::get("get_sub_cat", [SubCategoryController::class, 'getData']);
    Route::post("sub_cat/upload_image", [SubCategoryController::class, 'uploadImage']);
    Route::post("sub_cat/delete_image", [SubCategoryController::class, 'deleteImage']);
    Route::get("get_sub_cat/{catId}", [SubCategoryController::class, 'getDataById']);

    Route::post("delete_sub_cat", [SubCategoryController::class, 'deleteData']);

    Route::post("add_product", [ProductController::class, 'addData']);
    Route::post("update_product", [ProductController::class, 'updateDetails']);
    Route::get("get_product", [ProductController::class, 'getData']);
    Route::post("delete_product", [ProductController::class, 'deleteData']);
    Route::get("get_product/sub_cat/{subCatId}", [ProductController::class, 'getDataBySubCatId']);
    Route::post("product/upload_image", [ProductController::class, 'uploadImage']);
    Route::post("product/delete_image", [ProductController::class, 'deleteImage']);
    Route::get('get_approval_products', [ProductController::class, 'getApprovalProductsData']);
    Route::post('product/import_products', [ProductController::class, 'importCSV']);
    Route::get("get_all_product", [ProductController::class, 'getAllData']);
    Route::post("add_stockApproval", [ProductController::class, 'addProductStock']);
    Route::get("get_stockApprovals", [ProductController::class, 'getStockApprovalData']);
    Route::post("change_stockApproval_status", [ProductController::class, 'changeStockApprovalStaus']);
    Route::get("get_pi_and_po_number", [ProductController::class, 'getPiAndPoNumber']);
    Route::get('/get_product_list', [ProductController::class, 'getDataList']);


    Route::post("add_address", [AddressController::class, 'addData']);
    Route::get("address/user/{id}", [AddressController::class, 'getDataByUserId']);
    Route::get("address/{id}", [AddressController::class, 'getDataById']);
    Route::post('address/user/delete', [AddressController::class, 'markAsdeleteAddress']);


    Route::post("add_txn", [TransactionsController::class, 'addData']);
    Route::get("txn/user/{id}", [TransactionsController::class, 'getDataByUId']);
    Route::get("txn/order/{id}", [TransactionsController::class, 'getDataByOrderId']);
    Route::get("txn/sub_order/{id}", [TransactionsController::class, 'getDataBySubOrderId']);

    Route::get("txn/{id}", [TransactionsController::class, 'getDataById']);
    Route::get("txn", [TransactionsController::class, 'getData']);
    Route::post("add_order_txn", [TransactionsController::class, 'addDirectData']);
    Route::get("txn/by_date_range/{startDate}/{endDate}", [TransactionsController::class, 'getDataByDateRange']);


    Route::post("add_vendor", [VendorController::class, 'addData']);
    Route::post("vendor/upload_images", [VendorController::class, 'uploadImages']);
    Route::get("get_vendor", [VendorController::class, 'getData']);
    Route::get("get_vendor_by_id/{id}", [VendorController::class, 'getDataById']);
    Route::post("update_vendor", [VendorController::class, 'updateData']);
    Route::get('get_vendors', [VendorController::class, 'getActiveVendorList']);
    Route::get('change_vendor_status/{id}', [VendorController::class, 'changeVendorStaus']);

    Route::post("add_warehouse", [WarehouseController::class, 'addData']);
    Route::get("get_all_warehouse", [WarehouseController::class, 'getData']);
    Route::get("get_warehouse_by_id/{id}", [WarehouseController::class, 'getDataById']);
    Route::post("update_warehouse", [WarehouseController::class, 'updateData']);
    Route::get('change_warehouse_status/{id}', [WarehouseController::class, 'changeWarehouseStaus']);
    Route::get('get_warehouse', [WarehouseController::class, 'getActiveWarehouseList']);

    Route::post("add_purchaseOrder", [PurchaseOrderController::class, 'addData']);
    Route::get("get_purchaseOrder", [PurchaseOrderController::class, 'getData']);
    Route::get("get_purchaseOrder_by_id/{id}", [PurchaseOrderController::class, 'getDataById']);
    Route::post("update_purchaseOrder", [PurchaseOrderController::class, 'updateData']);
    Route::post("update_purchaseOrder_status", [PurchaseOrderController::class, 'updatePOStatus']);
    Route::get("send_po_approval/{id}", [PurchaseOrderController::class, 'sendApproval']);
    Route::get("get_pending_purchaseOrder", [PurchaseOrderController::class, 'getPendingData']);

    Route::get("get_purchaseInvoice", [PurchaseInvoiceController::class, 'getData']);
    Route::post("update_purchaseInvoice_status", [PurchaseInvoiceController::class, 'updatePIStatus']);
    Route::get("send_pi_approval/{id}", [PurchaseInvoiceController::class, 'sendApproval']);
    Route::get("get_pending_purchaseInvoice", [PurchaseInvoiceController::class, 'getPendingData']);
    Route::get("get_purchasePayment", [PurchaseInvoiceController::class, 'getPurchasePayment']);
    Route::get("send_pp_approval/{id}", [PurchaseInvoiceController::class, 'sendPaymentApproval']);
    Route::get("send_pp_approval_reject_pr/{id}", [PurchaseInvoiceController::class, 'sendpyamentApprovalRejectPR']);

    Route::get("get_pending_purchasePayment", [PurchaseInvoiceController::class, 'getPaymentPendingData']);
    Route::post("update_purchasePayment_status", [PurchaseInvoiceController::class, 'updatePPStatus']);

    Route::get("get_approved_purchaseInvoice", [PurchaseReturnController::class, 'getApprovedPIData']);
    Route::post("add_purchaseReturn", [PurchaseReturnController::class, 'addData']);
    Route::post("update_purchaseReturn", [PurchaseReturnController::class, 'updateData']);
    Route::get("get_purchaseReturn", [PurchaseReturnController::class, 'getData']);
    Route::get("get_purchaseReturn_by_id/{id}", [PurchaseReturnController::class, 'getDataById']);
    Route::get("send_pr_approval/{id}", [PurchaseReturnController::class, 'sendApproval']);
    Route::get("get_pending_purchaseReturn", [PurchaseReturnController::class, 'getPendingData']);
    Route::post("update_purchaseReturn_status", [PurchaseReturnController::class, 'updatePRStatus']);


    Route::post("add_order", [OrderController::class, 'addData']);
    Route::post("add_order_data", [OrderController::class, 'addOrderData']);
    Route::post("add_order_cart", [OrderController::class, 'addCardAndOrderData']);
    Route::post('/check-order-status', [OrderController::class, 'checkOrderCreated']);


    Route::get("get_order_product_list", [OrderController::class, 'getOrderProductsByDateRange']);
    Route::get("get_order/user/{id}", [OrderController::class, 'getDataByUId']);
    Route::get("get_stop_order/user/{id}", [OrderController::class, 'getStopOrderDataByUId']);
    Route::get("get_order/{id}", [OrderController::class, 'getDataById']);
    Route::get("get_cart_order/{id}", [OrderController::class, 'getCartDataById']);
    Route::get("get_cart_product/{id}", [OrderController::class, 'getCartProductDataById']);
    Route::get("get_order", [OrderController::class, 'getData']);
    Route::get("get_buyonce_order", [OrderController::class, 'getBuyOnceData']);
    Route::get("get_subscription_order", [OrderController::class, 'getSubscriptionOrdereData']);
    Route::post("update_order", [OrderController::class, 'updateDetails']);
    Route::post("update_order/txn", [OrderController::class, 'updateOrderTxnAndAddNewTxn']);

    Route::post("add_cart", [CartController::class, 'addData']);
    Route::post("cart/update_qty", [CartController::class, 'updateQty']);
    Route::post("cart/delete", [CartController::class, 'deleteData']);
    Route::get("get_cart/user/{id}", [CartController::class, 'getDataByUId']);
    Route::get("get_cart_orders", [CartController::class, 'getAllCartDataGroupedByUser']);


    Route::post("add_executive_details", [DeliveryExecutiveController::class, 'addExecutiveDetails']);
    Route::post("executive/upload_images", [DeliveryExecutiveController::class, 'uploadImages']);
    Route::get('get_executive_details', [DeliveryExecutiveController::class, 'getExecutive']);
    Route::get('get_executive_by_id/{id}', [DeliveryExecutiveController::class, 'getExecutiveById']);
    Route::post("update_executive", [DeliveryExecutiveController::class, 'updateExecutive']);
    Route::get('change_executive_status/{id}', [DeliveryExecutiveController::class, 'changeExecutiveStaus']);
    Route::POST('store_generated_pswd/{id}', [DeliveryExecutiveController::class, 'storePassword']);
    Route::get('get_password/{id}', [DeliveryExecutiveController::class, 'getPassword']);
    Route::get('get_all_executives', [DeliveryExecutiveController::class, 'getAllDeliveryExecutives']);
    Route::get('get_orders_by_date/{date}/{exe_id}', [DeliveryExecutiveController::class, 'getOrdersByDate']);
    Route::get('get_stock_orders_by_date/{date}/{exe_id}', [DeliveryExecutiveController::class, 'getStockOrdersByDate']);
    Route::POST('update_stocks/{id}/{exe_id}/{date}/{executive_assign_id}', [DeliveryExecutiveController::class, 'UpdateStocks']);
    Route::get('get_delivery_orders_by_date/{date}/{exe_id}', [DeliveryExecutiveController::class, 'getDeliveryOrdersByDate']);
    Route::post("store_delivered_info/{exe_id}", [DeliveryExecutiveController::class, 'storeDeliveredInfo']);
    Route::get('get_executive_emails', [DeliveryExecutiveController::class, 'getExecutiveEmails']);
    Route::post("re_assign_executive", [DeliveryExecutiveController::class, 'reAssignExecutive']);
    Route::get('get_executives_by_pincode/{pincode}/{exe_id}', [DeliveryExecutiveController::class, 'getExecutivesByPincode']);



    Route::get('get_delivery_routes', [DeliveryRoutesController::class, 'getAllData']);
    Route::post('add_delivery_route', [DeliveryRoutesController::class, 'addData']);
    Route::get('get_delivery_route/{id}', [DeliveryRoutesController::class, 'getData']);
    Route::post('update_delivery_route', [DeliveryRoutesController::class, 'update']);
    Route::post('delete_delivery_route', [DeliveryRoutesController::class, 'removeData']);

    Route::get('get_delivery_executive_routes', [DeliveryExecutiveRouteController::class, 'getAllData']);
    Route::get('get_delivery_executive_routes_details/{executiveId}', [DeliveryExecutiveRouteController::class, 'getDeliveryExecutivesWithRouteswithId']);
    Route::post('add_delivery_executive_route', [DeliveryExecutiveRouteController::class, 'updateOrAddRoutes']);
    Route::get('get_delivery_executive_route/{id}', [DeliveryExecutiveRouteController::class, 'getData']);
    Route::post('update_delivery_executive_route', [DeliveryExecutiveRouteController::class, 'updateData']);
    Route::post('delete_delivery_executive_route', [DeliveryExecutiveRouteController::class, 'removeData']);

    Route::get('get_delivery_order_detail/{selectedDate}', [DeliveryExecutiveOrderController::class, 'getDeliveryOrdersByDate']);
    Route::get('get_delivery_executive_order_details/{executiveId}/{selectedDate}', [DeliveryExecutiveOrderController::class, 'getDeliveryOrdersExecutivesId']);
    Route::post('add_delivery_executive_orders', [DeliveryExecutiveOrderController::class, 'addData']);
    Route::get('get_delivery_executive_orders', [DeliveryExecutiveOrderController::class, 'getAllData']);
    Route::get('get_delivery_executive_orders/{startDate}/{endDate}', [DeliveryExecutiveOrderController::class, 'getAllDataWithDateRange']);
    Route::post('delete_delivery_executive_order', [DeliveryExecutiveOrderController::class, 'removeData']);
    Route::get('get_all_re_assigne_request_data', [DeliveryExecutiveOrderController::class, 'getAllReAssigneRequestData']);
    Route::get('get_delivery_executive_by_order/{id}', [DeliveryExecutiveOrderController::class, 'getDeliveryExectiveByOrder']);
    Route::post('delivery_re_assign_Order', [DeliveryExecutiveOrderController::class, 'reAssignOrder']);


    Route::post("upload_banner_image", [BannerImageController::class, 'uploadImage']);
    Route::post("delete_banner_image", [BannerImageController::class, 'deleteData']);

    Route::post("add_user_holiday", [UserHolyDayController::class, 'addData']);
    Route::get("get_user_holiday/user/{id}", [UserHolyDayController::class, 'getDataByUserId']);
    Route::post("delete_user_holiday", [UserHolyDayController::class, 'deleteData']);

    Route::post("add_order_assign", [OrderAssignController::class, 'addData']);
    Route::post("order_assign/delete", [OrderAssignController::class, 'deleteData']);
    Route::get("get_order/emp_user/{id}", [OrderAssignController::class, 'getDataByUId']);
    Route::get("get_normal_order/emp_user/{id}", [OrderAssignController::class, 'getNormalDataByUId']);
    Route::get("get_delivered_order/emp_user/{id}", [OrderAssignController::class, 'getDeliveryDataByUId']);
    Route::get("get_assign_user_order/order/{id}", [OrderAssignController::class, 'getOrderAssignUserByOrderId']);
    Route::get("get_assign_user_order/date/order/{id}", [OrderAssignController::class, 'getDataByUIdAndDateDeliveredSub']);
    Route::get("get_assign_user_order/sub/order/{id}", [OrderAssignController::class, 'getDataByUIdAndDeliveredSub']);
    Route::get("get_upcoming_delivery/normal", [OrderAssignController::class, 'getAllNoramlDelivery']);
    Route::get("get_upcoming_delivery/sub", [OrderAssignController::class, 'getAllSubAllDelivery']);
    Route::get("get_upcoming_delivery/sub/assign_user/{id}", [OrderAssignController::class, 'getAllSubAllDeliveryByAssignUser']);
    Route::get("get_upcoming_delivery/sub_date/{date}", [OrderAssignController::class, 'getAllSubAllDeliveryByDate']);
    Route::get("get_upcoming_delivery/sub_date/assign_user/{id}/{date}", [OrderAssignController::class, 'getAllSubAllDeliveryByAssignUserByDate']);



    Route::post("add_sub_order_delivery", [SubOrderDeliveyController::class, 'addData']);
    Route::post("add_sub_order_delivery/add_manually", [SubOrderDeliveyController::class, 'addDataManually']);
    Route::post("add_order_delivery_data", [SubOrderDeliveyController::class, 'addOrderDeliveryData']);

    Route::post("add_sub_order_delivery_weekly", [SubOrderDeliveyController::class, 'addDataWeekely']);
    Route::post("add_sub_order_delivery_weekly/add_manually", [SubOrderDeliveyController::class, 'addDataWeekelyManually']);
    Route::post("add_normal_order_delivery", [SubOrderDeliveyController::class, 'addNormalOrderData']);
    Route::get("get_sub_order_delivery/order/{id}", [SubOrderDeliveyController::class, 'getDataByOrderId']);


    Route::post("update_web_page", [WebPageController::class, 'updateData']);

    Route::get("get_settings", [AppSettingController::class, 'getDataAllPages']);
    Route::get("get_settings/{id}", [AppSettingController::class, 'getDataBySettingId']);
    Route::post("update_settings", [AppSettingController::class, 'updateData']);


    Route::post("add_pincode", [AllowPincodeController::class, 'addData']);
    Route::get("get_pincode", [AllowPincodeController::class, 'getDataAllPincode']);
    Route::post("delete_pincode", [AllowPincodeController::class, 'delete']);

    Route::post("add_testimonial", [TestimonialController::class, 'addData']);
    Route::post("update_testimonial", [TestimonialController::class, 'updateDetails']);
    Route::post("delete_testimonial", [TestimonialController::class, 'delete']);

    Route::post("testimonial/upload_image", [TestimonialController::class, 'uploadImage']);
    Route::post("testimonial/delete_image", [TestimonialController::class, 'deleteImage']);

    Route::get("get_user_notification", [UserNotificationController::class, 'getDataAllNoti']);
    Route::get("get_user_notification/date/{date}", [UserNotificationController::class, 'getDataByDate']);
    Route::post("add_user_notification", [UserNotificationController::class, 'addData']);
    Route::get("get_user_notification/{id}", [UserNotificationController::class, 'getDataById']);
    Route::post("send_low_wallet_notificaiton", [UserNotificationController::class, 'sendLowWalletNotification']);


    Route::post("send_notification_to_topic", [sendNotificationController::class, 'sendReqFirebaseNotificationToTopic']);

    Route::get("get_report/delivery", [ReportController::class, 'getDataAllDeliveryReport']);
    Route::get("get_report/delivery/{userId}", [ReportController::class, 'getDataAllDeliveryByUser']);

    Route::get("get_report/delivery/{firstDate}/{lastDate}", [ReportController::class, 'getDataAllDeliveryReportByDate']);
    Route::get("get_report/delivery/{userId}/{firstDate}/{lastDate}", [ReportController::class, 'getDataAllDeliveryReportByDateAndUser']);

    Route::post("update_payment_getway", [PaymentGetwayController::class, 'updateData']);
    Route::get("get_payment_getway", [PaymentGetwayController::class, 'getDataAllData']);
    Route::get("get_payment_getway/active", [PaymentGetwayController::class, 'getDataAcive']);

    Route::post('add_available_delivery_location', [AvailableDeliveryLocationController::class, 'addData']);
    Route::post('delete_available_delivery_location', [AvailableDeliveryLocationController::class, 'delete']);

    Route::post('add_social_media', [SocialMediaController::class, 'addData']);
    Route::post('delete_social_media', [SocialMediaController::class, 'delete']);

    //Reports
    Route::get("get_user_report", [ReportController::class, 'getCustomerReportData']);
    Route::get("get_user_report/{firstDate}/{lastDate}", [ReportController::class, 'getCustomerReportDataByDate']);
    Route::get("get_subscriptions_report", [ReportController::class, 'getSubscriptionReportData']);
    Route::get("get_subscriptions_report/{firstDate}/{lastDate}", [ReportController::class, 'getSubscriptionReportDataByDate']);
    Route::get("get_subscriber_report", [ReportController::class, 'getSubscriberReportData']);
    Route::get("get_subscriber_report/{firstDate}/{lastDate}", [ReportController::class, 'getSubscriberReportDataByDate']);
    Route::get("get_sales_report", [ReportController::class, 'getSalesReport']);
    Route::get("get_sales_report/{firstDate}/{lastDate}", [ReportController::class, 'getSalesReportByDate']);
    Route::get("get_sales_report/{selectedDate}", [ReportController::class, 'getSalesReportByDaily']);
    Route::get('get_reconciliation_report', [ReportController::class, 'getReconciliationReport']);
    Route::get('get_reconciliation_report/{firstDate}/{lastDate}', [ReportController::class, 'getReconciliationReportByDate']);
    Route::get('get_po_report/{firstDate}/{lastDate}', [ReportController::class, 'getPoReportByDate']);
    Route::get('get_po_report', [ReportController::class, 'getPoReport']);
    Route::get('get_pr_report/{firstDate}/{lastDate}', [ReportController::class, 'getPrReportByDate']);
    Route::get('get_pr_report', [ReportController::class, 'getPrReport']);
    Route::get('get_pi_report/{firstDate}/{lastDate}', [ReportController::class, 'getPiReportByDate']);
    Route::get('get_pi_report', [ReportController::class, 'getPiReport']);
    Route::get('get_pp_report/{firstDate}/{lastDate}', [ReportController::class, 'getPpReportByDate']);
    Route::get('get_pp_report', [ReportController::class, 'getPpReport']);
    Route::get('get_sa_report/{firstDate}/{lastDate}', [ReportController::class, 'getSaReportByDate']);
    Route::get('get_sa_report', [ReportController::class, 'getSaReport']);

    Route::post("update_web_app_settings", [WebAppSettingsController::class, 'updateData']);


    Route::post("upload_image_only", [UploadImageController::class, 'uploadImageOnly']);

    Route::get("get_specific_notification", [SpecificNotificationController::class, 'getDataAllNoti']);
    Route::get("get_specific_notification/{id}", [SpecificNotificationController::class, 'getDataByUId']);


    Route::get("get_invoice_settings", [InvoiceSettingController::class, 'getDataAllData']);
    Route::post("update_invoice_settings", [InvoiceSettingController::class, 'updateData']);
    Route::get("get_invoice_settings/{id}", [InvoiceSettingController::class, 'getDataDataById']);

    Route::post('/create-razorpay-order', [PaymentController::class, 'createRazorpayOrder']);

    //Coupons
    Route::get('get_all_coupons', [CouponController::class, 'getAllCoupons']);
    Route::post('add_coupon', [CouponController::class, 'createCoupon']);
    Route::put('update_coupon/{id}', [CouponController::class, 'updateCoupon']);
    Route::post('validate_coupon', [CouponController::class, 'validateCoupon']);

    Route::get('get_available_coupons', [CouponController::class, 'getAvailableCouponsForUser']);

    // Referral System
    Route::get('get_referral_code', [ReferralController::class, 'getReferralCode']);

    Route::post('use_referral_code', [ReferralController::class, 'updateReferralCode']);
    Route::put('complete_referral', [ReferralController::class, 'completeReferral']);
});


Route::get("get_available_delivery_location", [AvailableDeliveryLocationController::class, 'getDataAllData']);
Route::get("get_social_media", [SocialMediaController::class, 'getDataAllData']);
Route::get("get_web_app_settings", [WebAppSettingsController::class, 'getDataAllData']);
Route::get("get_web_app_settings/{settingId}", [WebAppSettingsController::class, 'getDataDataById']);
Route::get("get_web_app_settings_by_ids/{settingIds}", [WebAppSettingsController::class, 'getDataDataByIds']);
Route::get("get_web_app_settings/setting/{settingName}", [WebAppSettingsController::class, 'getDataDataByTitle']);
Route::post("login", [LoginController::class, 'login']);
Route::post("login/mobile", [LoginController::class, 'loginMobile']);
Route::post("user_check/mobile", [LoginController::class, 'checkUserRegMobile']);
Route::post("add_user", [UsersController::class, 'addData']);
Route::post("update_pass", [UsersController::class, 'updatePassword']);

Route::post('send_otp', [OTPController::class, 'sendOTP']);
Route::post('verify_otp', [OTPController::class, 'verifyOTP']);
Route::post('update_user_by_phone', [UsersController::class, 'updateDetailsByPheNumber']);
Route::post("check_user", [UsersController::class, 'checkUserExists']);

Route::get("get_pincode/pincode/{pincode}", [AllowPincodeController::class, 'getDataByPincode']);
Route::get("get_products", [ProductController::class, 'getDatas']);
Route::get("get_product/{id}", [ProductController::class, 'getDataById']);
Route::get("get_cat", [CategoryController::class, 'getData']);
Route::get("get_banner/mobile", [BannerImageController::class, 'getMobileImageBanner']);
Route::get("get_sub_cat/cat_id/{catId}", [SubCategoryController::class, 'getDataByCatId']);
Route::get("get_products/sub_cat/{subCatId}", [ProductController::class, 'getDatasBySubCatId']);

Route::get('invoice/{id}', [InvoiceController::class, 'Invoice']);
// Route::get('sub_invoice/{month}/{year}/{id}', [InvoiceController::class, 'SubInvoice']);
Route::get('sub_invoice/{id}', [InvoiceController::class, 'SubInvoice']);

Route::get("get_web_page/page/{id}", [WebPageController::class, 'getDataByPageId']);

Route::get("get_testimonial", [TestimonialController::class, 'getData']);
Route::get("get_testimonial/{pincode}", [TestimonialController::class, 'getDataById']);
Route::get("checkorderassi", [OrderAssignController::class, 'check']);

Route::post('forget-password', [ForgotPasswordController::class, 'ForgetPasswordStore']);
Route::post('reset-password', [ForgotPasswordController::class, 'ResetPasswordStore']);

Route::post('/webhook/razorpay', [RazorpayWebhookController::class, 'handleWebhook']);
Route::post('validate_referral_code', [ReferralController::class, 'validateReferralCodeByPhone']);
       
   //  Route::post("add_usep",[UsersController::class,'addData']);
