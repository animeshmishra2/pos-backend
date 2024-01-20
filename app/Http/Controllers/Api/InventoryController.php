<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;

use App\Models\StoreRequest;
use App\Models\StoreRequestDetail;
use App\Models\Inventory;
use App\Models\ProductBatch;
use DB;
use App\Models\BillwiseRequest;
use App\Models\BillwiseRequestDetail;
use App\Models\DirectTransferRequest;
use App\Models\DirectTransferRequestDetail;
use App\Models\AutoTransferRequest;
use App\Models\AutoTransferRequestDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $inventory = Inventory::latest()->paginate(25);

        return $inventory;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        $inventory = Inventory::create($request->all());

        return response()->json($inventory, 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $inventory = Inventory::findOrFail($id);

        return $inventory;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        $inventory = Inventory::findOrFail($id);
        $inventory->update($request->all());

        return response()->json($inventory, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Inventory::destroy($id);

        return response()->json(null, 204);
    }
    
  public function getPurchases(Request $request)
    {
        $req=json_decode($request->getContent()); 
        $user = auth()->guard('api')->user(); 
                
        $userAccess = DB::table('staff_access')
            ->join('store_warehouse', 'staff_access.idstore_warehouse', '=', 'store_warehouse.idstore_warehouse')
            ->select(
                'staff_access.idstore_warehouse',
                'staff_access.idstaff_access',
                'store_warehouse.is_store',
                'staff_access.idstaff'
            )
            ->where('staff_access.idstaff', $user->id) // replace 2 with $user->id
            ->first();

        try {
            if($userAccess){
                $orderMaster = DB::table('vendor_purchases')
                    ->leftJoin('vendor', 'vendor_purchases.idvendor', '=', 'vendor.idvendor')
                    ->select(
                        'vendor.name AS vendor_name',
                        'vendor.gst AS vendor_gst',
                        'vendor_purchases.*'
                    )->where('vendor_purchases.idstore_warehouse', $userAccess->idstore_warehouse); //replace 1 with $userAccess->idstore_warehouse

                if (isset($req->idvendor) > 0) {
                    $orderMaster->where('vendor_purchases.idvendor', $req->idvendor);
                }

                if (isset($req->bill_number)) {
                    $orderMaster->where('vendor_purchases.bill_number', $req->bill_number);
                } else {
                    $orderMaster->whereBetween('vendor_purchases.created_at', [$req->valid_from, $req->valid_till]);
                }
                $orderMaster->orderBy('vendor_purchases.idvendor_purchases', 'DESC');
                $purchaseData=$orderMaster->get();
                $purchaseArray=[];
                $i=0;
                foreach($purchaseData as $p){
                    $purchaseArray[$i]=$p;
                    $orderDetail = DB::table('vendor_purchases_detail')
                    ->leftJoin('vendor_purchases', 'vendor_purchases_detail.idvendor_purchases', '=', 'vendor_purchases.idvendor_purchases')
                    ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'vendor_purchases_detail.idproduct_master')
                    ->leftJoin('product_batch', 'product_batch.idproduct_master', '=', 'vendor_purchases_detail.idproduct_master')
                    ->select(
                        'product_master.name AS prod_name',
                        'product_master.barcode',
                        'vendor_purchases_detail.*',
                        'product_batch.name as batch_name',
                        'product_batch.mrp as batch_mrp',
                        'product_batch.idproduct_batch as idproduct_batch'
                    )->where('vendor_purchases_detail.idvendor_purchases', $p->idvendor_purchases)
                    ->where('vendor_purchases.idstore_warehouse', $userAccess->idstore_warehouse) //replace 1 with $userAccess->idstore_warehouse
                    ->get();
                    $purchaseArray[$i]->purchase_details=$orderDetail;
                    $i++;
                }
                return response()->json(["statusCode" => 0, "message" => "Success", "data" => $purchaseArray], 200);
            }else{
                return response()->json(["statusCode" => 1, "message" => "", "err" => 'user Access required'], 200);
            }
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => '', "err" => $e->getMessage()], 200);
        }
    }
    public function getPurchaseDetails($id)
    {
        try {
            $user = auth()->guard('api')->user();
            $userAccess = DB::table('staff_access')
                ->join('store_warehouse', 'staff_access.idstore_warehouse', '=', 'store_warehouse.idstore_warehouse')
                ->select(
                    'staff_access.idstore_warehouse',
                    'staff_access.idstaff_access',
                    'store_warehouse.is_store',
                    'staff_access.idstaff'
                )
                ->where('staff_access.idstaff', $user->id)
                ->first();

            $orderDetail = DB::table('vendor_purchases_detail')
                ->leftJoin('vendor_purchases', 'vendor_purchases_detail.idvendor_purchases', '=', 'vendor_purchases.idvendor_purchases')
                ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'vendor_purchases_detail.idproduct_master')
                ->select(
                    'product_master.name AS prod_name',
                    'product_master.barcode',
                    'vendor_purchases_detail.*'
                )->where('vendor_purchases_detail.idvendor_purchases', $id)
                ->where('vendor_purchases.idstore_warehouse', $userAccess->idstore_warehouse)
                ->get();

            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $orderDetail], 200);
        } catch (Exception $e) {
            return response()->json($e->getTrace(), 403);
        }
    }
  
  public function StoreInventory(Request $request)
    {
       try {
        DB::beginTransaction();

        $requestedData = json_decode($request->getContent(), true);
        $user = auth()->guard('api')->user();

        foreach ($requestedData as $req) {
            $storeWarehouseDetail = DB::table('store_warehouse')
                ->where('idstore_warehouse', $req['idstore_warehouse_from'])
                ->first();

            if ($storeWarehouseDetail) {
                $directTransferRequest = [
                    'idstore_warehouse_from' => $req['idstore_warehouse_from'],
                    'idstore_warehouse_to' => $req['to_warehouse_id'],
                    'dispatch_date' => date("Y-m-d"),
                    'dispatched_by' => $user->id,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'status' => 1
                ];

                $createDirectTransfer = DirectTransferRequest::create($directTransferRequest);

                if ($createDirectTransfer) {
                    foreach ($req['products'] as $pro) {
                        $wareProductInvDetail = DB::table('inventory')
                            ->where('idproduct_master', $pro['idproduct_master'])
                            ->where('idstore_warehouse', $req['idstore_warehouse_from'])
                            ->first();

                        if ($wareProductInvDetail) {
                            $updatedQty = $pro['quantity_rec'];

                            if ($wareProductInvDetail->quantity < $updatedQty) {
                                $updatedQty = $wareProductInvDetail->quantity;
                            }

                            DB::table('inventory')
                                ->where('idproduct_master', $pro['idproduct_master'])
                                ->where('idstore_warehouse', $req['to_warehouse_id'])
                                ->update([
                                    'quantity' => DB::raw('quantity + ' . $updatedQty),
                                ]);

                            $billwiseRequestDetail = [
                                'iddirect_transfer_requests' => $createDirectTransfer->id,
                                'idstore_warehouse_to' => $req['to_warehouse_id'],
                                'idproduct_master' => $pro['idproduct_master'],
                                'quantity' => $wareProductInvDetail->quantity,
                                'idproduct_batch' => $pro['idproduct_batch'],
                                'quantity_sent' =>$pro['quantity_rec'],
                                'quantity_received' => $updatedQty,
                                'created_by' => $user->id,
                                'updated_by' => $user->id,
                                'status' => 1
                            ];

                            DirectTransferRequestDetail::create($billwiseRequestDetail);

                            DB::table('inventory')
                                ->where('idproduct_master', $pro['idproduct_master'])
                                ->where('idstore_warehouse', $req['idstore_warehouse_from'])
                                ->update([
                                    'quantity' => DB::raw('quantity - ' . $updatedQty),
                                ]);
                        } else {
                            DB::rollBack();
                            return response()->json(["statusCode" => 1, "message" => '', "err" => 'warehouse product inventory does not exist'], 200);
                        }
                    }
                } else {
                    DB::rollBack();
                    return response()->json(["statusCode" => 1, "message" => '', "err" => 'issue while creating direct transfer request'], 200);
                }
            } else {
                DB::rollBack();
                return response()->json(["statusCode" => 1, "message" => '', "err" => 'Warehouse does not exist'], 200);
            }
        }

        DB::commit();
        return response()->json(["statusCode" => 0, "message" => "Success"], 200);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(["statusCode" => 1, "message" => '', "err" => $e->getMessage()], 200);
    }
    }
    
    
      public function billWiseTransfer(Request $request)
    {
          try {
            DB::beginTransaction();
            $requestedData = json_decode($request->getContent());
            $user = auth()->guard('api')->user();

                  foreach ($requestedData as $req) {

                         $billwiseRequest = array(
                               'idstore_warehouse_from' => $req->idstore_warehouse_from,
                                'idstore_warehouse_to' => $req->to_warehouse_id,
                                'dispatch_date' => date("Y-m-d"),
                                'dispatched_by' => $user->id,
                                'created_by' => $user->id,
                                'updated_by' => $user->id,
                                'status' => 1
                            );
                            $createBillwise = BillwiseRequest::create($billwiseRequest);
                               if ($createBillwise) {
                    foreach ($req->products as $pro) {
                        
                      
                     
                        $productBatchDetail = DB::table('product_batch')
                            ->where('idproduct_master', $pro->idproduct_master)
                            ->where('idstore_warehouse', $pro->idstore_warehouse)
                            ->where('mrp', $pro->mrp)
                            ->where('name', $pro->name)
                            ->first();
                       

                        if($productBatchDetail ){
                            $updatedQty=$pro->quantity_rec;
                            if($productBatchDetail->quantity < $updatedQty){ // check if warehouse Qty lessthan request then only available warehose qty will transfer
                                $updatedQty=$productBatchDetail ->quantity;
                            }
                            if (isset($productBatchDetail->idproduct_batch) && isset($productBatchDetail->idvendor_purchases_detail)) {
                                DB::table('product_batch')
                                    ->where('idproduct_batch', $productBatchDetail->idproduct_batch)
                                    ->update([
                                        'quantity' => DB::raw('quantity + ' . $updatedQty),
                                        'selling_price' => $productBatchDetail->selling_price!=''?$productBatchDetail->selling_price:0,
                                        'purchase_price' => $productBatchDetail->unit_purchase_price!=''?$productBatchDetail->unit_purchase_price:0,
                                        'mrp' => $pro->mrp,
                                        'product'=>$productBatchDetail->product,
                                        'copartner'=>$productBatchDetail->copartner,
                                        'land'=>$productBatchDetail->land,
                                    ]);
                                    $batch_id=$productBatchDetail->idproduct_batch;
                            } else { 
                                $batch = array(
                                    'idstore_warehouse' => $pro->idstore_warehouse,
                                    'idproduct_master' => $pro->idproduct_master,
                                    'name' => $pro->name,
                                    'purchase_price' => floatval($productBatchDetail->purchase_price),
                                    'selling_price' => floatval($productBatchDetail->selling_price),
                                    'mrp' => floatval($pro->mrp),
                                    'product'=>$productBatchDetail->product,
                                    'copartner'=>$productBatchDetail->copartner,
                                    'land'=>$productBatchDetail->land,
                                    'discount' => 0,
                                    'quantity' =>  $productBatchDetail->quantity,
                                    'expiry' => $productBatchDetail->expiry,
                                    'created_by' => $user->id, // replace 1 with $user->id
                                    'updated_by' => $user->id, // replace 1 with $user->id
                                    'status' => 1
                                );
                                $pb = ProductBatch::create($batch);
                                $batch_id=$pb->idproduct_batch;
                            }
                            
                            $productInvDetail = DB::table('inventory')
                                ->where('idproduct_master', $pro->idproduct_master)
                                ->where('idstore_warehouse', $pro->idstore_warehouse)
                                ->first();

                            if ($productInvDetail && $productInvDetail->idinventory) {
                                DB::table('inventory')
                                    ->where('idproduct_master', $pro->idproduct_master)
                                    ->where('idstore_warehouse', $pro->idstore_warehouse)
                                    ->update([
                                        'quantity' => DB::raw('quantity + ' . $updatedQty),
                                        'selling_price' => $productBatchDetail->selling_price!=''?$productBatchDetail->selling_price:0,
                                        'purchase_price' => $productBatchDetail->purchase_price!=''?$productBatchDetail->purchase_price:0,
                                        'mrp' => $pro->mrp,
                                        'product'=>$productBatchDetail->product,
                                        'copartner'=>$productBatchDetail->copartner,
                                        'land'=>$productBatchDetail->land
                                    ]);
                            } else {
                                $inv = array(
                                    'idstore_warehouse' => $pro->idstore_warehouse,
                                    'idproduct_master' => $pro->idproduct_master,
                                    'purchase_price' => $productBatchDetail->purchase_price!=''?$productBatchDetail->purchase_price:0,
                                    'selling_price' => $productBatchDetail->selling_price!=''?$productBatchDetail->selling_price:0,
                                    'mrp' => floatval($pro->mrp),
                                    'product'=>$productBatchDetail->product,
                                    'copartner'=>$productBatchDetail->copartner,
                                    'land'=>$productBatchDetail->land,
                                    'discount' => 0,
                                    'quantity' => $updatedQty,
                                    'only_online' => 0,
                                    'only_offline' => 0,
                                    'created_by' => $user->id, // replace 1 with $user->id
                                    'updated_by' => $user->id, // replace 1 with $user->id
                                    'status' => 1
                                );
                                $inv = Inventory::create($inv);
                            }

                          
                
                            // add request details
                            $billwiseRequestDetail = array(
                                'idbillwise_requests' => $createBillwise->id,
                                'idproduct_master' => $pro->idproduct_master,
                                'idproduct_batch' => $batch_id,
                                'quantity'=>$productBatchDetail->quantity,
                                'quantity_sent'=>$updatedQty,
                                'quantity_received'=>$updatedQty,
                                'created_by' => $user->id, // replace 1 with $user->id
                                'updated_by' => $user->id, // replace 1 with $user->id
                                'status' => 1
                            );
                            $createBillwiseDetails = BillwiseRequestDetail::create($billwiseRequestDetail);
                            // update from qty
                            DB::table('inventory')
                                ->where('idproduct_master', $pro->idproduct_master)
                                ->where('idstore_warehouse', $pro->idstore_warehouse)
                                ->update([
                                    'quantity' => DB::raw('quantity - ' . $updatedQty)
                                ]);


                        }else{
                            return response()->json(["statusCode" => 1, "message" => '', "err" => 'vendor Purchase Detail does not exist'], 200);
                        }
                    }
                               }else {
                    DB::rollBack();
                    return response()->json(["statusCode" => 1, "message" => '', "err" => 'issue while creating direct transfer request'], 200);
                }
                    
                  }
                    DB::commit();
                
                return response()->json(["statusCode" => 0, "message" => "Success"], 200);
       
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(["statusCode" => 1, "message" => '', "err" => $e->getMessage()], 200);
        }
    }
    
   public function getDirectTransferRequest(){
     
         $requestData= DB::table('direct_transfer_requests AS r')
            ->join('store_warehouse AS t', 't.idstore_warehouse', '=', 'r.idstore_warehouse_to')
            ->leftJoin('store_warehouse AS f', 'f.idstore_warehouse', '=', 'r.idstore_warehouse_from')
            ->select(
                't.name AS to_name',
                'f.name AS from_name',
                'r.*'
            );
            
        if (isset($req->valid_from) && isset($req->valid_till)) {
            $requestData->whereBetween('created_at', [$req->valid_from, $req->valid_till]);
        }
        $requestData->orderBy('created_at', 'DESC');
        $TransferData=$requestData->get();
        
        return response()->json(["statusCode" => 0, "message" => "Success", "data" => $TransferData], 200);
    }
    public function getBillwiseTransferRequest(Request $request){
        $user = auth()->guard('api')->user();
          $requestData= DB::table('billwise_requests AS r')
            ->join('store_warehouse AS t', 't.idstore_warehouse', '=', 'r.idstore_warehouse_to')
            ->leftJoin('store_warehouse AS f', 'f.idstore_warehouse', '=', 'r.idstore_warehouse_from')
            ->select(
                't.name AS to_name',
                'f.name AS from_name',
                'r.*'
            );
            
        if (isset($req->valid_from) && isset($req->valid_till)) {
            $requestData->whereBetween('created_at', [$req->valid_from, $req->valid_till]);
        }
        $requestData->orderBy('created_at', 'DESC');
        $TransferData=$requestData->get();
        
        return response()->json(["statusCode" => 0, "message" => "Success", "data" => $TransferData], 200);;
    }
    public function getAutoTransferRequest(Request $request){
         $requestData= DB::table('auto_transfer_requests AS r')
            ->join('store_warehouse AS t', 't.idstore_warehouse', '=', 'r.idstore_warehouse_to')
            ->leftJoin('store_warehouse AS f', 'f.idstore_warehouse', '=', 'r.idstore_warehouse_from')
            ->select(
                't.name AS to_name',
                'f.name AS from_name',
                'r.*'
            );
            
        if (isset($req->valid_from) && isset($req->valid_till)) {
            $requestData->whereBetween('created_at', [$req->valid_from, $req->valid_till]);
        }
        $requestData->orderBy('created_at', 'DESC');
        $TransferData=$requestData->get();
        
        return response()->json(["statusCode" => 0, "message" => "Success", "data" => $TransferData], 200);
    }
    public function getDirectTransferRequestDetail($id){
        
            $orderDetail = DB::table('direct_transfer_request_details')
            ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'direct_transfer_request_details.idproduct_master')
            ->leftJoin('product_batch', 'product_batch.idproduct_batch', '=', 'direct_transfer_request_details.idproduct_batch')
            ->select(
                'product_master.name AS prod_name',
                'product_master.barcode',
                'direct_transfer_request_details.*',
                'product_batch.name as batch_name',
                'product_batch.mrp as batch_mrp'
            )->where('direct_transfer_request_details.iddirect_transfer_requests', $id)
            ->get();
            
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $orderDetail], 200);
       
    }

    public function getBillwiseTransferRequestDetail($id){
        
         $orderDetail = DB::table('billwise_request_details')
            ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'billwise_request_details.idproduct_master')
            ->leftJoin('product_batch', 'product_batch.idproduct_batch', '=', 'billwise_request_details.idproduct_batch')
            ->select(
                'product_master.name AS prod_name',
                'product_master.barcode',
                'billwise_request_details.*',
                'product_batch.name as batch_name',
                'product_batch.mrp as batch_mrp'
            )->where('billwise_request_details.id', $id)
            ->get();
            
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $orderDetail], 200);
    }
    public function getAutoTransferRequestDetail($id){
        
            $user = auth()->guard('api')->user();
            $orderDetail = DB::table('auto_transfer_request_details')
            ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'auto_transfer_request_details.idproduct_master')
            ->leftJoin('product_batch', 'product_batch.idproduct_batch', '=', 'auto_transfer_request_details.idproduct_batch')
            ->select(
                'product_master.name AS prod_name',
                'product_master.barcode',
                'auto_transfer_request_details.*',
                'product_batch.name as batch_name',
                'product_batch.mrp as batch_mrp'
            )->where('auto_transfer_request_details.idauto_transfer_requests', $id)
            ->get();
            
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $orderDetail], 200);
        
    }
}
