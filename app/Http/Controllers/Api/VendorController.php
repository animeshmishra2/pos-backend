<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
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
                ->where('store_warehouse.is_store', 0)
                ->first();
            $ven = Vendor::where('idstore_warehouse', $userAccess->idstore_warehouse)->get();
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $ven], 200);
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
            $check = Vendor::where('gst',$req->gst)->first();
            if(isset($check) ){
                return response()->json(["statusCode" => 1, "message" => "Error","err" => $req->gst."- !This gst no has already a registered vendor"], 403);
            }
            $r = [
                'idstore_warehouse' => $userAccess->idstore_warehouse,
                'name' =>  $req->name,
                'address' =>  $req->address,
                'email' =>  $req->email,
                'phone' =>  $req->contact,
                'gst' =>  $req->gst,
                'email' =>  $req->email,
                'state' =>  $req->state,
                'city' =>  $req->city,
                'payment_type' =>  $req->payment_type,
                'credit_day' =>  $req->credit_day,
                'email' =>  $req->email,
                'bank_name' =>  $req->bank_name,
                'benificiary_name' =>  $req->benificiary_name,
                'acc_no' =>  $req->acc_no,
                'ifsc' =>  $req->ifsc,
                'payment_details' => $req->payment_details ?? "",
                'created_by' => $user->id,
                'status' => 1
            ];
            Vendor::create($r);
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
        $vendor = Vendor::findOrFail($id);

        return $vendor;
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

            $package = Vendor::findOrFail($id);
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
        Vendor::destroy($id);

        return response()->json(null, 204);
    }

   
}
