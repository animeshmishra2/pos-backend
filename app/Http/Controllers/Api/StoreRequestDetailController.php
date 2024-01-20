<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\StoreRequestDetail;
use Illuminate\Http\Request;

class StoreRequestDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $storerequestdetail = StoreRequestDetail::latest()->paginate(25);

        return $storerequestdetail;
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
        
        $storerequestdetail = StoreRequestDetail::create($request->all());

        return response()->json($storerequestdetail, 201);
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
        $storerequestdetail = StoreRequestDetail::findOrFail($id);

        return $storerequestdetail;
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
        
        $storerequestdetail = StoreRequestDetail::findOrFail($id);
        $storerequestdetail->update($request->all());

        return response()->json($storerequestdetail, 200);
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
        StoreRequestDetail::destroy($id);

        return response()->json(null, 204);
    }
}
