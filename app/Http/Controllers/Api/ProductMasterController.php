<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Inventory;
use App\Models\ProductBatch;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\ProductMaster;
use App\Models\ExcelProdds;
use Illuminate\Http\Request;

use function PHPUnit\Framework\throwException;

class ProductMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
    }

    public function getProducts($barcode, $exact = 0)
    {
        try {
            $user = auth()->guard('api')->user();
            if ($user->user_type !== 'A') {
                // throw new Exception("invalid access");
            }
            $productmaster = DB::table('product_master')
                ->leftJoin('sub_sub_category', 'sub_sub_category.idsub_sub_category', '=', 'product_master.idsub_sub_category')
                ->leftJoin('sub_category', 'sub_category.idsub_category', '=', 'product_master.idsub_category')
                ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->select(
                    'brands.name AS brand',
                    'category.name AS category',
                    'sub_category.name AS scategory',
                    'sub_sub_category.name AS sscategory',
                    'product_master.*'
                );
            if ($exact == 1) {
                $productmaster->where('product_master.barcode', $barcode);
            } else {
                $productmaster->where(function ($query) use ($barcode) {
                    return $query
                        ->where('product_master.barcode', 'like', $barcode . '%')
                        ->orWhere('product_master.name', 'like', $barcode . '%');
                });
            }
            $res = $productmaster->limit(40)->get();
            return response()->json(["statusCode" => 0, "message" => $exact, "data" => $res], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
    
    
     public function getProductt($barcode, $exact = 0)
    {
        try {
            $user = auth()->guard('api')->user();
            if ($user->user_type !== 'A') {
                // throw new Exception("invalid access");
            }
            $productmaster = DB::table('product_master')
                ->leftJoin('sub_sub_category', 'sub_sub_category.idsub_sub_category', '=', 'product_master.idsub_sub_category')
                ->leftJoin('sub_category', 'sub_category.idsub_category', '=', 'product_master.idsub_category')
                ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->select(
                    'product_master.idbrand',
                    'brands.name AS brand',
                    'product_master.idproduct_master',
                    'product_master.idcategory',
                    'category.name AS category',
                    'product_master.idsub_category',
                    'sub_category.name AS scategory',
                    'product_master.idsub_sub_category',
                    'sub_sub_category.name AS sscategory',
                    'product_master.name AS prod_name',
                    'product_master.description',
                    'product_master.barcode',
                     'product_master.hsn',
                    'product_master.sgst',
                    'product_master.cgst',
                    'product_master.igst',
                    'product_master.status'
                );
            if ($exact == 1) {
                $productmaster->where('product_master.barcode', $barcode);
            } else {
                $productmaster->where(function ($query) use ($barcode) {
                    return $query
                        ->where('product_master.barcode', 'like', $barcode . '%')
                        ->orWhere('product_master.name', 'like', $barcode . '%');
                });
            }
            $res = $productmaster->limit(40)->get();
            return response()->json(["statusCode" => 0, "message" => $exact, "data" => $res], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
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
        try {
            $req = json_decode($request->getContent());
            $user = auth()->guard('api')->user();

            if (ProductMaster::where('barcode', $req->barcode)->exists()) {
                return response()->json(["statusCode" => 1, "message" => "Barcode already exists."], 200);
            }

            $r = [
                'name' =>  $req->name,
                'barcode' =>  $req->barcode,
                'sgst' =>  $req->sgst,
                'cgst' =>  $req->cgst,
                'description' =>  $req->description,
                'idbrand' =>  $req->idbrand,
                'idcategory' =>  $req->idcategory,
                'idsub_category' =>  $req->idsub_category,
                'idsub_sub_category' =>  1341,
                'status' =>  $req->status
            ];
            ProductMaster::create($r);
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
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
        $productmaster = ProductMaster::findOrFail($id);

        return $productmaster;
    }
   
   
   public function cgstsgstupdationinfwrongApi()
   {
       
       $dd = 
       $vd = DB::raw('SELECT DISTINCT idbrand FROM `product_master` WHERE cgst OR sgst NOT IN (2.5,6,9) ORDER BY `product_master`.`cgst` DESC');

         return response()->json($vd);
   }
    
   public function getproductNotFromInventory(){
      $product = DB::table('product_master')
    ->groupBy('idproduct_master')
    ->get();

        $data = [];

            foreach ($product as $p) {
                $inventory = DB::table('inventory')
                    ->where('inventory.idproduct_master',$p->idproduct_master)
                    ->where('inventory.idstore_warehouse', 3)
                    ->first();
            
                if (!isset($inventory)) {
                    $data[] = $p->name;
                }
            }

           return response()->json($data);
   }
   public function updatePlan(){
       ini_set('max_execution_time', 32000);
        $product = DB::table('inventory')
    ->join('product_master', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
    ->get();
                      
        foreach($product as $p){
            if($p->product="" || $p->land="" || $p->copartner=""){
               $pdata = DB::table('product_master')->where('idproduct_master',$p->product_id)->first();
      $margin = ($p->mrp - ($p->unit_purchase_price + (($p->unit_purchase_price*($pdata->sgst + $pdata->cgst))/100)))/$p->mrp * 100;
        
         $value=intVal($margin);
         if($value>=6 && $value<=100){
             $mdat = DB::table('wallet_margin_discount')->where('margin',$value)->select('instant_discount')->first();
             $instant_price = $p->mrp - ($p->mrp * $mdat->instant_discount)/100;
        
                $data[] =[
                      'mrp_margin'=>$margin,
                      'instant_price'=> $instant_price ,
                      'product'=> ($p->mrp - ($p->mrp - $instant_price) * 2),
                      'copartner'=> ($p->mrp - ($p->mrp - $instant_price) * 2),
                      'land'=> ($p->mrp - ($p->mrp -$instant_price) * 2.5),
                    ];
                    
                 
                      $update =   DB::table('inventory')->where('idproduct_master',$p->product_id)->update([
                            'product'=>($p->mrp - ($p->mrp - $instant_price) * 2),
                             'copartner'=>($p->mrp - ($p->mrp -$instant_price) * 2),
                              'land'=>($p->mrp - ($p->mrp -$instant_price) * 2.5),
                        ]);
                        

                       
         }else{
             $data[] =[
                      'mrp_margin'=>0,
                      'instant_price'=> 0 ,
                      'product'=> 0,
                      'copartner'=>0,
                      'land'=> 0,
                    ];
                    
                    
                       $update =   DB::table('inventory')->where('idproduct_master',$p->product_id)->update([
                            'product'=>$p->mrp,
                             'copartner'=>$p->mrp,
                              'land'=>$p->mrp,
                        ]);
                       
                       
         }
            }
        }
        
      
        return response()->json(["statusCode" => 0, "data" => $data], 200);
    }
    public function findByBarcode($barcode, $storeId = 0, $exact = 0)
    {
        try {
            $user = auth()->guard('api')->user();
            if ($user->user_type === 'A') {
                if ($storeId == 0) {
                    throw new Exception("invalid store access");
                }
                $idStore = $storeId;
            } else {
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
            }


            $productmaster = DB::table('product_master')
                ->leftJoin('sub_sub_category', 'sub_sub_category.idsub_sub_category', '=', 'product_master.idsub_sub_category')
                ->leftJoin('sub_category', 'sub_category.idsub_category', '=', 'product_master.idsub_category')
                ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
                ->select(
                    'product_master.idbrand',
                    'brands.name AS brand',
                    'product_master.idproduct_master',
                    'product_master.idcategory',
                    'category.name AS category',
                    'product_master.idsub_category',
                    'sub_category.name AS scategory',
                    'product_master.idsub_sub_category',
                    'sub_sub_category.name AS sscategory',
                    'product_master.name AS prod_name',
                    'product_master.description',
                    'product_master.barcode',
                     'product_master.hsn',
                    'product_master.sgst',
                    'product_master.cgst',
                    'product_master.igst',
                    'product_master.status',
                    'inventory.quantity',
                    'inventory.idinventory',
                    'inventory.selling_price',
                     'inventory.purchase_price',
                    'inventory.mrp',
                    'inventory.product',
                    'inventory.copartner',
                    'inventory.land',
                    'inventory.discount',
                    'inventory.instant_discount_percent',
                    'inventory.listing_type',
                    'inventory.listing_type AS origListType'
                );
                
            if ($exact == 1) {
                $productmaster->where('product_master.barcode', $barcode);
            } else {
                $productmaster->where(function ($query) use ($barcode) {
                    return $query
                        ->where('product_master.barcode', 'like', $barcode . '%')
                        ->orWhere('product_master.name', 'like', $barcode . '%');
                });
            }
            
            $res = $productmaster->where('inventory.idstore_warehouse', $idStore)
                ->limit(40)
                ->get();
          
           
            $allProds = Helper::getBatchesAndMemberPrices($res, $idStore);
          
            return response()->json(["statusCode" => 0, "message" => $exact, "data" => $allProds], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
    
    
     public function fetchBatch($barcode, $storeId = 0, $exact = 0)
    {
        try {
            $user = auth()->guard('api')->user();
            if ($user->user_type === 'A') {
                if ($storeId == 0) {
                    throw new Exception("invalid store access");
                }
                $idStore = $storeId;
            } else {
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
            }


            $productmaster = DB::table('product_master')
                ->leftJoin('sub_sub_category', 'sub_sub_category.idsub_sub_category', '=', 'product_master.idsub_sub_category')
                ->leftJoin('sub_category', 'sub_category.idsub_category', '=', 'product_master.idsub_category')
                ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
                ->select(
                    'product_master.idbrand',
                    'brands.name AS brand',
                    'product_master.idproduct_master',
                    'product_master.idcategory',
                    'category.name AS category',
                    'product_master.idsub_category',
                    'sub_category.name AS scategory',
                    'product_master.idsub_sub_category',
                    'sub_sub_category.name AS sscategory',
                    'product_master.name AS prod_name',
                    'product_master.description',
                    'product_master.barcode',
                     'product_master.hsn',
                    'product_master.sgst',
                    'product_master.cgst',
                    'product_master.igst',
                    'product_master.status',
                    'inventory.quantity',
                    'inventory.idinventory',
                    'inventory.selling_price',
                     'inventory.purchase_price',
                    'inventory.mrp',
                    'inventory.product',
                    'inventory.copartner',
                    'inventory.land',
                    'inventory.discount',
                    'inventory.instant_discount_percent',
                    'inventory.listing_type',
                    'inventory.listing_type AS origListType'
                );
                
            if ($exact == 1) {
                $productmaster->where('product_master.barcode', $barcode);
            } else {
                $productmaster->where(function ($query) use ($barcode) {
                    return $query
                        ->where('product_master.barcode', 'like', $barcode . '%')
                        ->orWhere('product_master.name', 'like', $barcode . '%');
                });
            }
            
            $res = $productmaster->where('inventory.idstore_warehouse', $idStore)
                ->limit(40)
                ->get();
          
           
            $allProds = Helper::getBatchesAndMemberPrices($res, $idStore);
          
            return response()->json(["statusCode" => 0, "message" => $exact, "data" => $allProds], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
    
    
      public function fetchVendorWiseBatch(Request $request)
    {
        $req = json_decode($request->getContent());
        try {
            $user = auth()->guard('api')->user();
           


            $cord  = DB::table('product_master')
                ->leftJoin('sub_sub_category', 'sub_sub_category.idsub_sub_category', '=', 'product_master.idsub_sub_category')
                ->leftJoin('sub_category', 'sub_category.idsub_category', '=', 'product_master.idsub_category')
                ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->leftJoin('product_batch', 'product_batch.idproduct_master', '=', 'product_master.idproduct_master')
                ->select(
                    'product_master.name AS prod_name',
                    'brands.name AS brand_name',
                    'product_master.barcode',
                    'product_batch.name AS batch_name',
                    'product_batch.mrp',
                    'product_batch.*'
                )->where('product_batch.idstore_warehouse',1)->where('product_batch.idproduct_master', $req->idproduct_master)->get();
                
          
            
           foreach ($cord as $prod) {
                $batch = ProductBatch::where('idproduct_master', $prod->idproduct_master)
                    ->where('idstore_warehouse',1)
                    ->where('status', 1)
                    ->where('quantity', '>', 0)
                    ->get();
                $prod->available_batches = $batch;
            }

            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $cord], 200);
          
            return response()->json(["statusCode" => 0, "message" => 'Success', "data" => $allProds], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
    public function hsnUpdate(){
        $dat = DB::table('vendor_purchases_detail')
      ->latest()
    ->get();
  
        foreach($dat as $d){
          $hsn[] =  DB::table('product_master')->where('product_master.idproduct_master',$d->idproduct_master)->update(['hsn'=>$d->hsn]);
        }
        return response()->json($hsn);
    }
   public function imageUrl(Request $request)
   {
      $p = ProductMaster::select('barcode')->get();
     foreach($p as $pp){
         $photo=$pp->barcode.'.jpg';
         $image_link[]=env('APP_URL').'images/'.$photo;
     }
     return response()->json($image_link);
   }
   
   public function updateIgst()
   {
    $p = ProductMaster::get();  
    
    foreach($p as $pp){
        $igst = $pp->cgst + $pp->sgst;
         $p[] = ProductMaster::where('idproduct_master',$pp->idproduct_master)->update(['igst'=>$igst]);
    }
    
    return response()->json($p);
   }
   
   
    public function updateQuantity(Request $request)
    {
        $req = json_decode($request->getContent());
        try {
            $user = auth()->guard('api')->user();
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

            DB::table('product_batch')
                ->where('idproduct_batch', $req->idproduct_batch)
                ->where('idstore_warehouse', $req->idstore_warehouse)
                ->update([
                    'quantity' => $req->quantity
                ]);
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

    public function updateInvDetails(Request $request)
    {
        $req = json_decode($request->getContent());
        try {
            $user = auth()->guard('api')->user();
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

            DB::table('inventory')
                ->where('idproduct_master', $req->idproduct_master)
                ->update([
                    'selling_price' => $req->selling_price,
                    'instant_discount_percent' => $req->instant_discount_percent,
                    'listing_type' => $req->listing_type,
                ]);
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
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
        try {
            $user = auth()->guard('api')->user();
            $package = ProductMaster::findOrFail($id);
            $package->update($request->all());
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
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
        ProductMaster::destroy($id);

        return response()->json(null, 204);
    }

    public function syncProducts(Request $request)
    {
        $oldProds = DB::select('SELECT  * FROM excel_prods where `status` = 1 order by idexcel_prods ASC limit 0,3000');


        // $oldProds = DB::select('SELECT count(*) FROM excel_prods WHERE `status`=1;');
        // print_r($oldProds);die; //commented during pushgit 

        foreach ($oldProds as $pro) {
            echo "<br/>Going to Insert " . $pro->idexcel_prods . "<br/>";
            print_r($pro);
            echo "<br/>";
            $subCat = null;
            $subSubCat = null;
            $cat = null;
            $idBrand = null;

            $tmp = DB::select('select idcategory FROM category where name="' . $pro->category . '";');
            $cat = $tmp[0]->idcategory;

            if (strlen($pro->sub_category) > 0) {
                $tmp = DB::select('select idsub_category FROM sub_category where name="' . $pro->sub_category . '";');
                $subCat = $tmp[0]->idsub_category;
            }
            if (strlen($pro->sub_sub_category) > 0) {
                $tmp = DB::select('select idsub_sub_category FROM sub_sub_category where name="' . $pro->sub_sub_category . '";');
                $subSubCat = $tmp[0]->idsub_sub_category;
            }
            if (strlen($pro->brand_name) > 0) {
                $tmp = DB::select('select idbrand FROM brands where name="' . $pro->brand_name . '";');
                $idBrand = $tmp[0]->idbrand;
            }
            // print_r($pro);
            $qr = array(
                'idcategory' => $cat,
                'idsub_category' => $subCat,
                'idsub_sub_category' => $subSubCat,
                'idbrand' => $idBrand,
                'name' => $pro->product_name,
                'description' => "",
                'barcode' => $pro->barcode,
                'hsn' => "",
                'cgst' => floatval($pro->tax) / 2,
                'sgst' => floatval($pro->tax) / 2,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1
            );
            print_r($qr);

            $prod = ProductMaster::create($qr);

            echo "<br/>" . $prod->idproduct_master . " ----- " . $pro->idexcel_prods . "<<<br/>";

            $inv = array(
                'idstore_warehouse' => 1,
                'idproduct_master' => $prod->idproduct_master,
                'purchase_price' => floatval($pro->purchase),
                'selling_price' => floatval($pro->selling_price),
                'mrp' => floatval($pro->mrp),
                'discount' => floatval($pro->discount),
                'quantity' => 99,
                'only_online' => 0,
                'only_offline' => 0,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1
            );

            $inv = Inventory::create($inv);



            $batch = array(
                'idstore_warehouse' => 1,
                'idproduct_master' => $prod->idproduct_master,
                'name' => 'BASE',
                'purchase_price' => floatval($pro->purchase),
                'selling_price' => floatval($pro->selling_price),
                'mrp' => floatval($pro->mrp),
                'discount' => floatval($pro->discount),
                'quantity' => 99,
                'expiry' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1
            );
            //print_r($batch);die;
            $pb = ProductBatch::create($batch);
            DB::update('update excel_prods set status = 0 where idexcel_prods = ' . $pro->idexcel_prods);
            echo "----Done Inserting----" . $inv->idinventory . "----" . $pb->idproduct_batch . "<br/>";
        }

        return response()->json($oldProds, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
     
   
    public function search($barcode)
    {
        $productmaster = DB::table('product_master')
            ->where('barcode', 'like', strval($barcode) . '%')
            ->orWhere('name', 'like', '%' . strval($barcode) . '%')
            ->get();
        return ["statusCode" => 0, "message" => "Success", "data" => $productmaster];
    }
    
       public function updateBarcode()
    {
          $de = DB::table('excel_prodds')->get();
          
          foreach($de as $d){
              if($d->fixdis !=0 && $d->barcode!=''){
                   $sell = $d->mrp * ($d->fixdis/100);
                   $sp= $d->mrp - $sell;
              }else{
                  $sp =$d->mrp;
                  
                 
              }
        //   $dd[]= 'id-'. $d->id.'sp - '.$sp;
            
               $update = ExcelProdds::where('barcode',$d->barcode)->update(['selling_price'=>$sp]);
          }
             
        return ["statusCode" => 0, "message" => "Success", "data" => $de];
    }
    
    
     public function brandDis(){
         $db = DB::table('brandd_masters')->get();
         
         foreach($db as $d){
               $dd = DB::table('excel_prodds')->where('product_name','like','%'.$d->brand.'%')->where('fixdis',0)->update([
                      'fixdis'=>$d->disc
                   ]);   
         }
         
         return response()->json($dd);
     }
    
    
    public function pushData(Request $req){
        $ra = DB::table('excel_prodds')->get();
            
        foreach($ra as $r){
            
           $check = DB::table('product_master')->where('barcode',$r->barcode)->first();
           if(!$check && !isset($check)){
                  $category = DB::table('category')
                              ->join('sub_category','sub_category.idcategory','=','category.idcategory')
                              ->join('sub_sub_category','sub_sub_category.idcategory','=','category.idcategory')
                              ->where('category.name',$r->category)
                              ->first();
                              
          
                  $addProduct =[
                        'idcategory'=>$category->idcategory ?? 1,
                         'idsub_category'=>$category->idsub_category ?? 1,
                          'idsub_sub_category'=>$category->idsub_sub_category ?? 1,
                          'idbrand'=>$category->idsub_sub_category ?? 1,
                          'name'=>$r->product_name,
                          'description'=>$r->product_name,
                          'barcode'=>$r->barcode,
                          'image'=>'no.jpg',
                          'hsn'=>$r->hsncode,
                          'cgst'=>$r->cgst,
                          'sgst'=>$r->sgst,
                          'igst'=>$r->igst,
                          'cess'=>0,
                          'created_by'=>1
                      ];
                      
                    $product =  ProductMaster::create($addProduct);
                $batchCheck = DB::table('product_batch')->where('idproduct_master',$product->idproduct_master)->where('mrp',$r->mrp)->get();
                foreach($batchCheck as $b){
                 if(!isset($b)){
                    $addWarehouse = [
                            'idproduct_master'=>$product->idproduct_master,
                            'idstore_warehouse'=>1,
                            'name'=>'base',
                            'purchase_price'=>$r->prate,
                            'selling_price'=>$r->selling_price,
                            'mrp'=>$r->mrp,
                            'product'=>0,
                            'copartner'=>0,
                            'discount'=>0,
                            'quantity'=>($r->stock<0)? 0:$r->stock ,
                            'gst_status'=>1,
                            'expiry'=> '',
                            'created_by'=>4,
                        ];
                        
                         $addstore = [
                            'idproduct_master'=>$product->idproduct_master,
                            'idstore_warehouse'=>12,
                            'name'=>'base',
                            'purchase_price'=>$r->prate,
                            'selling_price'=>$r->selling_price,
                            'mrp'=>$r->mrp,
                            'product'=>0,
                            'copartner'=>0,
                            'discount'=>0,
                            'quantity'=>($r->stock<0)? 0:$r->stock,
                            'gst_status'=>1,
                            'expiry'=>'',
                            'created_by'=>4,
                        ];
                        
                        
                             $addWarehouse1 = [
                            'idproduct_master'=>$product->idproduct_master,
                            'idstore_warehouse'=>1,
                            'purchase_price'=>$r->prate,
                            'selling_price'=>$r->selling_price,
                            'mrp'=>$r->mrp,
                            'product'=>0,
                            'copartner'=>0,
                            'discount'=>0,
                            'quantity'=>($r->stock<0)? 0:$r->stock,
                            'gst_status'=>1,
                            'created_by'=>4,
                        ];
                        
                         $addstore1 = [
                            'idproduct_master'=>$product->idproduct_master,
                            'idstore_warehouse'=>12,
                            'purchase_price'=>$r->prate,
                            'selling_price'=>$r->selling_price,
                            'mrp'=>$r->mrp,
                            'product'=>0,
                            'copartner'=>0,
                            'discount'=>0,
                            'quantity'=>($r->stock<0)? 0:$r->stock,
                            'gst_status'=>1,
                            'created_by'=>4,
                        ];
                        ProductBatch::create($addWarehouse);
                        ProductBatch::create($addstore);
                        Inventory::create($addWarehouse1);
                        Inventory::create($addstore1);
                 }
                }
                    
           }else{
                $batchCheck = DB::table('product_batch')->where('idproduct_master',$check->idproduct_master)->where('mrp',$r->mrp)->first();
             
                 if(!isset($b)){
                    $addWarehouse = [
                            'idproduct_master'=>$check->idproduct_master,
                            'idstore_warehouse'=>1,
                            'name'=>'base',
                            'purchase_price'=>$r->prate,
                            'selling_price'=>$r->selling_price,
                            'mrp'=>$r->mrp,
                            'product'=>0,
                            'copartner'=>0,
                            'discount'=>0,
                            'quantity'=>($r->stock<0)? 0:$r->stock,
                            'gst_status'=>1,
                            'expiry'=> '',
                            'created_by'=>4,
                        ];
                        
                         $addstore = [
                            'idproduct_master'=>$check->idproduct_master,
                            'idstore_warehouse'=>12,
                            'name'=>'base',
                            'purchase_price'=>$r->prate,
                            'selling_price'=>$r->selling_price,
                            'mrp'=>$r->mrp,
                            'product'=>0,
                            'copartner'=>0,
                            'discount'=>0,
                            'quantity'=>($r->stock<0)? 0:$r->stock,
                            'gst_status'=>1,
                            'expiry'=>'',
                            'created_by'=>4,
                        ];
                        
                        
                        $addWarehouse1 = [
                            'idproduct_master'=>$check->idproduct_master,
                            'idstore_warehouse'=>1,
                            'purchase_price'=>$r->prate,
                            'selling_price'=>$r->selling_price,
                            'mrp'=>$r->mrp,
                            'product'=>0,
                            'copartner'=>0,
                            'discount'=>0,
                            'quantity'=>($r->stock<0)? 0:$r->stock,
                            'gst_status'=>1,
                            'created_by'=>4,
                        ];
                        
                         $addstore1 = [
                            'idproduct_master'=>$check->idproduct_master,
                            'idstore_warehouse'=>12,
                            'purchase_price'=>$r->prate,
                            'selling_price'=>$r->selling_price,
                            'mrp'=>$r->mrp,
                            'product'=>0,
                            'copartner'=>0,
                            'discount'=>0,
                            'quantity'=>($r->stock<0)? 0:$r->stock,
                            'gst_status'=>1,
                            'created_by'=>4,
                        ];
                        ProductBatch::create($addWarehouse);
                        ProductBatch::create($addstore);
                        Inventory::create($addWarehouse1);
                        Inventory::create($addstore1);
                 }else{
                                         $addWarehouse = [
                            'name'=>'base',
                            'purchase_price'=>$r->prate,
                            'selling_price'=>$r->selling_price,
                            'mrp'=>$r->mrp,
                            'product'=>0,
                            'copartner'=>0,
                            'discount'=>0,
                            'quantity'=>$r->stock,
                            'expiry'=> '',
                            'created_by'=>4,
                        ];
                        
                         $addstore = [
                            'name'=>'base',
                            'purchase_price'=>$r->prate,
                            'selling_price'=>$r->selling_price,
                            'mrp'=>$r->mrp,
                            'product'=>0,
                            'copartner'=>0,
                            'discount'=>0,
                            'quantity'=>($r->stock<0)? 0:$r->stock,
                            'expiry'=>'',
                            'created_by'=>4,
                        ];
                        
                                     $addWarehouse1 = [
                            'purchase_price'=>$r->prate,
                            'selling_price'=>$r->selling_price,
                            'mrp'=>$r->mrp,
                            'product'=>0,
                            'copartner'=>0,
                            'discount'=>0,
                            'quantity'=>$r->stock,
                            'created_by'=>4,
                        ];
                        
                         $addstore1 = [
                            'purchase_price'=>$r->prate,
                            'selling_price'=>$r->selling_price,
                            'mrp'=>$r->mrp,
                            'product'=>0,
                            'copartner'=>0,
                            'discount'=>0,
                            'quantity'=>($r->stock<0)? 0:$r->stock,
                            'created_by'=>4,
                        ];
                        ProductBatch::where('idproduct_master',$check->idproduct_master)->update($addWarehouse);
                        ProductBatch::where('idproduct_master',$check->idproduct_master)->update($addstore);
                        Inventory::where('idproduct_master',$check->idproduct_master)->update($addWarehouse1);
                        Inventory::where('idproduct_master',$check->idproduct_master)->update($addstore1);
                 }
               
           }
           
          
        }
       
    }
    


    public function prodListCalLvl($idstore_warehouse, $lvl, $id)
    {
        try {
            $productmaster = DB::table('product_master')
                ->leftJoin('sub_sub_category', 'sub_sub_category.idsub_sub_category', '=', 'product_master.idsub_sub_category')
                ->leftJoin('sub_category', 'sub_category.idsub_category', '=', 'product_master.idsub_category')
                ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
                ->select(
                    'product_master.idbrand',
                    'brands.name AS brand',
                    'product_master.idproduct_master',
                    'product_master.idcategory',
                    'category.name AS category',
                    'product_master.idsub_category',
                    'sub_category.name AS scategory',
                    'product_master.idsub_sub_category',
                    'sub_sub_category.name AS sscategory',
                    'product_master.name AS prod_name',
                    'product_master.description',
                    'product_master.barcode',
                    'product_master.barcode',
                    'product_master.hsn',
                    'product_master.sgst',
                    'product_master.cgst',
                    'product_master.is_veg',
                    'product_master.status',
                    'inventory.quantity',
                    'inventory.idinventory',
                    'inventory.selling_price',
                    'inventory.mrp',
                    'inventory.discount',
                    'inventory.product',
                    'inventory.copartner',
                     'inventory.land',
                    'inventory.instant_discount_percent',
                    'inventory.listing_type'
                );
            if ($lvl == 1) {
                $productmaster->where('product_master.idcategory', $id);
            } elseif ($lvl == 2) {
                $productmaster->where('product_master.idsub_category', $id);
            } elseif ($lvl == 3) {
                $productmaster->where('product_master.idsub_sub_category', $id);
            } else {
                throw new Exception("Bad Request");
            }
             $productmaster->where('inventory.idstore_warehouse', $idstore_warehouse);
            $productmaster->orderBy('inventory.quantity', 'desc');
           
            $res = $productmaster->simplePaginate(50);
            $count = $res->count();
            $pageNo=$res->currentPage();
            $allProd = Helper::getBatchesAndMemberPrices($res, $idstore_warehouse);
            return response()->json(["statusCode" => 0, "message" => 'success',"totalCount"=>$count,"pageNo"=>$pageNo, "data" => $allProd], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

    public function prodListByBrand($idstore_warehouse, $id)
    {
        try {
            $productmaster = DB::table('product_master')
                ->leftJoin('sub_sub_category', 'sub_sub_category.idsub_sub_category', '=', 'product_master.idsub_sub_category')
                ->leftJoin('sub_category', 'sub_category.idsub_category', '=', 'product_master.idsub_category')
                ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
                ->select(
                    'product_master.idbrand',
                    'brands.name AS brand',
                    'product_master.idproduct_master',
                    'product_master.idcategory',
                    'category.name AS category',
                    'product_master.idsub_category',
                    'sub_category.name AS scategory',
                    'product_master.idsub_sub_category',
                    'sub_sub_category.name AS sscategory',
                    'product_master.name AS prod_name',
                    'product_master.description',
                    'product_master.barcode',
                    'product_master.hsn',
                    'product_master.sgst',
                    'product_master.cgst',
                    'product_master.is_veg',
                    'product_master.status',
                    'inventory.quantity',
                    'inventory.idinventory',
                    'inventory.selling_price',
                    'inventory.mrp',
                    'inventory.discount',
                    'inventory.product',
                    'inventory.copartner',
                    'inventory.land',
                    'inventory.instant_discount_percent',
                    'inventory.listing_type'
                );
            if ($id > 0) {
                $productmaster->where('product_master.idbrand', $id);
            } else {
                throw new Exception("Bad Request");
            }
             $productmaster->where('inventory.idstore_warehouse', $idstore_warehouse);
            $productmaster->orderBy('inventory.quantity', 'desc');
            $res = $productmaster->simplePaginate(50);
            $allProd = Helper::getBatchesAndMemberPrices($res, $idstore_warehouse);
            return response()->json(["statusCode" => 0, "message" => 'success', "data" => $allProd], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

    public function findProductByName($storeId, $name)
    {
        try {
            // $user = auth()->guard('api')->user();
            // if ($user->user_type === 'C') {
            //     if ($storeId == 0) {
            //         throw new Exception("invalid Store Access");
            //     }
            //     $idStore = $storeId;
            // }
            $productmaster = DB::table('product_master')
                ->leftJoin('sub_sub_category', 'sub_sub_category.idsub_sub_category', '=', 'product_master.idsub_sub_category')
                ->leftJoin('sub_category', 'sub_category.idsub_category', '=', 'product_master.idsub_category')
                ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
                ->select(
                    // 'product_master.idbrand',
                    'brands.name AS brand',
                    'product_master.idproduct_master',
                    // 'product_master.idcategory',
                    'category.name AS category',
                    // 'product_master.idsub_category',
                    'sub_category.name AS scategory',
                    // 'product_master.idsub_sub_category',
                    'sub_sub_category.name AS sscategory',
                    'product_master.name AS prod_name',
                    'product_master.description',
                    'product_master.barcode',
                    'product_master.hsn',
                    'product_master.sgst',
                    'product_master.cgst',
                    'product_master.status',
                    'inventory.quantity',
                    'inventory.idinventory',
                    'inventory.selling_price',
                    'inventory.mrp',
                    'inventory.discount',
                    'inventory.product',
                    'inventory.copartner',
                    'inventory.land',
                    'inventory.instant_discount_percent',
                    'inventory.listing_type'
                );

            $productmaster->where('product_master.name', 'like', '%' . $name . '%');
            $res = $productmaster->where('inventory.idstore_warehouse', $storeId)
                ->limit(50)
                ->get();
            $allProd = Helper::getBatchesAndMemberPrices($res, $storeId);
            return response()->json(["statusCode" => 0, "message" => "success", "data" => $allProd], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
    
    public function getProductDetailById($storeId,$id)
    {
        
        try {
            
            $productmaster = DB::table('product_master')
                ->leftJoin('sub_sub_category', 'sub_sub_category.idsub_sub_category', '=', 'product_master.idsub_sub_category')
                ->leftJoin('sub_category', 'sub_category.idsub_category', '=', 'product_master.idsub_category')
                ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
                ->select(
                    // 'product_master.idbrand',
                    'brands.name AS brand',
                     'brands.logo AS brand_image',
                    'product_master.idproduct_master',
                    // 'product_master.idcategory',
                    'category.name AS category',
                    // 'product_master.idsub_category',
                    'sub_category.name AS scategory',
                    // 'product_master.idsub_sub_category',
                    'sub_sub_category.name AS sscategory',
                    'product_master.name AS prod_name',
                    'product_master.description',
                    'product_master.barcode',
                    'product_master.hsn',
                    'product_master.sgst',
                    'product_master.cgst',
                    'product_master.is_veg',
                    'product_master.status',
                     'product_master.idbrand',
                     'product_master.idcategory',
                    'inventory.quantity',
                    'inventory.idinventory',
                    'inventory.selling_price',
                    'inventory.mrp',
                    'inventory.discount',
                     'inventory.product',
                      'inventory.copartner',
                       'inventory.land',
                    'inventory.instant_discount_percent',
                    'inventory.listing_type'
                );
                
            $productmaster->where('product_master.idproduct_master', $id);
            $res = $productmaster->where('inventory.idstore_warehouse', $storeId)
                ->limit(80)
                ->get();
              
           
            $allProd = Helper::getBatchesAndMemberPricesWithRelatedProducts($res, $storeId);
            return response()->json(["statusCode" => 0, "message" => "success", "data" => $allProd], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

       public function barcodeRemoval(){
        
      $duplicateProducts = ProductMaster::select('*')
    ->whereIn('barcode', function ($query) {
        $query->select('barcode')
            ->from('product_master')
            ->groupBy('barcode')
            ->havingRaw('COUNT(barcode) > 1');
    })
    ->get();
        //$data=[];
        foreach ($duplicateProducts  as $barcode) {
            $pbcheck= DB::table('product_batch')->where('idproduct_master',$barcode->idproduct_master)->exists();
             $icheck= DB::table('inventory')->where('idproduct_master',$barcode->idproduct_master)->exists();
              $vcheck= DB::table('vendor_purchases_detail')->where('idproduct_master',$barcode->idproduct_master)->exists();
              
              if($icheck){
                  $id[] = $barcode->barcode;
              }
            
           
              
        }
       
        
        
     return response()->json($id);
        
    }
    public function getProductByPrice(Request $request)
    {
        try {
            $req = json_decode($request->getContent());

            // $user = auth()->guard('api')->user();
            // $userAccess = DB::table('staff_access')
            //     ->leftJoin('store_warehouse', 'staff_access.idstore_warehouse', '=', 'store_warehouse.idstore_warehouse')
            //     ->select(
            //         'staff_access.idstore_warehouse',
            //         'staff_access.idstaff_access',
            //         'store_warehouse.is_store',
            //         'staff_access.idstaff'
            //     )
            //     ->where('staff_access.idstaff', $user->id)
            //     ->first();
            // $idStore = $userAccess->idstore_warehouse;

            $productmaster = DB::table('product_master')
                ->leftJoin('sub_sub_category', 'sub_sub_category.idsub_sub_category', '=', 'product_master.idsub_sub_category')
                ->leftJoin('sub_category', 'sub_category.idsub_category', '=', 'product_master.idsub_category')
                ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
                ->select(
                    'product_master.idbrand',
                    'brands.name AS brand',
                    'product_master.idproduct_master',
                    'product_master.idcategory',
                    'category.name AS category',
                    'product_master.idsub_category',
                    'sub_category.name AS scategory',
                    'product_master.idsub_sub_category',
                    'sub_sub_category.name AS sscategory',
                    'product_master.name AS prod_name',
                    'product_master.description',
                    'product_master.barcode',
                    'product_master.hsn',
                    'product_master.sgst',
                    'product_master.cgst',
                    'product_master.status',
                    'inventory.quantity',
                    'inventory.idinventory',
                    'inventory.selling_price',
                    'inventory.mrp',
                    'inventory.discount',
                     'inventory.product',
                      'inventory.copartner',
                       'inventory.land',
                    'inventory.discount',
                    'inventory.instant_discount_percent',
                    'inventory.listing_type'
                );
            $productmaster->where('inventory.selling_price', '>=', $req->minAmount);
            $productmaster->where('inventory.selling_price', '<=', $req->maxAmount);

            switch ($req->search_by) {
                case 'cat':
                    $productmaster->where('product_master.idcategory', $req->idcategory);
                    break;
                case 'scat':
                    $productmaster->where('product_master.idsub_category', $req->idsub_category);
                    break;
                case 'sscat':
                    $productmaster->where('product_master.idsub_sub_category', $req->idsub_sub_category);
                    break;
                case 'name':
                    $productmaster->where('product_master.name', 'like', $req->name . '%');
                    break;
                default:
                    break;
            }

            $res = $productmaster->where('inventory.idstore_warehouse', $req->store_id)
                ->limit(50)
                ->get();
            $allProds = Helper::getBatchesAndMemberPrices($res, $req->store_id);
            return response()->json(["statusCode" => 0, "message" => 'Success', "data" => $allProds], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
}



// php artisan crud:api PackageMaster --fields='idpackage_master#int; name#string; triggered_on#string; created_by#int; updated_by#int; status#int' --controller-namespace=Api

// php artisan crud:api Package --fields='idpackage#int; idpackage_master#int; idstore_warehouse#int; applicable_on#string; frequency#string; base_trigger_amount#int; additional_tag_amount#int; created_by#int; updated_by#int; status#int' --controller-namespace=Api

// php artisan crud:api PackageProductList --fields='idpackage_prod_list#int;  idpackage#int; idproduct_master#int; quantity#double;is_triggerer_tag_along#int;created_by#int; updated_by#int; status#int' --controller-namespace=Api

// php artisan crud:api RateSlab --fields='idrate_slab#int;  idpackage#int; from_amount#double; till_amount#double; additional_amount#double;created_by#int; updated_by#int; status#int' --controller-namespace=Api
