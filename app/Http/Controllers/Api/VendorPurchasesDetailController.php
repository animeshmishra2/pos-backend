<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\VendorPurchasesDetail;
use Illuminate\Http\Request;

class VendorPurchasesDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $vendorpurchasesdetail = VendorPurchasesDetail::latest()->paginate(25);

        return $vendorpurchasesdetail;
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
        
        $vendorpurchasesdetail = VendorPurchasesDetail::create($request->all());

        return response()->json($vendorpurchasesdetail, 201);
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
        $vendorpurchasesdetail = VendorPurchasesDetail::findOrFail($id);

        return $vendorpurchasesdetail;
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
        
        $vendorpurchasesdetail = VendorPurchasesDetail::findOrFail($id);
        $vendorpurchasesdetail->update($request->all());

        return response()->json($vendorpurchasesdetail, 200);
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
        VendorPurchasesDetail::destroy($id);

        return response()->json(null, 204);
    }
}
