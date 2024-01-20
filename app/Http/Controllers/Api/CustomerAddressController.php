<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Exception;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;

class CustomerAddressController extends Controller
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
            $address = CustomerAddress::where('idcustomer', $user->id)->get();
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $address], 200);
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
            CustomerAddress::create(
                [
                    'idcustomer' => $user->id,
                    'name' => $req->name,
                    'address' => $req->address,
                    'pincode' => $req->pincode,
                    'landmark' => $req->landmark,
                    'is_default' => $req->is_default,
                    'phone' => $req->phone,
                    'created_by' => $user->id,
                    'lat' => $req->lat,
                    'long' => $req->long,
                    'tag' => $req->tag,
                    'status' => $req->status
                ]
            );
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
        $customeraddress = CustomerAddress::findOrFail($id);

        return $customeraddress;
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
            $req = json_decode($request->getContent());
            $user = auth()->guard('api')->user();
            $customeraddress = CustomerAddress::where('idcustomer_address', $id)->where('idcustomer', $user->id)->first();
            if ($customeraddress->idcustomer_address > 0) {
                CustomerAddress::where('idcustomer_address', $id)->where('idcustomer', $user->id)->update(
                    [
                        'name' => $req->name,
                        'address' => $req->address,
                        'pincode' => $req->pincode,
                        'landmark' => $req->landmark,
                        'is_default' => $req->is_default,
                        'phone' => $req->phone,
                        'updated_by' => $user->id,
                        'lat' => $req->lat,
                        'long' => $req->long,
                        'tag' => $req->tag,
                        'status' => $req->status
                    ]
                );
            } else {
                throw new Exception("Invalid Access");
            }
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
        CustomerAddress::destroy($id);

        return response()->json(null, 204);
    }
}
