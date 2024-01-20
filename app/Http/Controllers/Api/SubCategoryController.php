<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\SubCategory;
use App\Models\SubSubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $rec = DB::table('sub_category')
            ->leftJoin('category', 'sub_category.idcategory', '=', 'category.idcategory')
            ->select(
                'category.name AS cat_name',
                'sub_category.*'
            )
            ->where('category.status', '1')
            ->where('sub_category.status', '1')
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
                'name' =>  $req->name,
                'image' =>  'no-name.png',
                'cat_icon' =>  'no-name.png',
                'description' =>  $req->description,
                'category_for' => 1,
                'created_by' => $user->id,
                'status' => $req->status
            ];
            SubCategory::create($r);
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
    public function getSubCatByCatId($catId)
    {
        try {
            $rec = DB::table('sub_category')
            ->leftJoin('category', 'sub_category.idcategory', '=', 'category.idcategory')
            ->select(
                'category.name AS cat_name',
                'sub_category.*'
            )
            ->where('category.idcategory', $catId)
            ->where('sub_category.status', '1')
            ->orderBy('name', 'ASC')
            ->get();
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $rec], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
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
            $package = SubCategory::findOrFail($id);
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
        //SubCategory::destroy($id);

        return false;
    }

    public function getSubCategoryHierarchy($id)
    {
        $url = "https://allwinmedico.in/ggb-api/resources/images/";
        try {
            $subCategory = SubCategory::select('idsub_category','name', 'image', 'cat_icon', 'description')->where('status', '1')->where('idsub_category', $id)->get();
            $subCats = [];
            $catImages = [];
            $catImages[] = ["id" => 1, "image" => $url."banners/category-banner/1.jpg"];
            $catImages[] = ["id" => 2, "image" => $url."banners/category-banner/2.jpg"];
            $catImages[] = ["id" => 3, "image" => $url."banners/category-banner/3.jpg"];
            
            foreach($subCategory as $scat){
                $scat['images'] = $catImages;
                $scat['sub_sub_cat'] = SubSubCategory::select('idsub_sub_category','name', 'image', 'description')->where('status', '1')->where('idsub_category', $scat->idsub_category)->orderBy('name', 'ASC')->get();
                $subCats[] = $scat;
            }
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $subCats], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => $subCategory, "err" => $e->getMessage()], 200);
        }
    }
}
