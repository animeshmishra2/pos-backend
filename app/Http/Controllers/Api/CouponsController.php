<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Exception;
use App\Models\Coupon;
use Illuminate\Http\Request;
use DB;
class CouponsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $allCoupons = Coupon::latest()->paginate(25);

        return $allCoupons;
    }

    public function getByType(Request $request)
    {
        $req = json_decode($request->getContent());
        
        $allCoupons = StoreWare::where('idstorewhouse', $req->idstorewhouse)->latest()->paginate(25);

        return $allCoupons;
    }

  
    public function createCoupon(Request $request)
    {
          $req = json_decode($request->getContent());
         try {
            $user = auth()->guard('api')->user();

               
        $dt = Coupon::create([
                'idstore_warehouse' => $req->idstore_warehouse,
                'name' => $req->name,
                'minordervalue' => $req->minordervalue,
                'discount_percentage' => $req->discount_percentage,
                'discount' => $req->discount,
                'uptomax_amount' => $req->uptomax_amount,
                'usable_days' => $req->usable_days,
                'isgeneral' => $req->isgeneral,
                'reuse_limit' => $req->reuse_limit,
                'status' => $req->status,
                'active_from' => $req->active_from,
                'active_till' => $req->active_till,
                'created_by' => $user->id,
                'updated_by' =>  $user->id,
            ]);
               
         return response()->json(["statusCode" => 0, "message" => "Success" ,"data" => $dt], 200);
         } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
    
    
    public function updateCoupon(Request $request)
    {
        $req = json_decode($request->getContent());
         try {
         $user = auth()->guard('api')->user();
                    if(isset($req->idcoupon))
                    {
                        $up_array = [
                               'idstore_warehouse' => $req->idstore_warehouse,
                            'name' => $req->name,
                            'minordervalue' => $req->minordervalue,
                            'discount_percentage' => $req->discount_percentage,
                            'discount' => $req->discount,
                            'uptomax_amount' => $req->uptomax_amount,
                            'usable_days' => $req->usable_days,
                            'isgeneral' => $req->isgeneral,
                            'reuse_limit' => $req->reuse_limit,
                            'status' => $req->status,
                            'active_from' => $req->active_from,
                            'active_till' => $req->active_till,
                            'created_by' => $user->id,
                            'updated_by' =>  $user->id
                            ];
                        Coupon::where('idcoupon', $req->idcoupon)
                                ->update($up_array);
                        return response()->json(["statusCode" => 0, "message" => "Success" ,"data" => $up_array], 200);
                    }
                    else
                    {
                        return response()->json(["statusCode" => 1, "message" => "Enter idcoupon" ,"data" => array()], 200);
                    }
        
         } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

   


    
     public function getAllCouponData()
    {
        try {
            $couponss = Coupon::all();
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $couponss], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
    
    public function getSingleCouponDetailsByName($name)
    {
        try {
            $couponss = Coupon::where('name', $name)->first();
            if($couponss !=null)
            {
            $coup = array($couponss);
            }
            else
            {
              $coup = array();  
            }
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $coup], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
    
    
    public function getSingleCouponDetailsById($id)
    {
        try {
            $couponss = Coupon::where('idcoupon', $id)->first();
            if($couponss !=null)
            {
            $coup = array($couponss);
            }
            else
            {
              $coup = array();  
            }
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $coup], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
    
     public function deleteCoupon($id)
    {
        try {
            $coupons = Coupon::where('idcoupon', $id)->first();
            if($coupons !=null)
            {
                $coupons->delete();
            }else{
                 return response()->json(["statusCode" => 0, "message" => "This coupon id is Invalid"], 200);
            }
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $coup], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
}
