<?php

namespace App\Http\Controllers;

use App\Imports\ImportProds;
use App\Imports\ImportBarcode;
use App\Imports\ImportBrand;
use App\Imports\ImportExcel;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Models\ExcelProds;
use App\Models\ExcelProdds;
use App\Models\BarcodeMaster;
use App\Models\BranddMaster;
use App\Models\SubCategory;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class FileController extends Controller
{
    public function importView()
    {
        return view('file');
    }
    
     public function importProductView()
    {
        return view('excelImport');
    }
    
    public function barcodeImport(Request $r)
        {
              $rr =Excel::import(
            new ImportBarcode,
            $r->file('file')->store('files')
        );
        // dd($rr);die;
        echo ("Products Imported.");
          return redirect()->back()->with("Products Imported.");
        }
        
          public function brandImport(Request $r)
        {
              $rr =Excel::import(
            new ImportBrand,
            $r->file('file')->store('files')
        );
        // dd($rr);die;
        echo ("Products Imported.");
          return redirect()->back()->with("Products Imported.");
        }
   
       public function productImport(Request $r)
        {
              $rr =Excel::import(
            new ImportExcel,
            $r->file('file')->store('files')
        );
        // dd($rr);die;
        echo ("Products Imported.");
          return redirect()->back()->with("Products Imported.");
        }
   
    public function import(Request $request)
    {
        $r = Excel::import(
            new ImportProds,
            $request->file('file')->store('files')
        );
        
        return redirect()->back()->with(("Products Imported."));
    }
    public function syncCategory(Request $request)
    {
        $res = DB::select('select distinct(category) FROM excel_prods;');
        foreach ($res as $cat) {
            $r = [
                'name' =>  $cat->category,
                'image' =>  'no-name.png',
                'cat_icon' =>  'no-name.png',
                'description' =>  '',
                'category_for' => 1,
                'created_by' => -1,
                'updated_by' => -1,
                'status' => 1
            ];
            Category::create($r);
        }
    }
    public function syncSubCategory(Request $request)
    {
        $res = DB::select('select  distinct(sub_category), category FROM excel_prods;');
        foreach ($res as $cat) {
            echo "select  idcategory FROM category WHERE name='" . $cat->category . "';<br/>";
            $mainCat = Category::where('name', $cat->category )->get();
            echo "INSERT INTO `sub_category` 
            (`idcategory`, `name`, `image`, `cat_icon`, `description`, `category_for`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES 
            ('" . $mainCat[0]->idcategory . "', '" . $cat->sub_category . "', 'no-image.png', 'no-image.png', '', '1', '2022-12-31 23:41:48', '-1', '2022-12-31 23:41:48', '-1');
            <br/><br/>";
            DB::insert('INSERT INTO `sub_category` 
            (`idcategory`, `name`, `image`, `cat_icon`, `description`, `category_for`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES 
            ("' . $mainCat[0]->idcategory . '", "' . $cat->sub_category . '", "no-image.png", "no-image.png", "", 1, "2022-12-31 23:41:48", -1, "2022-12-31 23:41:48", -1);
            ');

            $r = [
                'idcategory' =>  $mainCat[0]->idcategory,
                'name' =>  $cat->sub_category,
                'image' =>  'no-name.png',
                'cat_icon' =>  'no-name.png',
                'description' =>  '',
                'category_for' => 1,
                'created_by' => -1,
                'status' => 1
            ];
            SubCategory::create($r);
        }
    }

    public function syncSubSubCategory(Request $request)
    {
        $res = DB::select("SELECT DISTINCT
        (ep.sub_sub_category),
        ep.category,
        ep.sub_category,
        sc.idsub_category,
        c.idcategory
    FROM
        excel_prods ep
            LEFT JOIN
        sub_category sc ON sc.name = ep.sub_category
            LEFT JOIN
        category c ON c.name = ep.category
    WHERE
        sub_sub_category != '';");

        foreach ($res as $cat) {
            DB::insert('INSERT INTO `sub_sub_category` 
            (`idcategory`, `idsub_category`, `name`, `image`, `description`, `category_for`, `created_at`, `created_by`, `updated_at`, `updated_by`, `status`) VALUES 
            ("' . (($cat->idcategory == "") ? 0 : $cat->idcategory) . '", "' . (($cat->idsub_category == "") ? 0 : $cat->idsub_category) . '", "' . $cat->sub_sub_category . '", "noname.png", "", "1", "2022-12-20 23:41:48", "-1", "2022-12-20 23:41:48", "-1", "1");
            ');
        }
    }
    public function syncBrands(Request $request)
    {
        $res = DB::select('select distinct(brand_name) FROM excel_prods;');
        foreach ($res as $cat) {
            DB::insert('INSERT INTO `brands` 
            (`name`, `logo`, `created_by`, `created_at`, `updated_by`, `updated_at`, `status`) VALUES 
            ("' . $cat->brand_name . '", "no-image.png", "-1", "2022-12-20 23:41:48", "-1", "2022-12-20 23:41:48", "1");
            ');
        }
    }
}
