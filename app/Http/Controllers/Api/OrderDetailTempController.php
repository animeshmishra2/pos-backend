<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\OrderDetailTemp;
use Illuminate\Http\Request;

class OrderDetailTempController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $orderdetailtemp = OrderDetailTemp::latest()->paginate(25);

        return $orderdetailtemp;
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
        
        $orderdetailtemp = OrderDetailTemp::create($request->all());

        return response()->json($orderdetailtemp, 201);
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
        $orderdetailtemp = OrderDetailTemp::findOrFail($id);

        return $orderdetailtemp;
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
        
        $orderdetailtemp = OrderDetailTemp::findOrFail($id);
        $orderdetailtemp->update($request->all());

        return response()->json($orderdetailtemp, 200);
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
        OrderDetailTemp::destroy($id);

        return response()->json(null, 204);
    }
}
