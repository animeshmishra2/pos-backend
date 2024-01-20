<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\ProductBatch;
use Illuminate\Http\Request;

class ProductBatchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $productbatch = ProductBatch::latest()->paginate(25);

        return $productbatch;
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
        
        $productbatch = ProductBatch::create($request->all());

        return response()->json($productbatch, 201);
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
        $productbatch = ProductBatch::findOrFail($id);

        return $productbatch;
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
        
        $productbatch = ProductBatch::findOrFail($id);
        $productbatch->update($request->all());

        return response()->json($productbatch, 200);
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
        ProductBatch::destroy($id);

        return response()->json(null, 204);
    }
}
