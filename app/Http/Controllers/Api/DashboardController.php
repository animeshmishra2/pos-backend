<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\SubCategory;
use App\Models\SubSubCategory;

class DashboardController extends Controller
{
    public function getDashboard($idstore_warehouse)
    {
        try {
            $brands = Brand::select('idbrand', 'name', 'logo AS image')->where('status', 1)->orderBy('name', 'ASC')->get();
            $category = Category::select('idcategory', 'name', 'image', 'cat_icon', 'description')->where('status', '1')->orderBy('name', 'ASC')->get();
            $carRet = [];

            $catImages = [];
            $catImages[] = ["id" => 1, "image" => "https://www.allwinmedico.in/ggb-api/resources/images/banners/category-banner/1.jpg"];
            $catImages[] = ["id" => 2, "image" => "https://www.allwinmedico.in/ggb-api/resources/images/banners/category-banner/2.jpg"];
            $catImages[] = ["id" => 3, "image" => "https://www.allwinmedico.in/ggb-api/resources/images/banners/category-banner/3.jpg"];

            foreach ($category as $cat) {
                $subCategory = SubCategory::select('idsub_category', 'name', 'image', 'cat_icon', 'description')->where('status', '1')->where('idcategory', $cat->idcategory)->orderBy('name', 'ASC')->get();
                $subCats = [];
                $cat['images'] = $catImages;
                foreach ($subCategory as $scat) {
                    $scat['sub_sub_cat'] = SubSubCategory::select('idsub_sub_category', 'name', 'image', 'description')->where('status', '1')->where('idsub_category', $scat->idsub_category)->orderBy('name', 'ASC')->get();
                    $subCats[] = $scat;
                    $scat['images'] = $catImages;
                }
                $cat['sub_cat'] = $subCats;
                $carRet[] = $cat;
            }

            $productQuery = Helper::prepareProductQuery();
            $productQuery->where('inventory.listing_type', 'day_deal');
            $res = $productQuery->where('inventory.idstore_warehouse', $idstore_warehouse)
                ->limit(20)
                ->get();
            $day_deal = Helper::getBatchesAndMemberPrices($res, $idstore_warehouse);

            $productQuery = Helper::prepareProductQuery();
            $productQuery->where('inventory.listing_type', 'popular');
            $res = $productQuery->where('inventory.idstore_warehouse', $idstore_warehouse)
                ->limit(20)
                ->get();
            $popular = Helper::getBatchesAndMemberPrices($res, $idstore_warehouse);

            $productQuery = Helper::prepareProductQuery();
            $productQuery->where('inventory.listing_type', 'frequent');
            $res = $productQuery->where('inventory.idstore_warehouse', $idstore_warehouse)
                ->limit(20)
                ->get();
            $frequent = Helper::getBatchesAndMemberPrices($res, $idstore_warehouse);

            $productQuery = Helper::prepareProductQuery();
            $productQuery->where('inventory.listing_type', 'new');
            $res = $productQuery->where('inventory.idstore_warehouse', $idstore_warehouse)
                ->limit(20)
                ->get();
            $new = Helper::getBatchesAndMemberPrices($res, $idstore_warehouse);

            $mainBanner = [];
            $mainBanner[] = ["id" => 1, "image" => "https://www.allwinmedico.in/ggb-api/resources/images/banners/main-banner/1.jpg"];
            $mainBanner[] = ["id" => 2, "image" => "https://www.allwinmedico.in/ggb-api/resources/images/banners/main-banner/2.jpg"];
            $mainBanner[] = ["id" => 3, "image" => "https://www.allwinmedico.in/ggb-api/resources/images/banners/main-banner/3.jpg"];
            $mainBanner[] = ["id" => 4, "image" => "https://www.allwinmedico.in/ggb-api/resources/images/banners/main-banner/4.jpg"];

            $offerBanner = [];
            $offerBanner[] = ["id" => 1, "image" => "https://www.allwinmedico.in/ggb-api/resources/images/banners/offer-banners/1.jpg"];
            $offerBanner[] = ["id" => 2, "image" => "https://www.allwinmedico.in/ggb-api/resources/images/banners/offer-banners/2.jpg"];
            $offerBanner[] = ["id" => 3, "image" => "https://www.allwinmedico.in/ggb-api/resources/images/banners/offer-banners/3.jpg"];

            $res = [
                'brandList' => $brands,
                'menuList' => $carRet,
                'banners' => [
                    'main' => $mainBanner,
                    'offer' => $offerBanner
                ],
                'products' => [
                    'dealOfDay' => $day_deal,
                    'mostPopular' => $popular,
                    'frequentBought' => $frequent,
                    'newArrival' => $new
                ]
            ];


            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $res], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
    
    
   public function getNewDashboard($idstore_warehouse)
    {
        try {
            $brands = Brand::select('idbrand', 'name', 'logo AS image')->where('status', 1)->orderBy('name', 'ASC')->take(12)->get();
            $category_m = Category::select('idcategory', 'name', 'image', 'cat_icon', 'description')->where('status', '1')->orderBy('name', 'ASC')->get();
            $productQuery = Helper::prepareProductQuery();
            $productQuery->where('inventory.listing_type', 'day_deal');
            $res = $productQuery->where('inventory.idstore_warehouse', $idstore_warehouse)
                ->limit(20)
                ->get();
            $day_deal = Helper::getNewBatchesAndMemberPrices($res, $idstore_warehouse);

            $productQuery = Helper::prepareProductQuery();
            $productQuery->where('inventory.listing_type', 'popular');
            $res = $productQuery->where('inventory.idstore_warehouse', $idstore_warehouse)
                ->limit(20)
                ->get();
            $popular = Helper::getNewBatchesAndMemberPrices($res, $idstore_warehouse);

            $productQuery = Helper::prepareProductQuery();
            $productQuery->where('inventory.listing_type', 'frequent');
            $res = $productQuery->where('inventory.idstore_warehouse', $idstore_warehouse)
                ->limit(20)
                ->get();
            $frequent = Helper::getNewBatchesAndMemberPrices($res, $idstore_warehouse);

            $productQuery = Helper::prepareProductQuery();
            $productQuery->where('inventory.listing_type', 'new');
            $res = $productQuery->where('inventory.idstore_warehouse', $idstore_warehouse)
                ->limit(20)
                ->get();
            $new = Helper::getNewBatchesAndMemberPrices($res, $idstore_warehouse);

            $mainBanner = [];
            $mainBanner[] = ["id" => 1, "image" => "http://ghargharbazaar.com/ggb-api/public/banners/main-banner/top_banner.png", "type"=>1, "sel_id"=>23];

            $offerBanner = [];
            $offerBanner[] = ["id" => 1, "image" => "http://ghargharbazaar.com/ggb-api/public/banners/offer-banners/offer_banner_1.png", "type"=>2, "sel_id"=>11];
            $offerBanner[] = ["id" => 2, "image" => "http://ghargharbazaar.com/ggb-api/public/banners/offer-banners/offer_banner_1.png", "type"=>2, "sel_id"=>17];
            $offerBanner[] = ["id" => 3, "image" => "http://ghargharbazaar.com/ggb-api/public/banners/offer-banners/offer_banner_1.png", "type"=>2, "sel_id"=>19];
            
            $categories = DB::table('category')
            ->select('category.idcategory', 'category.name AS category_name')
            ->selectRaw('COUNT(sub_category.idcategory) as sub_count')
            ->join('sub_category', 'category.idcategory', '=', 'sub_category.idcategory')
            ->where('category.status', '1')
            ->where('sub_category.status', '1')
            ->groupBy('category.idcategory')
            ->havingRaw('sub_count >= 5')
            ->limit(4)
            ->get();

            $result = [];
            
             foreach ($categories as $category) {
                $subcategories = DB::table('sub_category')
                ->select('idsub_category', 'name', 'image AS sub_cat_img', 'cat_icon AS subcat_icon')
                ->where('status', '1')
                ->where('idcategory', $category->idcategory)
                ->orderBy('name', 'ASC')
                ->take(10)
                ->get();

                $categoryData = [
                    'idcategory' => $category->idcategory,
                    'category_name' => $category->category_name,
                    'subcat' => $subcategories->toArray()
                ];

                $result[] = $categoryData;
            }
            $res = [
                'brandList' => $brands,
                'menuList' => $category_m,
                'banners' => [
                    'main' => $mainBanner,
                    'offer' => $offerBanner
                ],
                'extra_cats' =>$result,
                'products' => [
                    'dealOfDay' => $day_deal,
                    'mostPopular' => $popular,
                    'frequentBought' => $frequent,
                    'newArrival' => $new
                ]
            ];


            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $res], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
    
}
