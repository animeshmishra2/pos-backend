<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\CounterTransaction;
use Illuminate\Http\Request;

class CounterTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $countertransaction = CounterTransaction::latest()->paginate(25);

        return $countertransaction;
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
        
        $countertransaction = CounterTransaction::create($request->all());

        return response()->json($countertransaction, 201);
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
        $countertransaction = CounterTransaction::findOrFail($id);

        return $countertransaction;
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
        
        $countertransaction = CounterTransaction::findOrFail($id);
        $countertransaction->update($request->all());

        return response()->json($countertransaction, 200);
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
        CounterTransaction::destroy($id);

        return response()->json(null, 204);
    }
}
