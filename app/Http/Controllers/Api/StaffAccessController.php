<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\StaffAccess;
use Illuminate\Http\Request;

class StaffAccessController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $staffaccess = StaffAccess::latest()->paginate(25);

        return $staffaccess;
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
        
        $staffaccess = StaffAccess::create($request->all());

        return response()->json($staffaccess, 201);
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
        $staffaccess = StaffAccess::findOrFail($id);

        return $staffaccess;
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
        
        $staffaccess = StaffAccess::findOrFail($id);
        $staffaccess->update($request->all());

        return response()->json($staffaccess, 200);
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
        StaffAccess::destroy($id);

        return response()->json(null, 204);
    }

 
}
