<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\StoreType;
use Illuminate\Http\Request;

class StoreTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $storetype = StoreType::latest()->get();
        return response()->json(["statusCode" => 0, "message" => "Success", "data" => $storetype], 200);
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
        
        $storetype = StoreType::create($request->all());

        return response()->json($storetype, 201);
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
        $storetype = StoreType::findOrFail($id);
        return response()->json(["statusCode" => 0, "message" => "Success", "data" => $storetype], 200);
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
        
        $storetype = StoreType::findOrFail(0);//Disabled
        $storetype->update($request->all());

        return response()->json($storetype, 200);
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
        StoreType::destroy(0);//Disabled

        return response()->json(null, 204);
    }
}
