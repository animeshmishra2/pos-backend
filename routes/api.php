<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group([
    'prefix' => 'auth',
    'middleware' => ['cors', 'json.response']
], function () {
    Route::post('login', 'Api\AuthController@login');
    Route::post('signup', 'Api\AuthController@signup');
    Route::post('forgot-password', 'AuthController@forgotPassword');
    Route::post('change-password', 'Api\AuthController@updatePassword');
});

Route::group([
    'middleware' => ['cors', 'auth:api']
], function () {
    Route::get('/findByBarcode/{barcode}/{storeId?}/{exact?}', 'Api\ProductMasterController@findByBarcode');
    Route::get('/fetch-batch/{barcode}/{storeId?}/{exact?}', 'Api\ProductMasterController@fetchBatch');
     Route::post('/fetch-bill-wise-batch', 'Api\ProductMasterController@fetchVendorWiseBatch');
    Route::get('/find-product-master/{barcode}/{exact?}', 'Api\ProductMasterController@getProducts');
     Route::get('/find-product-by-wherehouse/{barcode}/{exact?}', 'Api\ProductMasterController@getProductt');
    Route::resource('counter', 'Api\CounterController', ['except' => ['create', 'edit']]);
     Route::get('/get-counter-det', 'Api\CountersLoginController@getLoginDetail');
    
    //Coupon
    Route::resource('coupons', 'Api\CouponsController', ['except' => ['create', 'edit']]);
    Route::get('/get-all-coupons', 'Api\CouponsController@getAllCouponData');
    Route::get('/get-coupon-by-name/{name}', 'Api\CouponsController@getSingleCouponDetailsByName');
    Route::get('/get-coupon-by-id/{id}', 'Api\CouponsController@getSingleCouponDetailsById');
    Route::get('/delete-coupon/{idcoupon}', 'Api\CouponsController@destroy');
    Route::post('/create-coupon', 'Api\CouponsController@createCoupon');
    Route::post('/update-coupon', 'Api\CouponsController@updateCoupon');
    
    
    
    Route::resource('counters-login', 'Api\CountersLoginController', ['except' => ['create', 'edit']]);
    Route::resource('counter-transaction', 'Api\CounterTransactionController', ['except' => ['create', 'edit']]);
    Route::resource('customer', 'Api\CustomerController', ['except' => ['create', 'edit']]);
    Route::resource('customer-address', 'Api\CustomerAddressController', ['except' => ['create', 'edit']]);
    Route::resource('customer-order', 'Api\CustomerOrderController', ['except' => ['create', 'edit']]);
    Route::resource('inventory', 'Api\InventoryController', ['except' => ['create', 'edit']]);
    Route::resource('product-master', 'Api\ProductMasterController', ['except' => ['create', 'edit']]);
    Route::resource('product-batch', 'Api\ProductBatchController', ['except' => ['create', 'edit']]);
    Route::resource('product-master', 'Api\ProductMasterController', ['except' => ['create', 'edit']]);
    Route::resource('staff-access', 'Api\StaffAccessController', ['except' => ['create', 'edit']]);
    Route::resource('vendor', 'Api\VendorController', ['except' => ['create', 'edit']]);
    Route::resource('vendor-purchases', 'Api\VendorPurchasesController', ['except' => ['create', 'edit']]);
    Route::get('calculatemargin','Api\VendorPurchasesController@calculateMargin');
    
    Route::resource('store-ware', 'Api\StoreWareController', ['except' => ['create', 'edit']]);
    Route::post('get-store-ware', 'Api\StoreWareController@getByType');
    Route::get('get-allother-sw', 'Api\StoreWareController@getExceptMine');
    Route::post('update-access', 'Api\UserController@updateAccess');
    Route::resource('staff', 'Api\UserController', ['except' => ['create', 'edit']]);
    Route::get('sync-products', 'Api\ProductMasterController@syncProducts');
    Route::get('/sync-allprods-tonew/{id}', 'Api\StoreRequestsController@syncAllProdsToNewSW');
    Route::resource('store-requests', 'Api\StoreRequestsController', ['except' => ['create', 'edit']]);
    Route::resource('store-request-detail', 'Api\StoreRequestDetailController', ['except' => ['create', 'edit']]);
    Route::get('/search-prod/{barcode}', 'Api\ProductMasterController@search');
    Route::get('/all-store-request', 'Api\StoreRequestsController@getAll');
    Route::post('/all-store-request', 'Api\StoreRequestsController@getAllByFilt');
    Route::post('/create-req-req', 'Api\StoreRequestsController@createRequirementRequest');
    Route::post('/review-req-req', 'Api\StoreRequestsController@reviewReqReq');
    Route::post('/accept-req-req', 'Api\StoreRequestsController@acceptReqReq');
    Route::get('/get-req-req-detail/{id}', 'Api\StoreRequestsController@getReqRequestDetail');
    Route::post('/create-sw-order', 'Api\StoreRequestsController@createOrder');
    Route::get('/store-order-detail/{id}', 'Api\StoreRequestsController@getStoreOrderDetail');
    Route::post('/store-order-dispatch', 'Api\StoreRequestsController@dispatchStoreOrder');
    Route::post('/accept-sw-order', 'Api\StoreRequestsController@acceptOrder');
    Route::post('/open-counter', 'Api\CountersLoginController@open');
    Route::post('/close-counter', 'Api\CountersLoginController@close');
    Route::get('/get-counter/{id}', 'Api\CounterController@getSWCounter');
    Route::get('/get-all-counter-bysw/{id}', 'Api\CounterController@getAllCounterBySW');
    Route::get('/active-counters', 'Api\StaffAccessController@getActiveCounters');

    Route::get('/hold-orders', 'Api\CustomerOrderTempController@getTempOrders');
    Route::get('/hold-order-detail/{id}', 'Api\CustomerOrderTempController@getTempOrderDetail');
    Route::get('/get-customer/{contact}', 'Api\CustomerController@getCustomerByContact');
    
    
    Route::get('online-order-data', 'Api\CustomerOrderController@getOnlineOrder');
    Route::post('update-order-status','Api\CustomerOrderController@updateOrderStatus');

    Route::resource('customer-order-temp', 'Api\CustomerOrderTempController', ['except' => ['create', 'edit']]);
    Route::resource('order-detail-temp', 'Api\OrderDetailTempController', ['except' => ['create', 'edit']]);
    Route::resource('order-detail', 'Api\OrderDetailController', ['except' => ['create', 'edit']]);
    Route::resource('order-detail-t-e-m-p', 'Api\OrderDetailTEMPController', ['except' => ['create', 'edit']]);
    Route::post('/counter-orders', 'Api\UserController@myOrders');
    Route::post('/online-orders', 'Api\UserController@myOnlineOrders');
    Route::post('/update-order-status','Api\OrderController@updateOrderStatus');
    Route::get('/get-order-details/{orderId}', 'Api\CustomerOrderController@getOrderDetail');
    Route::post('/save-customer', 'Api\CustomerController@register');
    Route::post('/update-inventory-qty', 'Api\ProductMasterController@updateQuantity');
    Route::post('/update-inventory-sp', 'Api\ProductMasterController@updateInvDetails');
    Route::resource('wallet', 'Api\WalletController', ['except' => ['create', 'edit']]);
    Route::resource('package-master', 'Api\PackageMasterController', ['except' => ['create', 'edit']]);
    Route::resource('package-product-list', 'Api\PackageProductListController', ['except' => ['create', 'edit']]);
    Route::resource('rate-slab', 'Api\RateSlabController', ['except' => ['create', 'edit']]);
    Route::resource('package', 'Api\PackageController', ['except' => ['create', 'edit']]);
    Route::get('/package/{storeId}', 'Api\PackageController@index');
    Route::get('/active-package', 'Api\PackageController@showActive');
    Route::get('/get-order-detail/{orderId}', 'Api\CustomerOrderController@getOrderDetailById');
    Route::get('/get-sub-cats-by-catid/{catId}', 'Api\SubCategoryController@getSubCatByCatId');
    Route::get('/get-ss-cats-by-scatid/{scatId}', 'Api\SubSubCategoryController@getSSCatBySCatId');
    Route::get('/get-user-bysw/{swId}', 'Api\UserController@getUserBySW');
    Route::post('/add-staff-access', 'Api\UserController@addStaffAccess');
    Route::resource('brand', 'Api\BrandController', ['except' => ['create', 'edit']]);
    Route::resource('category', 'Api\CategoryController', ['except' => ['create', 'edit']]);
    Route::resource('sub-category', 'Api\SubCategoryController', ['except' => ['create', 'edit']]);
    Route::resource('sub-sub-category', 'Api\SubSubCategoryController', ['except' => ['create', 'edit']]);
    Route::post('/add-vendor-bill', 'Api\VendorPurchasesController@addBill');
    Route::post('/get-bills', 'Api\VendorPurchasesController@getPurchases');
    Route::post('/myy-order', 'Api\UserController@myyOrder');
    Route::get('/get-order-dtl/{id}', 'Api\CustomerOrderController@getOrderDetaill');
    // Route::get('/find-product/{id}', 'Api\VendorPurchasesController@getProducts');
    Route::post('/edit-bill/{billid}', 'Api\VendorPurchasesController@editBill');
    Route::post('/update-vendor-bill/{id}', 'Api\VendorPurchasesController@updateBill');
    Route::post('/cancel-order', 'Api\CustomerOrderController@cancelOrder');
    Route::get('/get-bills-details/{id}', 'Api\VendorPurchasesController@getPurchaseDetails');
    Route::resource('vendor-purchases-detail', 'Api\VendorPurchasesDetailController', ['except' => ['create', 'edit']]);
    
    
    Route::get('get-warehouse-list','Api\StoreWareController@warehouseList');
    Route::get('get-store-list','Api\StoreWareController@storeList');
    
    
    //Customer Routes
    Route::post('/update-profile', 'Api\AuthController@updateProfile');
    Route::post('/missing-cart', 'Api\MissingProductMasterController@addProduct');
    // Route::post('/verify-registration', 'Api\AuthController@completeVerification');
    Route::post('/passbook', 'Api\CustomerController@getPassbook');
     Route::post('/get-passbook', 'Api\CustomerController@getPassbookk');
    Route::post('/place-order', 'Api\CustomerOrderTempController@placeOrder');
    Route::post('/change-membership', 'Api\CustomerController@changeMembership');
     Route::post('/change-membershipp', 'Api\CustomerController@changeMembershipp');
    Route::post('/my-orders', 'Api\UserController@myOrders'); //Same as Counter
    Route::post('/change-pay-mode', 'Api\CustomerOrderController@changePayMode');
    Route::get('/prepare-payment/{ordId}', 'Api\PayController@preparePayment');
    Route::post('/confirm-payment', 'Api\PayController@confirmPayment');  //confirm-payment from razorpay
    
    //Banners
    Route::get('get-banner','Api\BannerController@getBanner');
    Route::post('create-banner','Api\BannerController@createBanner');
    Route::post('update-banner','Api\BannerController@updateBanner');
    Route::post('delete-banner','Api\BannerController@destroyBanner');
    
    //SLOTS
    Route::post('create-slot','Api\SlotsController@createSlots');
    Route::post('update-slot','Api\SlotsController@updateSlots');
    Route::get('get-slot','Api\SlotsController@getSlots');
    Route::post('delete-slot','Api\SlotsController@destroySlot');
    Route::post('create-bulk-slot','Api\SlotsController@createBulkSlots');
    Route::post('update-slot-status','Api\SlotsController@updateSlotStatus');
    

    
    //Direct transfer
    Route::post('storeInventory', 'Api\InventoryController@StoreInventory');
      Route::get('get-direct-transfer-request-detail/{id}','Api\InventoryController@getDirectTransferRequestDetail');
        Route::get('get-direct-transfer-request', 'Api\InventoryController@getDirectTransferRequest');
        
    //bill wise transfer
  Route::post('get-purchase','Api\InventoryController@getPurchases');
  Route::post('bill-wise-transfer','Api\InventoryController@billWiseTransfer');
 Route::get('get-billwise-transfer-request', 'Api\InventoryController@getBillwiseTransferRequest');
 Route::get('get-billwise-transfer-request-detail/{id}', 'Api\InventoryController@getBillwiseTransferRequestDetail');
    
  //auto transfer 
  Route::get('get_inventory_threshold_products','Api\InventoryThresholdController@get_inventory_threshold_products');
 Route::post('auto-stock-transfer', 'Api\InventoryThresholdController@autoStockTransfer');
Route::get('get-auto-transfer-request-detail/{id}',  'Api\InventoryController@getAutoTransferRequestDetail');
    Route::get('get-auto-transfer-request',  'Api\InventoryController@getAutoTransferRequest');
    
  //Support
    Route::post('create-issue','Api\SupportController@createIssue');
    Route::post('get-issues','Api\SupportController@getIssuesByID');
    Route::post('get-customer-issues','Api\SupportController@getIssuesByCustomer');
    Route::get('get-support-categories','Api\SupportController@getSupportCategories');

//Shipping Charges
  Route::get('get-shipping-charge', 'Api\ShippingChargeMasterController@getShippingCharge');
Route::post('create-shipping-charge', 'Api\ShippingChargeMasterController@createShippingCharge');
Route::post('update-shipping-charge',  'Api\ShippingChargeMasterController@updateShippingCharge');
Route::post('delete-shipping-charge',  'Api\ShippingChargeMasterController@deleteShippingCharge');

//sms template 
Route::get('get-sms-template',  'Api\SmsTemplateMasterController@getsmsTemplate');
Route::post('create-sms-template',  'Api\SmsTemplateMasterController@createsmsTemplate');
Route::post('update-sms-template', 'Api\SmsTemplateMasterController@updatesmsTemplate');
Route::post('delete-sms-template',  'Api\SmsTemplateMasterController@deletesmsTemplate');


//email template
Route::get('get-email-template',  'Api\EmailTemplateMasterController@getemailTemplate');
Route::post('create-email-template','Api\EmailTemplateMasterController@createemailTemplate');
Route::post('update-email-template', 'Api\EmailTemplateMasterController@updateemailTemplate');
Route::post('delete-email-template', 'Api\EmailTemplateMasterController@deleteemailTemplate');
    
    //purchase order
    
    Route::post('place-orderr','Api\PurchaseOrderController@place_order');
    Route::get('purchase-order-list', 'Api\PurchaseOrderController@get_puchase_order');
    Route::get('generate-pdf/{start_date?}/{end_date?}','Api\PurchaseOrderController@generate_pdf');
    
    
    
    
        //Report API
    Route::get('inventory-report', 'Api\InventoryReportController@get_inventory_report');
    Route::get('product-report', 'Api\ProductReportController@get_product_report');
    Route::get('warehouse-report', 'Api\WarehouseReportController@get_warehouse_report');
    Route::get('order-report', 'Api\CustomerOrderController@getOrder');
    Route::get('order-detail-report', 'Api\CustomerOrderController@getOrderData');
    Route::get('expried-and-expiring-report', 'Api\InventoryReportController@expried_and_expiring_inventory');
    //Gst Report
    Route::get('gstr1','Api\GstReportController@get_gstr1');
    Route::get('gstr2','Api\GstReportController@get_gstr2');
    Route::get('gstr1-detail', 'Api\GstReportController@customer_order_artical_wise');
    Route::get('gstr2-detail', 'Api\GstReportController@purchase_order_artical_wise');
    Route::get('download-excel-gstr1/{year}/{month}/{last_six_month?}', 'Api\ExcelController@download_excel_gstr1');
    Route::get('download-excel-gstr2/{year}/{month}/{last_six_month?}','Api\ExcelController@download_excel_gstr2');
    
    //GRN report
    Route::post('add-order', 'Api\GRNReportController@add_order');
    Route::post('edit-order/{id}', 'Api\GRNReportController@edit_order');
    Route::get('grn-report', 'Api\GRNReportController@get_grn_puchase_order');
    Route::get('confirm-grn-report/{id}', 'Api\GRNReportController@confirm_grn');
 
    
    Route::get('performance-report','Api\SystemReportController@get_performance_report');
    Route::get('inventory-profitability-report','Api\SystemReportController@get_inventory_profitability_report');
    Route::get('inventory-value-report','Api\SystemReportController@get_value_report');
    Route::get('stock-levels-report','Api\SystemReportController@get_stock_levels_report');
    Route::get('inventory-forecasting-report','Api\SystemReportController@inventory_forecasting_report');
    Route::get('sales-report', 'Api\SystemReportController@get_sales_report');
    Route::get('cogs-report','Api\SystemReportController@get_cogs_report');
    Route::get('purchase-order-report', 'Api\SystemReportController@get_purchase_order_report');
});
  
