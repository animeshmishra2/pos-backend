<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\PackageMaster;
use Illuminate\Http\Request;

class PackageMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $packagemaster = PackageMaster::get();
        return response()->json(["statusCode" => 0, "message" => "Success", "data" => $packagemaster], 200);
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
        
        $packagemaster = PackageMaster::create($request->all());

        return response()->json($packagemaster, 201);
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
        $packagemaster = PackageMaster::findOrFail($id);

        return $packagemaster;
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
        
        $packagemaster = PackageMaster::findOrFail($id);
        $packagemaster->update($request->all());

        return response()->json($packagemaster, 200);
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
        //PackageMaster::destroy($id);

        return response()->json(null, 204);
    }
}
