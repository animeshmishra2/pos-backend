<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\PackageProductList;
use Illuminate\Http\Request;

class PackageProductListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $packageproductlist = PackageProductList::latest()->paginate(25);

        return $packageproductlist;
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
        
        $packageproductlist = PackageProductList::create($request->all());

        return response()->json($packageproductlist, 201);
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
        $packageproductlist = PackageProductList::findOrFail($id);

        return $packageproductlist;
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
        
        $packageproductlist = PackageProductList::findOrFail($id);
        $packageproductlist->update($request->all());

        return response()->json($packageproductlist, 200);
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
        PackageProductList::destroy($id);

        return response()->json(null, 204);
    }
}
