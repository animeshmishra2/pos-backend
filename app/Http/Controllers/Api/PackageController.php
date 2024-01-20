<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Package;
use App\Models\PackageProductList;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($storeId = 0)
    {

        try {
            $user = auth()->guard('api')->user();
            if ($user->user_type === 'A') {
                $idStore = $storeId;
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
                    ->first();
                $idStore = $userAccess->idstore_warehouse;
            }

            $pkgList = Package::where('idstore_warehouse', $idStore)->get();

            $packageListOrg = [];
            foreach ($pkgList as $pkg) {
                $pkg['tagged_prod'] = DB::table('package_prod_list')
                    ->join('product_master', 'package_prod_list.idproduct_master', '=', 'product_master.idproduct_master')
                    ->select(
                        'package_prod_list.idpackage_prod_list',
                        'package_prod_list.quantity',
                        'package_prod_list.idproduct_master',
                        'product_master.name'
                    )
                    ->where('idpackage', $pkg->idpackage)->where('is_triggerer_tag_along', 0)
                    ->get();
                // $pkg['tagged_prod'] = PackageProductList::where('idpackage', $pkg->idpackage)->where('is_triggerer_tag_along', 0)->get();
                // $pkg['trigger_prod'] = PackageProductList::where('idpackage', $pkg->idpackage)->where('is_triggerer_tag_along', 1)->get();

                $pkg['trigger_prod'] = DB::table('package_prod_list')
                    ->join('product_master', 'package_prod_list.idproduct_master', '=', 'product_master.idproduct_master')
                    ->select(
                        'package_prod_list.idpackage_prod_list',
                        'package_prod_list.quantity',
                        'package_prod_list.idproduct_master',
                        'product_master.name'
                    )
                    ->where('idpackage', $pkg->idpackage)->where('is_triggerer_tag_along', 1)
                    ->get();

                $packageListOrg[] = $pkg;
            }
            return response()->json(["statusCode" => 0, "message" => "Coming Here", "data" => $storeId], 200);
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
        $req = json_decode($request->getContent());
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

            $pkg = Package::create(array(
                'idpackage_master' => $req->idpackage_master,
                'idstore_warehouse' => $req->idstore_warehouse,
                'applicable_on' => $req->applicable_on,
                'frequency' => $req->frequency,
                'name' => $req->name,
                'base_trigger_amount' => $req->base_trigger_amount,
                'additional_tag_amount' => $req->additional_tag_amount,
                'bypass_make_gen' => $req->bypass_make_gen,
                'valid_from' => date('Y-m-d', strtotime($req->valid_from)),
                'valid_till' => date('Y-m-d', strtotime($req->valid_till)),
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'status' => 1
            ));
            $triggeringProds = [];
            $taggingProds = [];

            if (count($req->triggeringProds) > 0) {
                foreach ($req->triggeringProds as $tprod) {
                    $triggeringProds[] = [
                        'idpackage' => $pkg->idpackage,
                        'idproduct_master' => $tprod->idproduct_master,
                        'quantity' => $tprod->quantity,
                        'is_triggerer_tag_along' => 1,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'status' => 1
                    ];
                }
                PackageProductList::insert($triggeringProds);
            }
            if (count($req->tagAlongProds) > 0) {
                foreach ($req->tagAlongProds as $tprod) {
                    $taggingProds[] = [
                        'idpackage' => $pkg->idpackage,
                        'idproduct_master' => $tprod->idproduct_master,
                        'quantity' => $tprod->quantity,
                        'is_triggerer_tag_along' => 0,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'status' => 1
                    ];
                }
                PackageProductList::insert($taggingProds);
            }
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
    public function show($storeId)
    {
        try {
            $user = auth()->guard('api')->user();
            if ($user->user_type === 'A') {
                $idStore = $storeId;
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
                    ->first();
                $idStore = $userAccess->idstore_warehouse;
            }

            $pkgList = Package::where('idstore_warehouse', $idStore)->get();

            $packageListOrg = [];
            foreach ($pkgList as $pkg) {
                $pkg['tagged_prod'] = DB::table('package_prod_list')
                    ->join('product_master', 'package_prod_list.idproduct_master', '=', 'product_master.idproduct_master')
                    ->select(
                        'package_prod_list.idpackage_prod_list',
                        'package_prod_list.quantity',
                        'package_prod_list.idproduct_master',
                        'product_master.name'
                    )
                    ->where('idpackage', $pkg->idpackage)->where('is_triggerer_tag_along', 0)
                    ->get();
                // $pkg['tagged_prod'] = PackageProductList::where('idpackage', $pkg->idpackage)->where('is_triggerer_tag_along', 0)->get();
                // $pkg['trigger_prod'] = PackageProductList::where('idpackage', $pkg->idpackage)->where('is_triggerer_tag_along', 1)->get();

                $pkg['trigger_prod'] = DB::table('package_prod_list')
                    ->join('product_master', 'package_prod_list.idproduct_master', '=', 'product_master.idproduct_master')
                    ->select(
                        'package_prod_list.idpackage_prod_list',
                        'package_prod_list.quantity',
                        'package_prod_list.idproduct_master',
                        'product_master.name'
                    )
                    ->where('idpackage', $pkg->idpackage)->where('is_triggerer_tag_along', 1)
                    ->get();

                $packageListOrg[] = $pkg;
            }
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $packageListOrg], 200);
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
            if ($user->user_type !== 'A') {
                throw new Exception("User don't have access.");
            }
            $package = Package::findOrFail($id);
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
        //Package::destroy($id);

        return response()->json(null, 204);
    }

    public function showActive(Request $request)
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
            $idStore = $userAccess->idstore_warehouse;

            $packageListOrg = self::getPkgDetails($idStore);
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $packageListOrg], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

    static function getPkgDetails($idStore)
    {
        $pkgList = Package::where('idstore_warehouse', $idStore)
            ->whereDate('valid_from', '<=', Carbon::today())
            ->whereDate('valid_till', '>=', Carbon::today())
            ->where('status', 1)
            ->get();

        $packageListOrg = [];
        foreach ($pkgList as $pkg) {
            $pkg['tagged_prod'] = DB::table('package_prod_list')
                ->leftJoin('product_master', 'package_prod_list.idproduct_master', '=', 'product_master.idproduct_master')
                ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
                ->leftJoin('product_batch', 'product_batch.idproduct_master', '=', 'product_master.idproduct_master')
                ->select(
                    'package_prod_list.idpackage_prod_list',
                    'package_prod_list.quantity AS package_item_qty',
                    'product_master.idbrand',
                    'product_master.idproduct_master',
                    'product_master.idcategory',
                    'product_master.idsub_category',
                    'product_master.idsub_sub_category',
                    'product_master.name AS prod_name',
                    'product_master.description',
                    'product_master.barcode',
                    'product_master.hsn',
                    'product_master.sgst',
                    'product_master.cgst',
                    'product_master.status',
                    'inventory.quantity',
                    'inventory.idinventory',
                    'product_batch.selling_price',
                    'product_batch.mrp',
                    'product_batch.discount',
                    'product_batch.quantity',
                    'product_batch.idproduct_batch'
                )
                ->where('idpackage', $pkg->idpackage)
                ->where('is_triggerer_tag_along', 0)
                ->where('inventory.idstore_warehouse', $idStore)
                ->where('product_batch.idstore_warehouse', $idStore)
                // ->where('product_batch.name', 'BASE')
                ->get();

            $pkg['trigger_prod'] = DB::table('package_prod_list')
                ->leftJoin('product_master', 'package_prod_list.idproduct_master', '=', 'product_master.idproduct_master')
                ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
                ->leftJoin('product_batch', 'product_batch.idproduct_master', '=', 'product_master.idproduct_master')
                ->select(
                    'package_prod_list.idpackage_prod_list',
                    'package_prod_list.quantity AS package_item_qty',
                    'product_master.idbrand',
                    'product_master.idproduct_master',
                    'product_master.idcategory',
                    'product_master.idsub_category',
                    'product_master.idsub_sub_category',
                    'product_master.name AS prod_name',
                    'product_master.description',
                    'product_master.barcode',
                    'product_master.hsn',
                    'product_master.sgst',
                    'product_master.cgst',
                    'product_master.status',
                    'inventory.quantity',
                    'inventory.idinventory',
                    'product_batch.selling_price',
                    'product_batch.mrp',
                    'product_batch.discount',
                    'product_batch.quantity',
                    'product_batch.idproduct_batch'
                )
                ->where('idpackage', $pkg->idpackage)
                ->where('is_triggerer_tag_along', 1)
                ->where('inventory.idstore_warehouse', $idStore)
                ->where('product_batch.idstore_warehouse', $idStore)
                // ->where('product_batch.name', 'BASE')
                ->get();

            $packageListOrg[] = $pkg;
        }
        return $packageListOrg;
    }

    public function getPackageCustomer($idStore)
    {
        try {
            $user = auth()->guard('api')->user();

            $pkgList = Package::where('idstore_warehouse', $idStore)
                ->where('bypass_make_gen', 0)
                ->whereDate('valid_from', '<=', Carbon::today())
                ->whereDate('valid_till', '>=', Carbon::today())
                ->where('status', 1)
                ->get();

            $packageListOrg = [];
            foreach ($pkgList as $pkg) {
                $pkg['tagged_prod'] = DB::table('package_prod_list')
                    ->leftJoin('product_master', 'package_prod_list.idproduct_master', '=', 'product_master.idproduct_master')
                    ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
                    ->select(
                        'package_prod_list.idpackage_prod_list',
                        'package_prod_list.quantity AS package_item_qty',
                        'product_master.idbrand',
                        'product_master.idproduct_master',
                        'product_master.idcategory',
                        'product_master.idsub_category',
                        'product_master.idsub_sub_category',
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
                        'inventory.discount'
                    )
                    ->where('idpackage', $pkg->idpackage)
                    ->where('is_triggerer_tag_along', 0)
                    ->where('inventory.idstore_warehouse', $idStore)
                    ->get();

                $pkg['trigger_prod'] = DB::table('package_prod_list')
                    ->leftJoin('product_master', 'package_prod_list.idproduct_master', '=', 'product_master.idproduct_master')
                    ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
                    ->select(
                        'package_prod_list.idpackage_prod_list',
                        'package_prod_list.quantity AS package_item_qty',
                        'product_master.idbrand',
                        'product_master.idproduct_master',
                        'product_master.idcategory',
                        'product_master.idsub_category',
                        'product_master.idsub_sub_category',
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
                        'inventory.discount'
                    )
                    ->where('idpackage', $pkg->idpackage)
                    ->where('is_triggerer_tag_along', 1)
                    ->where('inventory.idstore_warehouse', $idStore)
                    ->get();

                $packageListOrg[] = $pkg;
            }
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $packageListOrg], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
}
