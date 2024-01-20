<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\VendorPurchase;
use App\Models\VendorPurchasesDetail;
use Illuminate\Http\Request;
use App\Models\ProductBatch;
use App\Models\Inventory;
use Illuminate\Support\Facades\DB;
use Exception;

class VendorPurchasesController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $vendorpurchases = VendorPurchase::latest()->paginate(25);

        return $vendorpurchases;
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

        $vendorpurchase = VendorPurchase::create($request->all());

        return response()->json($vendorpurchase, 201);
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
        $vendorpurchase = VendorPurchase::findOrFail($id);

        return $vendorpurchase;
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

        $vendorpurchase = VendorPurchase::findOrFail($id);
        $vendorpurchase->update($request->all());

        return response()->json($vendorpurchase, 200);
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
        VendorPurchase::destroy($id);

        return response()->json(null, 204);
    }
    


    public function addBill(Request $request)
    {
        try {
            DB::beginTransaction();
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
                ->where('store_warehouse.is_store', 0)
                ->first();
            $r = [
                'idvendor' =>  $req->vendor->idvendor,
                'idstore_warehouse' =>  $userAccess->idstore_warehouse,
                'total' =>  $req->total,
                'cgst' =>  $req->cgst?? 0,
                'sgst' =>  $req->sgst?? 0,
                'igst' =>  $req->igst?? 0,
                'bill_number' =>  $req->bill_number,
                 'pay_mode' =>  $req->paymode,
                'paid' =>  $req->paid,
                'balance' => ($req->total - $req->paid),
                'bill_date'=>$req->bill_date,
                'pending_value'=>$req->pending_value,
                'bill_remark'=>$req->bill_remark,
                'items' =>  count($req->products),
                'quantity' =>  $req->quantity,
                'bill_date' =>  $req->bill_date,
                'bill_date' =>  $req->bill_date,
                'created_by' =>  $user->id,
            ];
            $vp = VendorPurchase::create($r);
            
            foreach ($req->products as $pro) {
                $p = [
                    'idvendor_purchases' => $vp->idvendor_purchases,
                    'idproduct_master' =>  $pro->idproduct_master,
                    'mrp' =>  $pro->mrp,
                    'product'=>$pro->product,
                     'copartner'=>$pro->copartner,
                      'land'=>$pro->land,
                    'hsn' =>  $pro->hsn,
                    'quantity' =>  $pro->quantity,
                    'unit_purchase_price' =>  $pro->purchase_price,
                    'selling_price' =>  $pro->selling_price,
                    'free_quantity' =>  $pro->free_quantity,
                    'expiry' =>  implode(',', $pro->expiry),
                    'created_by' =>  $user->id,
                ];
                VendorPurchasesDetail::create($p);
                 $logs = [
                          'idvendor' => $req->vendor->idvendor,
                        'idvendor_purchases' => $vp->idvendor_purchases,
                    'idproduct_master' =>  $pro->idproduct_master,
                    'update_mrp' =>  $pro->mrp,
                    'update_product'=>$pro->product,
                     'update_copartner'=>$pro->copartner,
                      'update_land'=>$pro->land,
                    'update_hsn' =>  $pro->hsn,
                    'update_unit_purchase_price' =>  $pro->purchase_price,
                    'update_selling_price' =>  $pro->selling_price,
                    'free_quantity' =>  $pro->free_quantity,
                    'update_expiry' =>  implode(',', $pro->expiry),
                    'created_by' =>  $user->id,
                     'updated_by' =>  $user->id,
                    ];
                   DB::table('vendor_purchases_detail_logs')->insert($logs);

                $productBatchDetail = DB::table('product_batch')
                    ->where('idproduct_master', $pro->idproduct_master)
                    ->where('idstore_warehouse', $userAccess->idstore_warehouse)
                    ->where('mrp', $pro->mrp)
                    ->first();
                if (isset($productBatchDetail->idproduct_batch)) {
                    DB::table('product_batch')
                        ->where('idproduct_batch', $productBatchDetail->idproduct_batch)
                        ->update([
                            'quantity' => DB::raw('quantity + ' . $pro->quantity),
                            'selling_price' => $pro->selling_price,
                                    'purchase_price' => $pro->purchase_price,
                            'mrp' => $pro->mrp,
                            'product'=>$pro->product,
                     'copartner'=>$pro->copartner,
                      'land'=>$pro->land,
                        ]);
                } else {
                    $batch = array(
                        'idstore_warehouse' => $userAccess->idstore_warehouse,
                        'idproduct_master' => $pro->idproduct_master,
                        'name' => 'ADD',
                        'purchase_price' => floatval($pro->purchase_price),
                        'selling_price' => floatval($pro->selling_price),
                        'product'=>$pro->product,
                     'copartner'=>$pro->copartner,
                      'land'=>$pro->land,
                        'mrp' => floatval($pro->mrp),
                        'discount' => 0,
                        'quantity' =>  $pro->quantity,
                        'expiry' => implode(',', $pro->expiry),
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'status' => 1
                    );
                    $pb = ProductBatch::create($batch);
                }
                $productInvDetail = DB::table('inventory')
                    ->where('idproduct_master', $pro->idproduct_master)
                    ->where('idstore_warehouse', $userAccess->idstore_warehouse)
                    ->first();

                if ($productInvDetail && $productInvDetail->idinventory) {
                    DB::table('inventory')
                        ->where('idproduct_master', $pro->idproduct_master)
                        ->where('idstore_warehouse', $userAccess->idstore_warehouse)
                        ->update([
                            'quantity' => DB::raw('quantity + ' . $pro->quantity),
                            'selling_price' => $pro->selling_price,
                            'purchase_price' => $pro->purchase_price,
                            'mrp' => $pro->mrp,
                            'product'=>$pro->product,
                     'copartner'=>$pro->copartner,
                      'land'=>$pro->land,
                        ]);
                } else {
                    $inv = array(
                        'idstore_warehouse' => $userAccess->idstore_warehouse,
                        'idproduct_master' => $pro->idproduct_master,
                        'purchase_price' => floatval($pro->purchase_price),
                        'selling_price' => floatval($pro->selling_price),
                        'product'=>$pro->product,
                     'copartner'=>$pro->copartner,
                      'land'=>$pro->land,
                        'mrp' => floatval($pro->mrp),
                        
                        'discount' => 0,
                        'quantity' => $pro->quantity,
                        'only_online' => 0,
                        'only_offline' => 0,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'status' => 1
                    );
                    $inv = Inventory::create($inv);
                }
            }
            DB::commit();
            
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(["statusCode" => 1, "message" => '', "err" => $e->getMessage()], 200);
        }
    }
    
     public function editBill($billid)
    {
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
            $vp = DB::table('vendor_purchases')->where('vendor_purchases.idvendor_purchases', $billid)->where('vendor_purchases.idstore_warehouse', $userAccess->idstore_warehouse)
                 ->first();
            $vp_detail = DB::table('vendor_purchases_detail')
                ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'vendor_purchases_detail.idproduct_master')
                ->select(
                    'product_master.name AS prod_name',
                    'product_master.barcode',
                    'product_master.cgst',
                    'product_master.sgst',
                    'product_master.igst',
                      'vendor_purchases_detail.idproduct_master',
                      'vendor_purchases_detail.hsn',
                     'vendor_purchases_detail.idvendor_purchases',
                   'vendor_purchases_detail.quantity',
                    'vendor_purchases_detail.idvendor_purchases_detail',
                    'vendor_purchases_detail.selling_price as instant_price',
                    'vendor_purchases_detail.selling_price',
                     'vendor_purchases_detail.unit_purchase_price as purchase_price',
                    'vendor_purchases_detail.mrp',
                    'vendor_purchases_detail.product',
                    'vendor_purchases_detail.copartner',
                    'vendor_purchases_detail.land',
                    'vendor_purchases_detail.free_quantity',
                    'vendor_purchases_detail.expiry'
                   
                )
                             ->where('vendor_purchases_detail.idvendor_purchases',$vp->idvendor_purchases)
                             ->get();
           
            
            
            return response()->json(["statusCode" => 0, "message" => "Success","data"=>[$vp, 'vendor_purchases_detail'=> $vp_detail]], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => '', "err" => $e->getMessage()], 200);
        }
    }
    
    //   public function getProducts($id)
    // {
    //     try {
    //         // $user = auth()->guard('api')->user();
    //         // if ($user->user_type !== 'A') {
    //         //     // throw new Exception("invalid access");
    //         // }
    //         $productmaster = DB::table('product_master')
    //             ->leftJoin('sub_sub_category', 'sub_sub_category.idsub_sub_category', '=', 'product_master.idsub_sub_category')
    //             ->leftJoin('sub_category', 'sub_category.idsub_category', '=', 'product_master.idsub_category')
    //             ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
    //             ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
    //             ->select(
    //                 'brands.name AS brand',
    //                 'category.name AS category',
    //                 'sub_category.name AS scategory',
    //                 'sub_sub_category.name AS sscategory',
    //                 'product_master.*'
    //             )->where('product_master.idproduct_master', $id)->get();
           
               
             
          
    //         return response()->json(["statusCode" => 0, "data" => $productmaster], 200);
    //     } catch (Exception $e) {
    //         return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
    //     }
    // }

      public function updateBill(Request $request ,$id)
    {
        try {
            DB::beginTransaction();
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
                ->where('store_warehouse.is_store', 0)
                ->first();
                 
            $r = [
                'idvendor' =>  $req->vendor->idvendor,
                'idstore_warehouse' =>  $userAccess->idstore_warehouse,
                'total' =>  $req->total,
                'cgst' =>  $req->cgst,
                'sgst' =>  $req->sgst,
                'igst' => $req->igst,
                'bill_number' =>  $req->bill_number,
                 'pay_mode' =>  $req->paymode,
                'paid' =>  $req->paid,
                'balance' => ($req->total - $req->paid),
                  'bill_date'=>$req->bill_date,
                'pending_value'=>$req->pending_value,
                'bill_remark'=>$req->bill_remark,
                'items' =>  count($req->products),
                'quantity' =>  $req->quantity,
                'bill_date' => $req->bill_date,
                'created_by' =>  $user->id,
            ];
            
            $vp = VendorPurchase::where('idvendor_purchases',$id)->update($r);
         //dd($req->products);
            foreach ($req->products as $pro) {
                
                if(isset($pro->idvendor_purchases_detail)){
                       $p = [
                    'idvendor_purchases' => $pro->idvendor_purchases,
                    'idproduct_master' =>  $pro->idproduct_master,
                    'mrp' =>  $pro->mrp,
                    'product'=>$pro->product,
                     'copartner'=>$pro->copartner,
                      'land'=>$pro->land,
                    'hsn' =>  $pro->hsn,
                    'quantity' =>  $pro->quantity,
                    'unit_purchase_price' =>  $pro->purchase_price,
                    'selling_price' =>  $pro->selling_price,
                    'free_quantity' =>  0,
                    'expiry' =>  implode(',', $pro->expiry),
                    'created_by' =>  $user->id,
                ];
                VendorPurchasesDetail::where('idvendor_purchases_detail',$pro->idvendor_purchases_detail)->update($p);
                }else{
                       $p = [
                    'idvendor_purchases' => $id,
                    'idproduct_master' =>  $pro->idproduct_master,
                    'mrp' =>  $pro->mrp,
                    'product'=>$pro->product,
                     'copartner'=>$pro->copartner,
                      'land'=>$pro->land,
                    'hsn' =>  $pro->hsn,
                    'quantity' =>  $pro->quantity,
                    'unit_purchase_price' =>  $pro->purchase_price,
                    'selling_price' =>  $pro->selling_price,
                    'free_quantity' =>  0,
                    'expiry' =>  implode(',', $pro->expiry),
                    'created_by' =>  $user->id,
                ];
                VendorPurchasesDetail::create($p);
                }
                 //dd($pro);
             
               
                $logs = [
                          'idvendor' => $req->vendor->idvendor,
                        'idvendor_purchases' => $id,
                    'idproduct_master' =>  $pro->idproduct_master,
                    'update_mrp' =>  $pro->mrp,
                    'update_product'=>$pro->product,
                     'update_copartner'=>$pro->copartner,
                      'update_land'=>$pro->land,
                    'update_hsn' =>  $pro->hsn,
                    'update_unit_purchase_price' =>  $pro->purchase_price,
                    'update_selling_price' =>  $pro->selling_price,
                    'free_quantity' =>  $pro->free_quantity,
                    'update_expiry' =>  implode(',', $pro->expiry),
                    'created_by' =>  $user->id,
                     'updated_by' =>  $user->id,
                    ];
                   DB::table('vendor_purchases_detail_logs')->insert($logs);
                $productBatchDetail = DB::table('product_batch')
                    ->where('idproduct_master', $pro->idproduct_master)
                    ->where('idstore_warehouse', $userAccess->idstore_warehouse)
                    ->where('mrp', $pro->mrp)
                    ->first();
                if (isset($productBatchDetail->idproduct_batch)) {
                    DB::table('product_batch')
                        ->where('idproduct_batch', $productBatchDetail->idproduct_batch)
                        ->update([
                            'quantity' => DB::raw('quantity + ' . $pro->quantity),
                            'selling_price' => $pro->selling_price,
                             'purchase_price' => $pro->purchase_price,
                            'mrp' => $pro->mrp,
                            'product'=>$pro->product,
                     'copartner'=>$pro->copartner,
                      'land'=>$pro->land,
                        ]);
                } else {
                    $batch = array(
                        'idstore_warehouse' => $userAccess->idstore_warehouse,
                        'idproduct_master' => $pro->idproduct_master,
                        'name' => 'ADD',
                        'purchase_price' => floatval($pro->purchase_price),
                        'selling_price' => floatval($pro->selling_price),
                        'mrp' => floatval($pro->mrp),
                    'product'=>$pro->product,
                     'copartner'=>$pro->copartner,
                      'land'=>$pro->land,
                        'discount' => 0,
                        'quantity' =>  $pro->quantity,
                        'expiry' => implode(',', $pro->expiry),
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'status' => 1
                    );
                    $pb = ProductBatch::create($batch);
                  
                }
                $productInvDetail = DB::table('inventory')
                    ->where('idproduct_master', $pro->idproduct_master)
                    ->where('idstore_warehouse', $userAccess->idstore_warehouse)
                    ->first();

                if ($productInvDetail && $productInvDetail->idinventory) {
                    DB::table('inventory')
                        ->where('idproduct_master', $pro->idproduct_master)
                        ->where('idstore_warehouse', $userAccess->idstore_warehouse)
                        ->update([
                            'quantity' => DB::raw('quantity + ' . $pro->quantity),
                            'selling_price' => $pro->selling_price,
                            'purchase_price' => $pro->purchase_price,
                            'mrp' => $pro->mrp,
                             'product'=>$pro->product,
                     'copartner'=>$pro->copartner,
                      'land'=>$pro->land
                        ]);
                } else {
                    $inv = array(
                        'idstore_warehouse' => $userAccess->idstore_warehouse,
                        'idproduct_master' => $pro->idproduct_master,
                        'purchase_price' => floatval($pro->purchase_price),
                        'selling_price' => floatval($pro->selling_price),
                        'mrp' => floatval($pro->mrp),
                    'product'=>$pro->product,
                     'copartner'=>$pro->copartner,
                      'land'=>$pro->land,
                        'discount' => 0,
                        'quantity' => $pro->quantity,
                        'only_online' => 0,
                        'only_offline' => 0,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'status' => 1
                    );
                    $inv = Inventory::create($inv);
                }
            }

          
            DB::commit();
            
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(["statusCode" => 1, "message" => '', "err" => $e->getMessage()], 200);
        }
    }
    

    public function getPurchases(Request $request)
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
            $orderMaster = DB::table('vendor_purchases')
                ->leftJoin('vendor', 'vendor_purchases.idvendor', '=', 'vendor.idvendor')
                ->select(
                    'vendor.name AS vendor_name',
                    'vendor.gst AS vendor_gst',
                    'vendor_purchases.*'
                )->where('vendor_purchases.idstore_warehouse', $userAccess->idstore_warehouse);

            if ($req->idvendor > 0) {
                $orderMaster->where('vendor_purchases.idvendor', $req->idvendor);
            }

            if ($req->bill_number) {
                $orderMaster->where('vendor_purchases.bill_number', $req->bill_number);
            } else {
                $orderMaster->whereBetween('vendor_purchases.created_at', [$req->valid_from, $req->valid_till]);
            }
            $orderMaster->orderBy('vendor_purchases.idvendor_purchases', 'DESC');
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $orderMaster->get()], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 403);
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
    
    
    //instant  discount 
    
      public function calculateMargin(Request $req)
    {
        try{
       
        //$pdata = DB::table('product_master')->where('idproduct_master',$req->product_id)->first();
        $margin = (($req->mrp - ($req->purchase_price))/$req->mrp) * 100;
         $value=intVal($margin);
         if($value>=6 && $value<=100){
              $mdat = DB::table('wallet_margin_discount')->where('margin',$value)->select('instant_discount')->first();
              $instant_disc = ($req->mrp * $mdat->instant_discount)/100;
              $instant_price = $req->mrp -$instant_disc ;
        
                $data =[
                      'mrp_margin'=>$margin,
                      'selling_price'=> $instant_price ,
                      'product'=> ($req->mrp - ($instant_disc * 2)),
                      'copartner'=> ($req->mrp - ($instant_disc * 2)),
                      'land'=> ($req->mrp - ($instant_disc * 2.5)),
                    ];
         }else{
            $data =[
                      'mrp_margin'=>$margin,
                      'selling_price'=> $req->mrp ,
                      'product'=> $req->mrp,
                      'copartner'=> $req->mrp,
                      'land'=> $req->mrp,
                    ];
         }
         
        
        
         return response()->json(["statusCode" => 0, "message" => "Success", "data" => $data], 200);
        }catch(Exception $e){
             return response()->json($e->getTrace(), 403);
        }
        
    }
}
