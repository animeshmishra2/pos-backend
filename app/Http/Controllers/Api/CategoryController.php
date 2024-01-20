<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Exception;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\SubCategory;
use App\Models\SubSubCategory;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $category = Category::where('status', '1')->orderBy('name', 'ASC')->get();

        return response()->json(["statusCode" => 0, "message" => "Success", "data" => $category], 200);
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
                'image' =>  'no-name.png',
                'cat_icon' =>  'no-name.png',
                'description' =>  $req->description,
                'has_return_rule' =>  $req->has_return_rule,
                'return_type' =>  $req->ret_type,
                'return_duration' =>  $req->ret_duration,
                'category_for' => 1,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'status' => $req->status
            ];
            Category::create($r);
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
        $category = Category::findOrFail($id);

        return $category;
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
            $package = Category::findOrFail($id);
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
        Category::destroy($id);

        return response()->json(null, 204);
    }

    public function getCategoryHierarchy($id)
    {
        $url = "https://allwinmedico.in/ggb-api/resources/images/";
        try {
            $category = Category::select('idcategory','name', 'image', 'cat_icon', 'description')->where('idcategory', $id)->where('status', '1')->get();
            $carRet = [];
            $catImages = [];
            $catImages[] = ["id" => 1, "image" => $url."banners/category-banner/1.jpg"];
            $catImages[] = ["id" => 2, "image" => $url."banners/category-banner/2.jpg"];
            $catImages[] = ["id" => 3, "image" => $url."banners/category-banner/3.jpg"];
            foreach($category as $cat){
                $subCategory = SubCategory::select('idsub_category','name', 'image', 'cat_icon', 'description')->where('status', '1')->where('idcategory', $cat->idcategory)->orderBy('name', 'ASC')->get();
                $subCats = [];
                $cat['images'] = $catImages;
                foreach($subCategory as $scat){
                    $scat['sub_sub_cat'] = SubSubCategory::select('idsub_sub_category','name', 'image', 'description')->where('status', '1')->where('idsub_category', $scat->idsub_category)->orderBy('name', 'ASC')->get();
                    $scat['images'] = $catImages;
                    $subCats[] = $scat;
                }
                $cat['sub_cat'] = $subCats;
                $carRet[] = $cat;
            }
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $carRet], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
}
