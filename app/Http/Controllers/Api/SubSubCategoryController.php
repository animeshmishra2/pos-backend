<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Exception;

use App\Models\SubSubCategory;
use Illuminate\Http\Request;

class SubSubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $rec = DB::table('sub_sub_category')
            ->leftJoin('sub_category', 'sub_category.idsub_category', '=', 'sub_sub_category.idsub_category')
            ->leftJoin('category', 'sub_sub_category.idcategory', '=', 'category.idcategory')
            ->select(
                'category.name AS cat_name',
                'sub_category.name AS sub_cat_name',
                'sub_sub_category.*'
            )
            ->where('category.status', '1')
            ->where('sub_category.status', '1')
            ->where('sub_sub_category.status', '1')
            ->orderBy('name', 'ASC')
            ->get();
        
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $rec], 200);
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
                'idcategory' =>  $req->idcategory,
                'idsub_category' =>  $req->idsub_category,
                'name' =>  $req->name,
                'image' =>  'no-name.png',
                'description' =>  $req->description,
                'category_for' => 1,
                'created_by' => $user->id,
                'status' => 1
            ];
            SubSubCategory::create($r);
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
        $subsubcategory = SubSubCategory::findOrFail($id);

        return $subsubcategory;
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
            $package = SubSubCategory::findOrFail($id);
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
        SubSubCategory::destroy($id);

        return response()->json(null, 204);
    }

    public function getSSCatBySCatId($scatId)
    {
        try {
            $rec = DB::table('sub_sub_category')
            ->leftJoin('sub_category', 'sub_category.idsub_category', '=', 'sub_sub_category.idsub_category')
            ->select(
                'sub_category.name AS scat_name',
                'sub_sub_category.*'
            )
            ->where('sub_sub_category.idsub_category', $scatId)
            ->orderBy('name', 'ASC')
            ->get();
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $rec], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
}
