<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $user = auth()->guard('api')->user();
        $ven = Brand::orderBy('name', 'ASC')->get();
        return response()->json(["statusCode" => 0, "message" => "Success", "data" => $ven], 200);
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

            $r = [
                'name' =>  $req->name,
                'logo' =>  'no-name.png',
                'created_by' => $user->id,
                'status' => 1
            ];
            Brand::create($r);
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
        $brand = Brand::findOrFail($id);

        return $brand;
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
            $user = auth()->guard('api')->user();

            $package = Brand::findOrFail($id);
            $package->update($request->all());
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
        Brand::destroy($id);

        return response()->json(null, 204);
    }
}
