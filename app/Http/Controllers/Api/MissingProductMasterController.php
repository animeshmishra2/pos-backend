<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Inventory;
use App\Models\ProductBatch;
use App\Models\MissingProductMaster;
use Illuminate\Support\Facades\DB;
use Exception;

use App\Models\ProductMaster;
use Illuminate\Http\Request;

use function PHPUnit\Framework\throwException;

class MissingProductMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
    }

    public function addProduct(Request $req)
    {
        try {
          $user = auth()->guard('api')->user();
          if($req->id==""){
              $id = rand('111111',999999);
          }else{
              $id=$req->id;
          }
          
          $userAccess = DB::table('staff_access')
                    ->leftJoin('store_warehouse', 'staff_access.idstore_warehouse', '=', 'store_warehouse.idstore_warehouse')
                    ->select(
                        'staff_access.idstore_warehouse',
                        'staff_access.idstaff_access',
                        'store_warehouse.is_store',
                        'staff_access.idstaff'
                    )
                    ->where('staff_access.idstaff', $user->id)
                    ->first();
                $idStore = $userAccess->idstore_warehouse;
            $mproductmaster = [
                'idmissing_product_master'=>$id,
                'barcode'=>$req->barcode,
                'name'=>$req->name,
                'mrp'=>$req->mrp,
                'qty'=>$req->qty, 
                'selling_price'=>$req->selling_price, 
                'purcahse_price'=>$req->purchase_price, 
                'status'=>$req->status, 
                'sgst'=>$req->sgst, 
                'cgst'=>$req->cgst,
                'igst'=>$req->igst, 
                'store_id'=>$req->store_id,
                'counter_id'=>$req->counter_id, 
                ];
                
             
            $res = MissingProductMaster::create($mproductmaster);
            
            
             $bdata = [
                 'idstore_warehouse'=>$idStore,
                 'idproduct_master'=>$id,
                 'name'=>"BASE", 
                 'purchase_price'=>$req->purchase_price,
                 'selling_price'=>$req->selling_price, 
                 'mrp'=>$req->mrp,
                 'discount'=>0,
                 'quantity'=>$req->qty, 
                 'expiry'=>null, 
                 'created_by'=>$user->id,
                 'updated_by'=>0, 
                 'status'=>$req->status
                 ];
            $batch = ProductBatch::create($bdata);
            $detail = [
                  'barcode'=>$req->barcode,
                  'batches'=>$bdata,
                  'brand'=>'dd',
                  'category'=>1,
                  'cgst'=>$req->igst,
                  'description'=>'gfhdfghdgs',
                  'discount'=>0,
                  'hsn'=>446161111,
                  'idbrand'=>1,
                  'idcategory'=>1,
                  'idinventory'=>0,
                  'idproduct_master'=>$id,
                  'idsub_category'=>1,
                  'idsub_sub_category'=>1,
                  'igst'=>$req->igst,
                  'instant_discount_percent'=>0,
                  'listing_type'=>"day_deal",
                  'member_price'=>[],
                  'mrp'=>$req->mrp,
                  'origListType'=>'day_deal',
                  'prod_name'=>$req->name,
                   'quantity'=>$req->qty,
                    'scategory'=>1,
                     'selected_batch'=>[],
                      'sellingPriceForInstantDisc'=>0,
                      'selling_price'=>$req->selling_price,
                      'sgst'=>$req->sgst,
                      'sscategory'=>'Everyday Medicine',
                      'status'=>1,
                ];
            return response()->json(["statusCode" => 0, "data" => $detail], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }



}



