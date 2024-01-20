<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Inventory;
use App\Models\ProductBatch;
use App\Models\ProductMaster;
use Illuminate\Support\Facades\DB;
use App\Models\StoreRequest;
use App\Models\StoreRequestDetail;
use App\Models\StoreWare;
use Exception;
use Illuminate\Http\Request;

class StoreRequestsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $storerequests = StoreRequest::latest()->paginate(25);

        return $storerequests;
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

        $storerequest = StoreRequest::create($request->all());

        return response()->json($storerequest, 201);
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
        $storerequest = StoreRequest::findOrFail($id);

        return $storerequest;
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

        $storerequest = StoreRequest::findOrFail($id);
        $storerequest->update($request->all());

        return response()->json($storerequest, 200);
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
        StoreRequest::destroy($id);

        return response()->json(null, 204);
    }


    public function createOrder(Request $request)
    {
        $req = json_decode($request->getContent());
        $user = auth()->guard('api')->user();
        $fromWh = 0;
        try {
            if ($user->type == 'A') {
                $fromWh = $req->req_from_idstore_warehouse;
            } else {


                $userAccess = DB::table('staff_access')
                    ->join('store_warehouse', 'staff_access.idstore_warehouse', '=', 'store_warehouse.idstore_warehouse')
                    ->select(
                        'staff_access.idstore_warehouse',
                        'staff_access.idstaff_access',
                        'store_warehouse.is_store',
                        'staff_access.idstaff'
                    )
                    ->where('staff_access.idstaff', $user->id)
                    ->where('store_warehouse.is_store', 1)
                    ->first();

                if (!isset($userAccess->idstore_warehouse)) {
                    throw new Exception("User Don't Have Access to Store.");
                }
                $fromWh = $userAccess->idstore_warehouse;
            }
            $dt = StoreRequest::create([
                'idstore_warehouse_to' => $req->idstore_warehouse,
                'idstore_warehouse_from' => $fromWh,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1,
            ]);

            foreach ($req->order_detail as $ord) {
                $oReq = [
                    'idstore_request' => $dt->idstore_request,
                    'idproduct_master' => $ord->idproduct_master,
                    'quantity' => ($ord->quantity == "") ? 0 : $ord->quantity,
                    'created_by' => 1,
                    'status' => 1,
                ];
                StoreRequestDetail::create($oReq);
            }
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

    public function getAll()
    {
        $productmaster = DB::table('store_request AS r')
            ->join('store_warehouse AS t', 't.idstore_warehouse', '=', 'r.idstore_warehouse_to')
            ->leftJoin('store_warehouse AS f', 'f.idstore_warehouse', '=', 'r.idstore_warehouse_from')
            ->select(
                't.name AS to_name',
                'f.name AS from_name',
                'r.*'
            )
            ->get();
        return response()->json(["statusCode" => 0, "message" => "Success", "data" => $productmaster], 200);
    }
    public function createRequirementRequest(Request $request)
    {
        $req = json_decode($request->getContent());
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
        try {
            $dt = StoreRequest::create([
                'idstore_warehouse_to' => $req->idstore_warehouse_to,
                'idstore_warehouse_from' => $userAccess->idstore_warehouse,
                'request_type' => 1,
                'created_by' =>  $user->id,
                'status' => 1
            ]);
            foreach ($req->products as $ord) {
                $oReq = [
                    'idstore_request' => $dt->idstore_request,
                    'idproduct_master' => $ord->idproduct_master,
                    'quantity' => ($ord->quantity == "") ? 0 : $ord->quantity,
                    'created_by' =>  $user->id,
                    'status' => 1,
                ];
                StoreRequestDetail::create($oReq);
            }
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 403);
        }
    }

    public function getAllByFilt(Request $request)
    {
        $req = json_decode($request->getContent());
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
        try {

            $orderMaster = DB::table('store_request AS r')
                ->leftJoin('store_warehouse AS t', 't.idstore_warehouse', '=', 'r.idstore_warehouse_to')
                ->leftJoin('store_warehouse AS f', 'f.idstore_warehouse', '=', 'r.idstore_warehouse_from')
                ->select(
                    't.name AS to_name',
                    'f.is_store AS from_is_store',
                    'f.name AS from_name',
                    'r.*'
                );
            if ($req->isMyReqReq) {
                $orderMaster->where('r.idstore_warehouse_from', $userAccess->idstore_warehouse);
            } else {
                $orderMaster->where('r.idstore_warehouse_to', $userAccess->idstore_warehouse);
            }

            if (!!$req->status) {
                $orderMaster->where('r.status', $req->status);
            }
            $orderMaster->whereBetween('r.created_at', [$req->valid_from, $req->valid_till]);
            $orderMaster->orderBy('r.idstore_request', 'DESC');

            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $orderMaster->get()], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 403);
        }
    }

    public function getReqRequestDetail($id)
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

            $cord = DB::table('store_request_detail')
                ->leftJoin('store_request', 'store_request.idstore_request', '=', 'store_request_detail.idstore_request')
                ->leftJoin('product_master', 'store_request_detail.idproduct_master', '=', 'product_master.idproduct_master')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->leftJoin('product_batch', 'store_request_detail.idproduct_batch', '=', 'product_batch.idproduct_batch')
                ->select(
                    'product_master.name AS prod_name',
                    'brands.name AS brand_name',
                    'product_master.barcode',
                    'product_batch.name AS batch_name',
                    'product_batch.mrp',
                    'store_request_detail.*'
                )
                ->where('store_request_detail.idstore_request', $id)->get();

            foreach ($cord as $prod) {
                $batch = ProductBatch::where('idproduct_master', $prod->idproduct_master)
                    ->where('idstore_warehouse', $userAccess->idstore_warehouse)
                    ->where('status', 1)
                    ->where('quantity', '>', 0)
                    ->get();
                $prod->available_batches = $batch;
            }

            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $cord], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

    public function reviewReqReq(Request $request)
    {
        $req = json_decode($request->getContent());
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
        DB::beginTransaction();
        try {

            $dt = [
                'updated_by' =>  $user->id,
                'status' => 2
            ];

            StoreRequest::where('idstore_request', $req->idstore_request)
                ->update($dt);

            foreach ($req->products as $ord) {
                $q = [
                    'quantity_sent' => ($ord->quantity_sent == "") ? 0 : $ord->quantity_sent,
                    'updated_by' =>  $user->id,
                    'idproduct_batch' => $ord->idproduct_batch,
                ];
                StoreRequestDetail::where('idstore_request_detail', $ord->idstore_request_detail)
                    ->update($q);

                DB::table('product_batch')
                    ->where('idproduct_batch', $ord->idproduct_batch)
                    ->update([
                        'quantity' => DB::raw('quantity - ' . $ord->quantity_sent)
                    ]);

                DB::table('inventory')
                    ->where('idproduct_master', $ord->idproduct_master)
                    ->where('idstore_warehouse', $userAccess->idstore_warehouse)
                    ->update([
                        'quantity' => DB::raw('quantity - ' . $ord->quantity_sent)
                    ]);
            }
            DB::commit();
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage(), 403);
        }
    }

    public function acceptReqReq(Request $request)
    {
        $req = json_decode($request->getContent());
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
        DB::beginTransaction();
        try {
            $dt = [
                'updated_by' =>  $user->id,
                'status' => 3
            ];

            StoreRequest::where('idstore_request', $req->idstore_request)
                ->update($dt);

            $cord = DB::table('store_request_detail')
                ->leftJoin('store_request', 'store_request.idstore_request', '=', 'store_request_detail.idstore_request')
                ->leftJoin('product_master', 'store_request_detail.idproduct_master', '=', 'product_master.idproduct_master')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->leftJoin('product_batch', 'store_request_detail.idproduct_batch', '=', 'product_batch.idproduct_batch')
                ->select(
                    'product_master.name AS prod_name',
                    'brands.name AS brand_name',
                    'product_master.barcode',
                    'product_batch.name AS batch_name',
                    'product_batch.mrp',
                    'store_request.idstore_warehouse_to',
                    'store_request.idstore_warehouse_from',
                    'store_request_detail.*'
                )
                ->where('store_request_detail.idstore_request', $req->idstore_request)->get();

            foreach ($cord as $ord) {
                $q = [
                    'quantity_received' => ($ord->quantity_sent == "") ? 0 : $ord->quantity_sent,
                    'updated_by' =>  $user->id,
                ];
                StoreRequestDetail::where('idstore_request_detail', $ord->idstore_request_detail)
                    ->update($q);
                //Batch
                $orgBatchDet = ProductBatch::where('idproduct_batch', $ord->idproduct_batch)->first();

                $destProdBatchDet = ProductBatch::where('idstore_warehouse', $userAccess->idstore_warehouse)
                    ->where('idproduct_master', $ord->idproduct_master)
                    ->where('mrp', $orgBatchDet->mrp)
                    ->first();

                if(isset($destProdBatchDet->idproduct_batch)){
                    DB::table('product_batch')
                    ->where('idproduct_batch', $destProdBatchDet->idproduct_batch)
                    ->update([
                        'quantity' => DB::raw('quantity + ' . $ord->quantity_sent),
                        'expiry' => $orgBatchDet->expiry
                    ]);
                }
                else{
                    $batch = array(
                        'idstore_warehouse' => $userAccess->idstore_warehouse,
                        'idproduct_master' => $ord->idproduct_master,
                        'name' => $orgBatchDet->name,
                        'purchase_price' => $orgBatchDet->purchase_price,
                        'selling_price' => $orgBatchDet->selling_price,
                        'mrp' => $orgBatchDet->mrp,
                        'discount' => $orgBatchDet->discount,
                        'quantity' => $ord->quantity_sent,
                        'expiry' => $orgBatchDet->expiry,
                        'created_by' => $user->id,
                        'status' => 1
                    );
                    ProductBatch::create($batch);
                }
                
                //Inv

                $orgInvDet = Inventory::where('idproduct_master', $ord->idproduct_master)
                    ->where('idstore_warehouse', $cord[0]->idstore_warehouse_to)->first();

                $destInvDet = Inventory::where('idstore_warehouse', $userAccess->idstore_warehouse)
                    ->where('idproduct_master', $ord->idproduct_master)
                    ->first();

                if(isset($destInvDet->idinventory)){
                    DB::table('inventory')
                    ->where('idproduct_master', $ord->idproduct_master)
                    ->where('idstore_warehouse', $userAccess->idstore_warehouse)
                    ->update([
                        'quantity' => DB::raw('quantity + ' . $ord->quantity_sent)
                    ]);
                }

                else{
                    $inv = array(
                        'idstore_warehouse' => $userAccess->idstore_warehouse,
                        'idproduct_master' => $ord->idproduct_master,
                        'purchase_price' => $orgInvDet->purchase_price,
                        'selling_price' => $orgInvDet->selling_price,
                        'mrp' => $orgInvDet->mrp,
                        'discount' => $orgInvDet->discount,
                        'quantity' => $ord->quantity_sent,
                        'only_online' => 0,
                        'only_offline' => 0,
                        'created_by' => $user->id,
                        'status' => 1
                    );
                    $inv = Inventory::create($inv);
                }
            }
            DB::commit();
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json($e->getMessage(), 403);
        }
    }

    public function getStoreOrderDetail($id)
    {
        try {
            $mainReq = StoreRequest::where('idstore_request', $id)->get();
            $res = [];
            $reqDet = DB::table('store_request_detail')
                ->join('product_master', 'store_request_detail.idproduct_master', '=', 'product_master.idproduct_master')
                ->select(
                    'product_master.name',
                    'product_master.barcode',
                    'store_request_detail.*'
                )
                ->where('idstore_request', $id)
                ->get();
            foreach ($reqDet as $re) {
                $batchDet = ProductBatch::where('idproduct_master', $re->idproduct_master)
                    ->where('idstore_warehouse', $mainReq[0]->idstore_warehouse_to)
                    ->where('status', 1)->get();
                $re->batch = $batchDet;
                $re->selected_batch = "";
                $res[] = $re;
            }
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $res], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

    public function dispatchStoreOrder(Request $request)
    {
        $req = json_decode($request->getContent());
        try {
            $mainReq = StoreRequest::where('idstore_request', $req[0]->idstore_request)->first();
            //print_r($mainReq);die;
            foreach ($req as $ord) {
                $qtySent = ($ord->quantity_sent == "") ? $ord->quantity : $ord->quantity_sent;
                $oReq = [
                    'quantity_sent' => $qtySent,
                    'updated_by' => 1,
                    'status' => 2,
                    'idproduct_batch' => $ord->selected_batch
                ];

                StoreRequestDetail::where('idstore_request_detail', $ord->idstore_request_detail)
                    ->update($oReq);

                $currInvDet = Inventory::where('idproduct_master', $ord->idproduct_master)
                    ->where('idstore_warehouse', $mainReq->idstore_warehouse_to)
                    ->first();
                $setQty = $currInvDet->quantity - $qtySent;
                $setQty = ($setQty < 0) ? 0 : $setQty;

                Inventory::where('idproduct_master', $ord->idproduct_master)
                    ->where('idstore_warehouse', $mainReq->idstore_warehouse_to)
                    ->update(['quantity' => $setQty]);
            }
            StoreRequest::where('idstore_request', $ord->idstore_request)
                ->update(['status' => 2, 'dispatch_date' => date('Y-m-d H:i:s')]);
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }



    // Quantity is present in inventory only.
    public function acceptOrder(Request $request)
    {
        $req = json_decode($request->getContent());
        try {
            $mainReq = StoreRequest::where('idstore_request', $req[0]->idstore_request)->first();

            foreach ($req as $ord) {
                $qtyRec = ($ord->quantity_received == "") ? $ord->quantity_sent : $ord->quantity_received;
                $oReq = [
                    'quantity_received' => $qtyRec,
                    'updated_by' => 1,
                    'status' => 3,
                ];

                StoreRequestDetail::where('idstore_request_detail', $ord->idstore_request_detail)
                    ->update($oReq);

                $currInvDet = Inventory::where('idproduct_master', $ord->idproduct_master)
                    ->where('idstore_warehouse', $mainReq->idstore_warehouse_from)
                    ->get();

                if (count($currInvDet) > 0) {
                    Inventory::where('idproduct_master', $ord->idproduct_master)
                        ->where('idstore_warehouse', $mainReq->idstore_warehouse_from)
                        ->increment('quantity', $qtyRec);
                } else {
                    $senderInvDet = Inventory::where('idproduct_master', $ord->idproduct_master)
                        ->where('idstore_warehouse', $mainReq->idstore_warehouse_to)
                        ->first();
                    $inv = array(
                        'idstore_warehouse' => $mainReq->idstore_warehouse_from,
                        'idproduct_master' => $ord->idproduct_master,
                        'purchase_price' => floatval($senderInvDet->purchase_price),
                        'selling_price' => floatval($senderInvDet->selling_price),
                        'mrp' => floatval($senderInvDet->mrp),
                        'discount' => floatval($senderInvDet->discount),
                        'quantity' => $qtyRec,
                        'only_online' => 0,
                        'only_offline' => 0,
                        'created_by' => 1,
                        'updated_by' => 1,
                        'status' => 1
                    );
                    Inventory::create($inv);
                }
                $senderBatchDet = ProductBatch::where('idproduct_batch', $ord->idproduct_batch)->first();

                $currBatchDet = ProductBatch::where('name', $senderBatchDet->name)
                    ->where('idstore_warehouse', $mainReq->idstore_warehouse_from)
                    ->where('idproduct_master', $ord->idproduct_master)
                    ->get();
                if (count($currBatchDet) == 0) {
                    $batch = array(
                        'idstore_warehouse' => $mainReq->idstore_warehouse_from,
                        'idproduct_master' => $ord->idproduct_master,
                        'name' => 'BASE',
                        'purchase_price' => floatval($senderBatchDet->purchase_price),
                        'selling_price' => floatval($senderBatchDet->selling_price),
                        'mrp' => floatval($senderBatchDet->mrp),
                        'discount' => floatval($senderBatchDet->discount),
                        'quantity' => 0,
                        'expiry' => null,
                        'created_by' => 1,
                        'updated_by' => 1,
                        'status' => 1
                    );
                    ProductBatch::create($batch);
                }
            }
            StoreRequest::where('idstore_request', $ord->idstore_request)
                ->update(['status' => 3]);
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }


    // Quantity is present in inventory only.
    public function syncAllProdsToNewSW($id)
    {
        try {
            $detSW = StoreWare::where('idstore_warehouse', $id)->first();
            if (!isset($detSW->idstore_warehouse) || $id == 1) {
                throw new Exception("Invalid Store/WH.");
            }
            $allBaseInv = Inventory::where('idstore_warehouse', 1)
                ->where('status', 1)
                ->get();
            foreach ($allBaseInv as $ord) {
                $inv = array(
                    'idstore_warehouse' => $id,
                    'idproduct_master' => $ord->idproduct_master,
                    'purchase_price' => ($ord->purchase_price),
                    'selling_price' => ($ord->selling_price),
                    'mrp' => ($ord->mrp),
                    'discount' => ($ord->discount),
                    'quantity' => 99,
                    'only_online' => 0,
                    'only_offline' => 0,
                    'created_by' => -1,
                    'updated_by' => -1,
                    'status' => 1
                );
                Inventory::create($inv);

                $batch = array(
                    'idstore_warehouse' => $id,
                    'idproduct_master' => $ord->idproduct_master,
                    'name' => 'BASE',
                    'purchase_price' => floatval($ord->purchase_price),
                    'selling_price' => floatval($ord->selling_price),
                    'mrp' => floatval($ord->mrp),
                    'discount' => floatval($ord->discount),
                    'quantity' => 99,
                    'expiry' => null,
                    'created_by' => -1,
                    'updated_by' => -1,
                    'status' => 1
                );
                ProductBatch::create($batch);
                echo "inserting----<br/>";
                print_r($batch);
                echo "inserted----<br/>";
            }
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
}