//Open API
// Route::get('barcode-removal','Api\ProductMasterController@barcodeRemoval');
// Route::get('update-igst','Api\ProductMasterController@updateIgst');
// Route::get('cgstsgstapp','Api\ProductMasterController@cgstsgstupdationinfwrongApi');
// Route::get('push-brand','Api\ProductMasterController@brandDis');
// Route::get('clean-data','Api\ProductMasterController@cleanData');
// Route::get('push-data','Api\ProductMasterController@pushData');
// Route::get('update-barcode','Api\ProductMasterController@updateBarcode');
// Route::get('update-plan','Api\ProductMasterController@updatePlan');
// Route::get('hsn-update','Api\ProductMasterController@hsnUpdate');
// Route::get('update-product-records', 'Api\UpdateRecordController@update_product_records');
// Route::get('update-category-records', 'Api\UpdateRecordController@update_category_records');
// Route::get('update-sub-category-records','Api\UpdateRecordController@update_sub_category_records');
// Route::get('update-sub-sub-category-records', 'Api\UpdateRecordController@update_sub_sub_category_records');
// Route::get('update-brands-records','Api\UpdateRecordController@update_brands_records');

Route::get('cities/{id}','Api\UserController@cities');
Route::get('states','Api\UserController@states');
Route::get('/image', 'Api\ProductMasterController@imageUrl');
Route::post('/customer-login', 'Api\AuthController@loginCustomer'); //Email Pass
Route::post('/LoginOTP', 'Api\AuthController@customerRequestOTP');
Route::post('/VerifyOTP', 'Api\AuthController@customerVerifyOTP');
Route::get('/dashboard/{idstore_warehouse}', 'Api\DashboardController@getDashboard');
Route::get('/newdashboard/{idstore_warehouse}', 'Api\DashboardController@getNewDashboard');
Route::get('/category-level-1/{id}', 'Api\CategoryController@getCategoryHierarchy');
Route::get('/category-level-2/{id}', 'Api\SubCategoryController@getSubCategoryHierarchy');
Route::get('get-stores/{lat}/{long}', 'Api\StoreWareController@getStores');
Route::get('/prod-list-brand/{idstore_warehouse}/{id}', 'Api\ProductMasterController@prodListByBrand');
Route::get('find-products/{storeId}/{name}', 'Api\ProductMasterController@findProductByName');
Route::get('/prod-list-catlvl/{idstore_warehouse}/{lvl}/{id}', 'Api\ProductMasterController@prodListCalLvl');
Route::post('/add-to-cart', 'Api\CustomerOrderTempController@addToCart');
Route::post('/remove-from-cart', 'Api\CustomerOrderTempController@removeFromCart');
Route::post('/get-cart', 'Api\CustomerOrderTempController@getCart');
Route::resource('store-type', 'Api\StoreTypeController', ['except' => ['create', 'edit']]);
Route::get('get-delivery-slots/{storeId}', 'Api\StoreWareController@getDeliverySlot');
Route::post('/get-products-by-price', 'Api\ProductMasterController@getProductByPrice');
Route::get('get-packages/{storeId}', 'Api\PackageController@getPackageCustomer');
Route::get('/membership-plans', 'Api\CustomerController@getMembershipMaster');

Route::get('/product-details/{storeId}/{Id}', 'Api\ProductMasterController@getProductDetailById');
