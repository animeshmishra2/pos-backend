<?php // Code within app\Helpers\Helper.php

namespace App\Helpers;

use App\Models\ProductBatch;
use App\Models\ProductMaster;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use App\Models\OrderDetail;
use Illuminate\Support\Carbon;
class Helper
{
    
     public static function getBanners($banner_type='',$type='',$type_id='')
    {
        $bannerDetail = DB::table('banners');
        if($banner_type!=''){
            $bannerDetail->where('banner_type',$banner_type);
        }
        if($type!=''){
            $bannerDetail->where('type',$type);
        }
        if($type_id!=''){
            $bannerDetail->where('type_id',$type_id);
        }
        $bannerDetails = $bannerDetail->get();
        return $bannerDetails;
    }
    // public static function processDiscountOnOrder($request)
    // {
    //     $customerLoggedIn = (!!$request->contact) ? true : false;
    //     $membershipInstComission = 0;
    //     if($customerLoggedIn){
    //         $customer = Customer::where(['phone' => $request->contact])->first();
    //         $customer_data =
    //             DB::table('customer')
    //             ->join('customer_address', 'customer.idcustomer', '=', 'customer_address.idcustomer')
    //             ->join('membership_plan', 'customer.idmembership', '=', 'membership_plan.idmembership_plan')
    //             ->select(
    //                 'customer.idcustomer',
    //                 'customer.name',
    //                 'customer.idstore_warehouse',
    //                 'customer.phone',
    //                 'customer.email',
    //                 'customer.idmembership',
    //                 'customer.wallet_balance',
    //                 'customer.created_by',
    //                 'customer.status',
    //                 'customer_address.address',
    //                 'customer_address.pincode',
    //                 'customer_address.landmark',
    //                 'membership_plan.name as membership_type',
    //                 'membership_plan.instant_discount',
    //                 'membership_plan.commission'
    //             )
    //             ->where('customer.phone', $customer->contact)
    //             ->first();
    //             $membershipInstComission = ($customer_data &&
    //              $customer_data->instant_discount == 1 && 
    //              $customer_data->commission > 0) ? $customer_data->commission : 0;
    //     }
    //     else{
    //         $currentOpenMemPlan = DB::table('membership_plan')->where('idmembership_plan', 1);
    //         $membershipInstComission = ($currentOpenMemPlan &&
    //              $currentOpenMemPlan->instant_discount == 1 && 
    //              $currentOpenMemPlan->commission > 0) ? $currentOpenMemPlan->commission : 0;
    //     }

    //     $total = Helper::calculateTotalOnOrder($request->order_det);
    //     $discountToGiveAmt = 0;
    //     $discountToGivePer = 0;

    //     if($request->discountAmt > 0){
    //         $discountToGiveAmt = $request->discountAmt;
    //     }
    //     else if ($request->discountPer > 0){
    //         $discountToGiveAmt = $total['total'] * ($request->discountPer / 100);
    //     }
    //     else if (!!$request->activeNonGenPkg){
    //         $discountToGiveAmt = 0;
    //     }
    //     else if ($request->isAppliedDynFxDis){
    //         $discountToGiveAmt = $total['total'] * ($membershipInstComission / 100);
    //     }
    //     else if (!!$request->coupon){
    //         $discountToGiveAmt = 0;
    //     }
    //     $discountToGivePer = ($total['total'] == 0) ? 0 : $discountToGiveAmt * (100 / $total['total']);

    //     $total = Helper::calculateTotalOnOrder($request, $discountToGivePer);

    //     $instantDiscountPer = 0;
    //     $amountToWallet = 0;
    //     $instantDiscountPer = 0;
    //     $grandTotal = 0;

    //     if(isset($customer->idcustomer) && $customer->idcustomer > 0)
    //     {
    //         $customer_data =
    //             DB::table('customer')
    //             ->join('customer_address', 'customer.idcustomer', '=', 'customer_address.idcustomer')
    //             ->join('membership_plan', 'customer.idmembership', '=', 'membership_plan.idmembership_plan')
    //             ->select(
    //                 'customer.idcustomer',
    //                 'customer.name',
    //                 'customer.idstore_warehouse',
    //                 'customer.phone',
    //                 'customer.email',
    //                 'customer.idmembership',
    //                 'customer.wallet_balance',
    //                 'customer.created_by',
    //                 'customer.status',
    //                 'customer_address.address',
    //                 'customer_address.pincode',
    //                 'customer_address.landmark',
    //                 'membership_plan.name as membership_type',
    //                 'membership_plan.instant_discount',
    //                 'membership_plan.commission'
    //             )
    //             ->where('customer.idcustomer', $customer->idcustomer)
    //             ->first();

    //         if($customer_data->instant_discount == 1){
    //             //TODO Instant Discout     
    //             $instantDiscountPer = $customer_data->commission;             
    //         }
    //         if($customer_data->instant_discount == 0){
    //             $amountToWallet = ($grandTotal * $customer_data->commission) / 100;
    //         }
    //     }

    //     $grandTotal = $grandTotal;
    //     $totalInstDiscount = 0;
    //     if($instantDiscountPer > 0){
    //         $totalInstDiscount = ($grandTotal * $instantDiscountPer / 100);
    //         $grandTotal = $grandTotal - $totalInstDiscount;
    //     }
    // }

    // public static function calculateTotalOnOrder($orderDet, $instantDiscountPer = 0)
    // {
    //     $ordDet = [];
    //     $totalTaxPercent = 0;
    //     $totalTaxAmount = 0;
    //     $preTaxAmount = 0;
    //     $cgstPer = 0;
    //     $sgstPer = 0;
    //     $sgstAmtItem = 0;
    //     $cgstAmtItem = 0;
    //     $total = [
    //         'cgst' => 0,
    //         'sgst' => 0,
    //         'total' => 0,
    //         'productDiscount' => 0,
    //         'membershipDiscount' => 0,
    //         'couponDiscount' => 0,
    //         'customerDiscount' => 0,
    //         'grand' => 0,
    //         'totalQty' => 0,
    //     ];

    //     foreach ($orderDet as $prod) {
    //         $qty = $prod->qty;
    //         $idproduct_master = $prod->idproduct_master;
    //         $mrp = $prod->mrp;
    //         $selling_price = $prod->selling_price;
    //         $currItemMemDisc = 0;
    //         if($instantDiscountPer > 0){
    //             $currItemMemDisc = $selling_price * $instantDiscountPer / 100;
    //             $selling_price = $selling_price - $currItemMemDisc;
    //             $selling_price = $selling_price > 0 ? $selling_price : 0;
    //         }
    //         $idproduct_batch = $prod->detail->idproduct_batch;
    //         $idinventory = $prod->detail->idinventory;

    //         $totalTaxPercent = $prod->cgst + $prod->sgst;
    //         if ($totalTaxPercent > 0) {
    //             $preTaxAmount = $selling_price / (($totalTaxPercent + 100) / 100);
    //             $totalTaxAmount = $selling_price - $preTaxAmount;
    //             $cgstPer = $prod->cgst * 100 / $totalTaxPercent;
    //             $sgstPer = $prod->sgst * 100 / $totalTaxPercent;
    //             $sgstAmtItem = $totalTaxAmount * $sgstPer / 100;
    //             $cgstAmtItem = $totalTaxAmount * $cgstPer / 100;
    //         }

    //         $total['cgst'] += $cgstAmtItem * $qty;
    //         $total['sgst'] += $sgstAmtItem * $qty;
    //         $total['total'] += $mrp * $qty;
    //         $total['discount'] += ($mrp - $selling_price) * $qty;
    //         $total['grand'] += $selling_price * $qty;
    //         $total['totalQty'] += $qty;

    //         $ordDet[] = [
    //             'idproduct_master' => $idproduct_master,
    //             'idinventory' => $idinventory,
    //             'quantity' => $qty,
    //             'total_price' => $qty * $selling_price,
    //             'total_cgst' => $cgstAmtItem * $prod->qty,
    //             'total_sgst' => $sgstAmtItem * $prod->qty,
    //             'unit_mrp' => $prod->mrp,
    //             'unit_selling_price' => $selling_price,
    //             'discount' => ($prod->mrp - $selling_price),
    //         ];
    //     }
    //     return $total;
    // }

    public static function smssend($phone, $message)
    {
        $messages = array(
            // Put parameters here such as sender, force or test
            'sender' => "GGBPLT",
            'messages' => array(
                array(
                    'number' => $phone,
                    'text' => rawurlencode($message)
                )
            )
        );

        $data = array(
            'apikey' => 'NzkzMDQ1NmI2MzdhMzg0YzQ5NTYzNTUzNWE0YTY0NmQ=',
            'data' => json_encode($messages)
        );
        // Send the POST request with cURL
        $ch = curl_init('https://api.textlocal.in/bulk_json/');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        error_log("==================>" . print_r($response, true));
        return json_decode($response);
    }
    
    

    public static function getTemplate($data, $template)
    {
        //$common_template=$template;
        foreach ($data as $key => $val) {
            $template = str_replace('%' . $key . '%', $val, $template);
        }
        return  $template;
    }

    public static function addWalletAmount($userId, $refId, $amount, $description) {
        WalletTransaction::create([
            'idcustomer' => $userId,
            'type' => 'Referral',
            'ref_id' => $refId,
            'amount' => $amount,
            'transaction_type' => 'CR',
            'idmembership_plan' => 0,
            'remark' => $description,
            'created_by' => -1,
            'updated_by' => -1,
            'status' => 1
        ]);
      
        DB::table('wallet_balance')
            ->where('idcustomer', $userId)
            ->where('idmembership_plan',  0)
            ->update([
                'current_amount' => DB::raw('current_amount + ' . $amount),
                'total_incurred' => DB::raw('total_incurred + ' . $amount)
            ]);
    
    }

    public static function getBatchesAndMemberPrices($productList, $idStore) {
        $allProds = [];
            $mplans = DB::table('membership_plan')
                ->where('status', 1)
                ->where('instant_discount', 0)
                ->get();
            foreach ($productList as $pro) {
                $pro->sellingPriceForInstantDisc = $pro->selling_price - ($pro->selling_price * ($pro->instant_discount_percent) / 100);
                $pro->batches = ProductBatch::where('idstore_warehouse', $idStore)
                    ->where('idproduct_master', $pro->idproduct_master)
                    ->where('status', 1)
                    ->get();
                $pro->selected_batch = null;
                if (count($pro->batches) == 1) {
                    $pro->selected_batch = $pro->batches[0];
                }
                $disc = [];
                foreach ($mplans as $membership) {
                    $curDesc = [];
                    $curDesc['idmembership_plan'] = $membership->idmembership_plan;
                    $curDesc['name'] = $membership->name;
                    $curDesc['commission'] = $membership->commission;
                    $curDesc['selling_price'] = $pro->selling_price - ($pro->selling_price * ($membership->commission) / 100);
                    $disc[] = $curDesc;
                }
                $pro->member_price = [];
                $allProds[] = $pro;
            }
            return $allProds;
    }
    
     public static function getBatchesAndMemberPricesWithRelatedProducts($productList, $idStore) {
        $allProds = [];
            $mplans = DB::table('membership_plan')
                ->where('status', 1)
                ->where('instant_discount', 0)
                ->get();
            foreach ($productList as $pro) {
                $pro->sellingPriceForInstantDisc = $pro->selling_price - ($pro->selling_price * ($pro->instant_discount_percent) / 100);
                $pro->batches = ProductBatch::where('idstore_warehouse', $idStore)
                    ->where('idproduct_master', $pro->idproduct_master)
                    ->where('status', 1)
                    ->get();
                   
                $pro->selected_batch = null;
                if (count($pro->batches) == 1) {
                    $pro->selected_batch = $pro->batches[0];
                }
                $disc = [];
                 //brand wise
                    $pro->brand_wise = ProductMaster::leftJoin('sub_sub_category', 'sub_sub_category.idsub_sub_category', '=', 'product_master.idsub_sub_category')
                ->leftJoin('sub_category', 'sub_category.idsub_category', '=', 'product_master.idsub_category')
                ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
                ->select(
                    // 'product_master.idbrand',
                    'brands.name AS brand',
                    'product_master.idproduct_master',
                    // 'product_master.idcategory',
                    'category.name AS category',
                    // 'product_master.idsub_category',
                    'sub_category.name AS scategory',
                    // 'product_master.idsub_sub_category',
                    'sub_sub_category.name AS sscategory',
                    'product_master.name AS prod_name',
                    'product_master.description',
                    'product_master.barcode',
                    'product_master.hsn',
                    'product_master.sgst',
                    'product_master.cgst',
                    'product_master.status',
                     'product_master.idbrand',
                     'product_master.idcategory',
                    'inventory.quantity',
                    'inventory.idinventory',
                    'inventory.selling_price',
                    'inventory.mrp',
                    'inventory.discount',
                     'inventory.product',
                      'inventory.copartner',
                       'inventory.land',
                    'inventory.instant_discount_percent',
                    'inventory.listing_type'
                )
            ->where('inventory.idstore_warehouse', $idStore)
            ->where('product_master.idbrand', $pro->idbrand)
                    ->where('product_master.status', 1)
                    ->limit(10)
                    ->get();
                    //category wise
                     $pro->category_wise = ProductMaster::leftJoin('sub_sub_category', 'sub_sub_category.idsub_sub_category', '=', 'product_master.idsub_sub_category')
                ->leftJoin('sub_category', 'sub_category.idsub_category', '=', 'product_master.idsub_category')
                ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
                ->select(
                    // 'product_master.idbrand',
                    'brands.name AS brand',
                    'product_master.idproduct_master',
                    // 'product_master.idcategory',
                    'category.name AS category',
                    // 'product_master.idsub_category',
                    'sub_category.name AS scategory',
                    // 'product_master.idsub_sub_category',
                    'sub_sub_category.name AS sscategory',
                    'product_master.name AS prod_name',
                    'product_master.description',
                    'product_master.barcode',
                    'product_master.hsn',
                    'product_master.sgst',
                    'product_master.cgst',
                    'product_master.status',
                     'product_master.idbrand',
                     'product_master.idcategory',
                    'inventory.quantity',
                    'inventory.idinventory',
                    'inventory.selling_price',
                    'inventory.mrp',
                    'inventory.discount',
                     'inventory.product',
                      'inventory.copartner',
                       'inventory.land',
                    'inventory.instant_discount_percent',
                    'inventory.listing_type'
                )
            ->where('inventory.idstore_warehouse', $idStore)
            ->where('product_master.idcategory', $pro->idcategory)
               ->where('product_master.status', 1)
                     ->limit(10)
                    ->get();
                foreach ($mplans as $membership) {
                    $curDesc = [];
                    $curDesc['idmembership_plan'] = $membership->idmembership_plan;
                    $curDesc['name'] = $membership->name;
                    $curDesc['commission'] = $membership->commission;
                    $curDesc['selling_price'] = $pro->selling_price - ($pro->selling_price * ($membership->commission) / 100);
                    $disc[] = $curDesc;
                }
                $pro->member_price = [];
                $allProds[] = $pro;
            }
            return $allProds;
    }



        public static function getNewBatchesAndMemberPrices($productList, $idStore) {
            $allProds = [];
            foreach ($productList as $pro) {
                $pro->batches = ProductBatch::where('idstore_warehouse', $idStore)
                    ->where('idproduct_master', $pro->idproduct_master)
                    ->where('status', 1)
                    ->get();
                $pro->selected_batch = null;
                if (count($pro->batches) == 1) {
                    $pro->selected_batch = $pro->batches[0];
                }
                $allProds[] = $pro;
            }
            return $allProds;
        }

    
    public static function prepareProductQuery() {
        $productmaster = DB::table('product_master')
                ->leftJoin('sub_sub_category', 'sub_sub_category.idsub_sub_category', '=', 'product_master.idsub_sub_category')
                ->leftJoin('sub_category', 'sub_category.idsub_category', '=', 'product_master.idsub_category')
                ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
                ->select(
                    'product_master.idbrand',
                    'brands.name AS brand',
                    'product_master.idproduct_master',
                    'product_master.idcategory',
                    'category.name AS category',
                    'product_master.idsub_category',
                    'sub_category.name AS scategory',
                    'product_master.idsub_sub_category',
                    'sub_sub_category.name AS sscategory',
                    'product_master.name AS prod_name',
                    'product_master.description',
                    'product_master.barcode',
                    'product_master.hsn',
                    'product_master.sgst',
                    'product_master.cgst',
                    'product_master.status',
                    'inventory.quantity',
                    'inventory.idinventory',
                    'inventory.selling_price',
                    'inventory.mrp',
                    'inventory.discount',
                    'inventory.product',
                    'inventory.copartner',
                    'inventory.land',
                    'inventory.instant_discount_percent',
                    'inventory.listing_type',
                    'inventory.listing_type AS origListType'
                );
        return $productmaster;
    }
    
    
    
    public static function getFrequentProducts()
    {
          DB::statement("SET SESSION sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");

        // Get top 10 unique products from order_detail
        $topProducts = OrderDetail::select('idproduct_master')
            ->distinct()
            ->take(10)
            ->pluck('idproduct_master');

        // Retrieve products details based on the top 10 unique products
        $products = ProductMaster::select(
                'product_master.idbrand',
                'brands.name AS brand',
                'product_master.idproduct_master',
                'product_master.idcategory',
                'category.name AS category',
                'product_master.idsub_category',
                'sub_category.name AS scategory',
                'product_master.idsub_sub_category',
                'sub_sub_category.name AS sscategory',
                'product_master.name AS prod_name',
                'product_master.description',
                'product_master.barcode',
                'product_master.hsn',
                'product_master.sgst',
                'product_master.cgst',
                'product_master.status',
                'inventory.quantity',
                'inventory.idinventory',
                'inventory.selling_price',
                'inventory.mrp',
                'inventory.discount',
                'inventory.product',
                'inventory.copartner',
                'inventory.land',
                'inventory.instant_discount_percent',
                'inventory.listing_type',
                'inventory.listing_type AS origListType',
                DB::raw('SUM(order_detail.quantity) AS total_sold')
            )
            ->join('order_detail', 'order_detail.idproduct_master', '=', 'product_master.idproduct_master')
            ->join('brands', 'product_master.idbrand', '=', 'brands.idbrand')
            ->join('category', 'product_master.idcategory', '=', 'category.idcategory')
            ->join('sub_category', 'product_master.idsub_category', '=', 'sub_category.idsub_category')
            ->join('sub_sub_category', 'product_master.idsub_sub_category', '=', 'sub_sub_category.idsub_sub_category')
            ->join('inventory', 'product_master.idproduct_master', '=', 'inventory.idproduct_master')
            ->whereIn('order_detail.idproduct_master', $topProducts)
            ->groupBy('product_master.idproduct_master')
            ->orderByDesc('total_sold')
            ->get();
    return $products;
    }
    
 public static function gstr1_report($year, $month, $last_six_month = 0)
    {
        $data_without_date = DB::table('order_detail')
                    ->leftJoin('customer_order', 'customer_order.idcustomer_order', '=', 'order_detail.idcustomer_order')
                    ->select('customer_order.idcustomer_order', 'order_detail.quantity', 'order_detail.total_price', 'order_detail.total_cgst', 'order_detail.total_sgst', 'customer_order.created_at')
                    ->where('order_detail.total_cgst', '<>', 0)
                    ->where('order_detail.total_sgst', '<>', 0);
        
        if(!empty($last_six_month)) {
            $data_without_date->whereBetween('customer_order.created_at', [
                Carbon::now()->subMonths(6)->startOfDay(),
                Carbon::now()->endOfDay()
            ]);
        } else {
            $data_without_date->whereYear('customer_order.created_at', $year);
            $data_without_date->whereMonth('customer_order.created_at', $month);
        }
        $get_data = $data_without_date->get();
        
         $get_data_nil_reted_data = DB::table('order_detail')
                                ->leftJoin('customer_order', 'customer_order.idcustomer_order', '=', 'order_detail.idcustomer_order')
                                ->select('customer_order.idcustomer_order', 'order_detail.quantity', 'order_detail.total_price', 'order_detail.total_cgst', 'order_detail.total_sgst', 'customer_order.created_at')
                                ->where('order_detail.total_cgst', '=', 0)
                                ->where('order_detail.total_sgst', '=', 0);
                                
        if(!empty($last_six_month)) {
            $get_data_nil_reted_data->whereBetween('customer_order.created_at', [
                Carbon::now()->subMonths(6)->startOfDay(),
                Carbon::now()->endOfDay()
            ]);
        } else {
            $get_data_nil_reted_data->whereYear('customer_order.created_at', $year);
            $get_data_nil_reted_data->whereMonth('customer_order.created_at', $month);
        }
        $get_data_nil_reted = $get_data_nil_reted_data->get();                       

        $data = [];
        $data['period']['start_date'] = empty($last_six_month) ? Carbon::create($year, $month)->startOfMonth()->format('d/m/Y') : Carbon::now()->subMonths(6)->startOfDay()->format('d/m/Y');
        $data['period']['end_date'] = empty($last_six_month) ? Carbon::create($year, $month)->lastOfMonth()->format('d/m/Y') : Carbon::now()->endOfDay()->format('d/m/Y');   
        
        $data['business_to_bisiness'] = [
            'count' => 0,
            'taxable' => 0,
            'CGST' =>0,
            'SGST' => 0,
            'IGST' => 0,
            'cess' => 0,
            'TotalGST' => 0,
            'InvoiceAmount' => 0
        ];

        $gross_counter = 0;
        $gross_amount = 0;
        $gross_cgst = 0;
        $gross_sgst = 0;
        $gross_igst = 0;
        $gross_cess = 0;            
        foreach($get_data as $order) {
            $gross_counter += $order->quantity;
            $gross_amount += $order->total_price;
            $gross_cgst += $order->total_cgst;
            $gross_sgst += $order->total_sgst;
        }
        $total_gst = $gross_cgst + $gross_sgst;
        $invoice_amount = $gross_amount + $total_gst;

        $data['business_to_customer_small'] = [
            'count' => ($total_gst < 250000) ? $gross_counter : 0,
            'taxable' => ($total_gst < 250000) ? round($gross_amount, 4) : 0,
            'CGST' => ($total_gst < 250000) ? round($gross_cgst,4) : 0,
            'SGST' => ($total_gst < 250000) ? round($gross_sgst, 4) : 0,
            'IGST' => ($total_gst < 250000) ? $gross_igst : 0,
            'cess' => ($total_gst < 250000) ? $gross_cess : 0,
            'TotalGST' => ($total_gst < 250000) ? round($total_gst, 4) : 0,
            'InvoiceAmount' => ($total_gst < 250000) ? round($invoice_amount, 4) : 0,
        ]; 

        $data['business_to_customer_large'] = [
            'count' => ($total_gst > 250000) ? $gross_counter : 0,
            'taxable' => ($total_gst > 250000) ? round($gross_amount, 4) : 0,
            'CGST' => ($total_gst > 250000) ? round($gross_cgst,4) : 0,
            'SGST' => ($total_gst > 250000) ? round($gross_sgst, 4) : 0,
            'IGST' => ($total_gst > 250000) ? $gross_igst : 0,
            'cess' => ($total_gst > 250000) ? $gross_cess : 0,
            'TotalGST' => ($total_gst > 250000) ? round($total_gst, 4) : 0,
            'InvoiceAmount' => ($total_gst > 250000) ? round($invoice_amount, 4) : 0,
        ]; 
        
        $gross__nil_reted_counter = 0;
        $gross_nil_reted_amount = 0;
        foreach($get_data_nil_reted as $order) {
            $gross__nil_reted_counter += $order->quantity;
            $gross_nil_reted_amount += $order->total_price;
        }

        $data['nil_rated'] = [
            'count' => $gross__nil_reted_counter,
            'taxable' => round($gross_nil_reted_amount, 4),
            'CGST' =>0,
            'SGST' => 0,
            'IGST' => 0,
            'cess' => 0,
            'TotalGST' => 0,
            'InvoiceAmount' => round($gross_nil_reted_amount, 4),
        ]; 
        $data['exempted'] = [
            'count' => 0,
            'taxable' => 0,
            'CGST' =>0,
            'SGST' => 0,
            'IGST' => 0,
            'cess' => 0,
            'TotalGST' => 0,
            'InvoiceAmount' => 0
        ];
        $data['export_invoices'] = [
            'count' => 0,
            'taxable' => 0,
            'CGST' =>0,
            'SGST' => 0,
            'IGST' => 0,
            'cess' => 0,
            'TotalGST' => 0,
            'InvoiceAmount' => 0
        ];
        $data['tax_iability_on_advance'] = [
            'count' => 0,
            'taxable' => 0,
            'CGST' =>0,
            'SGST' => 0,
            'IGST' => 0,
            'cess' => 0,
            'TotalGST' => 0,
            'InvoiceAmount' => 0
        ];
        $data['set_off_tax_on_advance_of_prior_period'] = [
            'count' => 0,
            'taxable' => 0,
            'CGST' =>0,
            'SGST' => 0,
            'IGST' => 0,
            'cess' => 0,
            'TotalGST' => 0,
            'InvoiceAmount' => 0
        ];
        $data['credit_debit_Note_and_refund_voucher'] = [
            'count' => 0,
            'taxable' => 0,
            'CGST' =>0,
            'SGST' => 0,
            'IGST' => 0,
            'cess' => 0,
            'TotalGST' => 0,
            'InvoiceAmount' => 0
        ];
        $data['registered_arties'] = [
            'count' => 0,
            'taxable' => 0,
            'CGST' =>0,
            'SGST' => 0,
            'IGST' => 0,
            'cess' => 0,
            'TotalGST' => 0,
            'InvoiceAmount' => 0
        ];
        $data['unregistered_parties'] = [
            'count' => 0,
            'taxable' => 0,
            'CGST' =>0,
            'SGST' => 0,
            'IGST' => 0,
            'cess' => 0,
            'TotalGST' => 0,
            'InvoiceAmount' => 0
        ];
        $data['refund_from_advance'] = [
            'count' => 0,
            'taxable' => 0,
            'CGST' =>0,
            'SGST' => 0,
            'IGST' => 0,
            'cess' => 0,
            'TotalGST' => 0,
            'InvoiceAmount' => 0
        ];

        return $data;
    }

    public static function gstr2_report($year, $month, $last_six_month = 0)
    {
        $data_without_date = DB::table('vendor_purchases_detail')
                    ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'vendor_purchases_detail.idproduct_master')
                    ->select('vendor_purchases_detail.idproduct_master','vendor_purchases_detail.quantity', 'vendor_purchases_detail.unit_purchase_price', 'product_master.cgst', 'product_master.sgst')
                    ->where('product_master.cgst', '<>', 0)
                    ->where('product_master.sgst', '<>', 0);

        if(!empty($last_six_month)) {
            $data_without_date->whereBetween('vendor_purchases_detail.created_at', [
                Carbon::now()->subMonths(6)->startOfDay(),
                Carbon::now()->endOfDay()
            ]);
        } else {
            $data_without_date->whereYear('vendor_purchases_detail.created_at', $year);
            $data_without_date->whereMonth('vendor_purchases_detail.created_at', $month);
        }

        $get_data = $data_without_date->get();

        $data = [];
        $data['period']['start_date'] = empty($last_six_month) ? Carbon::create($year, $month)->startOfMonth()->format('d/m/Y') : Carbon::now()->subMonths(6)->startOfDay()->format('d/m/Y');
        $data['period']['end_date'] = empty($last_six_month) ? Carbon::create($year, $month)->lastOfMonth()->format('d/m/Y') : Carbon::now()->endOfDay()->format('d/m/Y');            
        
        $gross_counter = 0;
        $gross_amount = 0;
        $gross_cgst = 0;
        $gross_sgst = 0;
        $gross_igst = 0;
        $gross_cess = 0;            
        foreach($get_data as $order) {
            $gross_counter += $order->quantity;
            $gross_amount += $order->unit_purchase_price;
            $gross_cgst += $order->cgst;
            $gross_sgst += $order->sgst;
        }
        $total_gst = $gross_cgst + $gross_sgst;
        $invoice_amount = $gross_amount + $total_gst;
        $data['business_to_business'] = [
            'count' => $gross_counter,
            'taxable' => round($gross_amount, 4),
            'CGST' => round($gross_cgst,4),
            'SGST' => round($gross_sgst, 4),
            'IGST' => $gross_igst,
            'cess' => $gross_cess,
            'TotalGST' => round($total_gst, 4),
            'InvoiceAmount' => round($invoice_amount, 4),
        ];

        $data_without_nil_reted_date = DB::table('vendor_purchases_detail')
                    ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'vendor_purchases_detail.idproduct_master')
                    ->select('vendor_purchases_detail.idproduct_master','vendor_purchases_detail.quantity', 'vendor_purchases_detail.unit_purchase_price', 'product_master.cgst', 'product_master.sgst')
                    ->where('product_master.cgst', '=', 0)
                    ->where('product_master.sgst', '=', 0);

        if(!empty($last_six_month)) {
            $data_without_nil_reted_date->whereBetween('vendor_purchases_detail.created_at', [
                Carbon::now()->subMonths(6)->startOfDay(),
                Carbon::now()->endOfDay()
            ]);
        } else {
            $data_without_nil_reted_date->whereYear('vendor_purchases_detail.created_at', $year);
            $data_without_nil_reted_date->whereMonth('vendor_purchases_detail.created_at', $month);
        }

        $get_nil_reted_data = $data_without_nil_reted_date->get();

        $gross__nil_reted_counter = 0;
        $gross_nil_reted_amount = 0;
        foreach($get_nil_reted_data as $order) {
            $gross__nil_reted_counter += $order->quantity;
            $gross_nil_reted_amount += $order->unit_purchase_price;
        }

        $data['nil_rated'] = [
            'count' => $gross__nil_reted_counter,
            'taxable' => round($gross_nil_reted_amount, 4),
            'CGST' =>0,
            'SGST' => 0,
            'IGST' => 0,
            'cess' => 0,
            'TotalGST' => 0,
            'InvoiceAmount' => round($gross_nil_reted_amount, 4),
        ]; 

        return $data;
    }
    
    public static function get_b2c_invoice($year, $month, $start_date, $end_date)
    {
        $B2C_invoice_data = DB::table('customer_order')
                                   ->leftJoin('users', 'users.id', '=', 'customer_order.idcustomer') 
                                   ->select('users.name as desc', 'customer_order.created_at as invoice_date', 'customer_order.idcustomer_order as invoice_no', 'customer_order.total_price as invoice_value');
                                 
        if(!empty($start_date) &&  !empty($end_date)) {
            $B2C_invoice_data->whereBetween('customer_order.created_at',[$start_date, $end_date]);
        } else {
            $B2C_invoice_data->whereYear('customer_order.created_at', $year);
            $B2C_invoice_data->whereMonth('customer_order.created_at', $month);
        } 
        $B2C_invoice = $B2C_invoice_data->get();                        

        $total_quantity = 0.00;
        $total_amount = 0.00;
        $total_taxable_amount = 0.00;
        $total_sgst = 0.00;
        $total_cgst = 0.00;
        $total_igst = 0.00;
        $total_cess = 0.00;
        $total_gst = 0.00;
        foreach($B2C_invoice as $order)
        {
            $date = Carbon::parse($order->invoice_date);
            $order->invoice_date = $date->format('d-M-y');
            $order->desc = !empty($order->desc) ? $order->desc : 'Cash Sales and Purchase';
            $order->local_or_central = 'Local';
            $order->invoice_type = 'Inventory';
            $order->GSTIN = '';
            $products = self::get_order_detail_b2c_invoice($order->invoice_no);
            $product_data = [];
            foreach($products as $key => $product){
                $product_data[$key]['HSN_code'] = $product->HSN_code;
                $product_data[$key]['quantity'] = $product->quantity;
                $product_data[$key]['amount'] = $product->amount;
                $sgst_amount = !empty($product->SGST) ? ($product->amount * $product->SGST)/100 : 0;
                $cgst_amount = !empty($product->CGST) ? ($product->amount * $product->CGST)/100 : 0;
                $taxable_amount = $product->amount - $cgst_amount - $sgst_amount;
                $product_data[$key]['taxable_amount'] = round($taxable_amount, 2);
                $product_data[$key]['SGST_pr'] = $product->SGST;
                $product_data[$key]['SGST_amount'] = round($sgst_amount, 2);
                $product_data[$key]['CGST_pr'] = $product->CGST;
                $product_data[$key]['CGST_amount'] = round($cgst_amount, 2);
                $product_data[$key]['IGST_pr'] = 0.00;
                $product_data[$key]['IGST_amount'] = 0.00;
                $product_data[$key]['cess'] = 0.00;
                $product_data[$key]['total_gst'] = round($sgst_amount + $sgst_amount, 2);
                $total_quantity += $product->quantity;
                $total_amount += $product->amount;
                $total_taxable_amount += $taxable_amount;
                $total_sgst += $sgst_amount;
                $total_cgst += $cgst_amount;
                $total_gst += $sgst_amount + $sgst_amount;
            }
            $order->products = $product_data;
        }    
        $total = [
            'total_quantity' => round($total_quantity, 2),
            'total_amount' => round($total_amount, 2),
            'total_taxable_amount' => round($total_taxable_amount, 2),
            'total_taxable_amount' => round($total_sgst, 2),
            'total_cgst' => round($total_cgst, 2),
            'total_igst' => round($total_igst, 2),
            'total_cess' => round($total_cess, 2),
            'total_gst' => round($total_gst, 2),
        ];
        if(!empty($B2C_invoice->toArray())) {
            $B2C_invoice['total'] = $total;
        }
        $b2c_small_invoice = [];
        $b2c_large_invoice = [];
        if($total_gst <= 250000) {
            $b2c_small_invoice = $B2C_invoice->toArray();
        } else {
            $b2c_large_invoice = $B2C_invoice;
        }

        $data['b2c_large_invoice'] = $b2c_large_invoice;
        $data['b2c_small_invoice'] = $b2c_small_invoice;
        return $data;
    }

    public static function get_order_detail_b2c_invoice($id)
    {
        $order_detail = DB::table('order_detail')
                        ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'order_detail.idproduct_master')
                        ->select('product_master.hsn as HSN_code', 'order_detail.quantity', 'order_detail.total_price as amount', 'order_detail.total_sgst as SGST', 'order_detail.total_cgst as CGST')
                        ->where('order_detail.idcustomer_order', $id)
                        ->where('total_sgst', '<>', 0)
                        ->where('total_cgst', '<>', 0)
                        ->get();
        return $order_detail;                 
    }

    public static function get_nil_reted_invoice($year, $month, $start_date, $end_date)
    {
        $nil_reted_data = DB::table('customer_order')
                                   ->leftJoin('users', 'users.id', '=', 'customer_order.idcustomer') 
                                   ->select('users.name as desc', 'customer_order.created_at as invoice_date', 'customer_order.idcustomer_order as invoice_no', 'customer_order.total_price as invoice_value');
        // $h = 1;
        if(!empty($start_date) &&  !empty($end_date)) {
            $nil_reted_data->whereBetween('customer_order.created_at',[$start_date, $end_date]);
        } else {
            $nil_reted_data->whereYear('customer_order.created_at', $year);
            $nil_reted_data->whereMonth('customer_order.created_at', $month);
        } 
        $nil_reted = $nil_reted_data->get();                           

        $total_quantity = 0.00;
        $total_amount = 0.00;
        $total_taxable_amount = 0.00;
        $total_sgst = 0.00;
        $total_cgst = 0.00;
        $total_igst = 0.00;
        $total_cess = 0.00;
        $total_gst = 0.00;
        foreach($nil_reted as $order)
        {
            $date = Carbon::parse($order->invoice_date);
            $order->invoice_date = $date->format('d-M-y');
            $order->desc = !empty($order->desc) ? $order->desc : 'Cash Sales and Purchase';
            $order->local_or_central = 'Local';
            $order->invoice_type = 'Inventory';
            $order->GSTIN = '';
            $products = self::get_order_detail_nil_reted($order->invoice_no);
            $product_data = [];
            foreach($products as $key => $product){
                $product_data[$key]['HSN_code'] = $product->HSN_code;
                $product_data[$key]['quantity'] = $product->quantity;
                $product_data[$key]['amount'] = $product->amount;
                $sgst_amount = !empty($product->SGST) ? ($product->amount * $product->SGST)/100 : 0;
                $cgst_amount = !empty($product->CGST) ? ($product->amount * $product->CGST)/100 : 0;
                $taxable_amount = $product->amount - $cgst_amount - $sgst_amount;
                $product_data[$key]['taxable_amount'] = round($taxable_amount, 2);
                $product_data[$key]['SGST_pr'] = $product->SGST;
                $product_data[$key]['SGST_amount'] = $sgst_amount;
                $product_data[$key]['CGST_pr'] = $product->CGST;
                $product_data[$key]['CGST_amount'] = $cgst_amount;
                $product_data[$key]['IGST_pr'] = 0.00;
                $product_data[$key]['IGST_amount'] = 0.00;
                $product_data[$key]['cess'] = 0.00;
                $product_data[$key]['total_gst'] = $sgst_amount + $sgst_amount;
                $total_quantity += $product->quantity;
                $total_amount += $product->amount;
                $total_taxable_amount += $taxable_amount;
                $total_sgst += $sgst_amount;
                $total_cgst += $cgst_amount;
                $total_gst += $sgst_amount + $sgst_amount;
            }
            $order->products = $product_data;
        }    
        $total = [
            'total_quantity' => round($total_quantity, 2),
            'total_amount' => round($total_amount, 2),
            'total_taxable_amount' => round($total_taxable_amount, 2),
            'total_taxable_amount' => round($total_sgst, 2),
            'total_cgst' => round($total_cgst, 2),
            'total_igst' => round($total_igst, 2),
            'total_cess' => round($total_cess, 2),
            'total_gst' => round($total_gst, 2),
        ];
        $nil_reted_data = [];
        foreach($nil_reted as $order)
        {
            if(!empty($order->products)){
                $nil_reted_data[] = $order;
            }
        }
        if(!empty($nil_reted_data)) {
            $nil_reted_data['total'] = $total;
        }
        return $nil_reted_data;
    }

    public static function get_order_detail_nil_reted($id)
    {
        $order_detail = DB::table('order_detail')
                        ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'order_detail.idproduct_master')
                        ->select('product_master.hsn as HSN_code', 'order_detail.quantity', 'order_detail.total_price as amount', 'order_detail.total_sgst as SGST', 'order_detail.total_cgst as CGST')
                        ->where('order_detail.idcustomer_order', $id)
                        ->where('total_sgst', '=', 0)
                        ->where('total_cgst', '=', 0)
                        ->get();
        return $order_detail;                
    }

    public static function get_b2b_purchase_invoice($year, $month, $start_date, $end_date)
    {
        $b2b_invoices_data = DB::table('vendor_purchases')
                        ->leftJoin('vendor', 'vendor.idvendor', '=', 'vendor_purchases.idvendor')
                        ->select('vendor.name as desc', 'vendor_purchases.created_at as invoice_date', 'vendor_purchases.bill_number as invoice_no', 'vendor.gst as gstin', 'vendor_purchases.idvendor_purchases');
        
        if(!empty($start_date) &&  !empty($end_date)) {
            $b2b_invoices_data->whereBetween('vendor_purchases.created_at',[$start_date, $end_date]);
        } else {
            $b2b_invoices_data->whereYear('vendor_purchases.created_at', $year);
            $b2b_invoices_data->whereMonth('vendor_purchases.created_at', $month);
        } 
        $b2b_invoices = $b2b_invoices_data->get();

        $total_quantity = 0.00;
        $total_amount = 0.00;
        $total_taxable_amount = 0.00;
        $total_sgst = 0.00;
        $total_cgst = 0.00;
        $total_igst = 0.00;
        $total_cess = 0.00;
        $total_gst = 0.00;
        foreach($b2b_invoices as $order)
        {
            $date = Carbon::parse($order->invoice_date);
            $order->invoice_date = $date->format('d-M-y');
            $order->desc = !empty($order->desc) ? $order->desc : 'Cash Sales and Purchase';
            $products = self::get_order_detail_b2b_invoice($order->idvendor_purchases);
            $product_data = [];
            $invoce_value = 0;
            foreach($products as $key => $product){
                $product_data[$key]['HSN_code'] = $product->HSN_code;
                $product_data[$key]['quantity'] = $product->quantity;
                $sgst_amount = !empty($product->SGST) ? ($product->taxable_amount * $product->SGST)/100 : 0;
                $cgst_amount = !empty($product->CGST) ? ($product->taxable_amount * $product->CGST)/100 : 0;
                $amount = $product->taxable_amount +  $cgst_amount + $sgst_amount;
                $product_data[$key]['amount'] = round($amount, 2);
                $product_data[$key]['taxable_amount'] = round($product->taxable_amount, 2);
                $product_data[$key]['SGST_pr'] = $product->SGST;
                $product_data[$key]['SGST_amount'] = round($sgst_amount, 2);
                $product_data[$key]['CGST_pr'] = $product->CGST;
                $product_data[$key]['CGST_amount'] = round($cgst_amount, 2);
                $product_data[$key]['IGST_pr'] = 0.00;
                $product_data[$key]['IGST_amount'] = 0.00;
                $product_data[$key]['cess'] = 0.00;
                $product_data[$key]['total_gst'] = round($sgst_amount + $sgst_amount, 0);
                $total_quantity += $product->quantity;
                $total_amount += $amount;
                $total_taxable_amount += $product->taxable_amount;
                $total_sgst += $sgst_amount;
                $total_cgst += $cgst_amount;
                $total_gst += $sgst_amount + $sgst_amount;
                $invoce_value += $amount;
            }
            $order->invoice_value = round($invoce_value, 2);
            $order->local_or_central = 'Local';
            $order->invoice_type = 'Inventory';
            $order->GSTIN = !empty($order->gstin) ? $order->gstin : '';
            $order->products = $product_data;
        }

        $total = [
            'total_quantity' => round($total_quantity, 2),
            'total_amount' => round($total_amount, 2),
            'total_taxable_amount' => round($total_taxable_amount, 2),
            'total_taxable_amount' => round($total_sgst, 2),
            'total_cgst' => round($total_cgst, 2),
            'total_igst' => round($total_igst, 2),
            'total_cess' => round($total_cess, 2),
            'total_gst' => round($total_gst, 2),
        ];

        if(!empty($b2b_invoices->toArray())) {
            $b2b_invoices['total'] = $total;
        }

        return $b2b_invoices;
    }

    public static function get_order_detail_b2b_invoice($id)
    {
        $order_detail = DB::table('vendor_purchases_detail')
                        ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'vendor_purchases_detail.idproduct_master')
                        ->select('vendor_purchases_detail.hsn as HSN_code', 'vendor_purchases_detail.quantity', 'vendor_purchases_detail.unit_purchase_price as taxable_amount', 'product_master.sgst as SGST', 'product_master.cgst as CGST')
                        ->where('vendor_purchases_detail.idvendor_purchases', $id)
                        ->where('product_master.sgst', '<>', 0)
                        ->where('product_master.cgst', '<>', 0)
                        ->get();
        return $order_detail;                 
    }

    public static function get_b2b_purchase_nil_reted_invoice($year, $month, $start_date, $end_date)
    {
        $b2b_nil_reted_invoices_data = DB::table('vendor_purchases')
                        ->leftJoin('vendor', 'vendor.idvendor', '=', 'vendor_purchases.idvendor')
                        ->select('vendor.name as desc', 'vendor_purchases.created_at as invoice_date', 'vendor_purchases.bill_number as invoice_no', 'vendor.gst as gstin', 'vendor_purchases.idvendor_purchases');

        if(!empty($start_date) &&  !empty($end_date)) {
            $b2b_nil_reted_invoices_data->whereBetween('vendor_purchases.created_at',[$start_date, $end_date]);
        } else {
            $b2b_nil_reted_invoices_data->whereYear('vendor_purchases.created_at', $year);
            $b2b_nil_reted_invoices_data->whereMonth('vendor_purchases.created_at', $month);
        } 
        $b2b_nil_reted_invoices = $b2b_nil_reted_invoices_data->get();

        $total_quantity = 0.00;
        $total_amount = 0.00;
        $total_taxable_amount = 0.00;
        $total_sgst = 0.00;
        $total_cgst = 0.00;
        $total_igst = 0.00;
        $total_cess = 0.00;
        $total_gst = 0.00;
        foreach($b2b_nil_reted_invoices as $order)
        {
            $date = Carbon::parse($order->invoice_date);
            $order->invoice_date = $date->format('d-M-y');
            $order->desc = !empty($order->desc) ? $order->desc : 'Cash Sales and Purchase';
            $products = self::get_order_detail_b2b_nil_reted_invoice($order->idvendor_purchases);
            $product_data = [];
            $invoce_value = 0;
            foreach($products as $key => $product){
                $product_data[$key]['HSN_code'] = $product->HSN_code;
                $product_data[$key]['quantity'] = $product->quantity;
                $sgst_amount = !empty($product->SGST) ? ($product->taxable_amount * $product->SGST)/100 : 0;
                $cgst_amount = !empty($product->CGST) ? ($product->taxable_amount * $product->CGST)/100 : 0;
                $amount = $product->taxable_amount +  $cgst_amount + $sgst_amount;
                $product_data[$key]['amount'] = round($amount, 2);
                $product_data[$key]['taxable_amount'] = round($product->taxable_amount, 2);
                $product_data[$key]['SGST_pr'] = $product->SGST;
                $product_data[$key]['SGST_amount'] = round($sgst_amount, 2);
                $product_data[$key]['CGST_pr'] = $product->CGST;
                $product_data[$key]['CGST_amount'] = round($cgst_amount, 2);
                $product_data[$key]['IGST_pr'] = 0.00;
                $product_data[$key]['IGST_amount'] = 0.00;
                $product_data[$key]['cess'] = 0.00;
                $product_data[$key]['total_gst'] = round($sgst_amount + $sgst_amount, 0);
                $total_quantity += $product->quantity;
                $total_amount += $amount;
                $total_taxable_amount += $product->taxable_amount;
                $total_sgst += $sgst_amount;
                $total_cgst += $cgst_amount;
                $total_gst += $sgst_amount + $sgst_amount;
                $invoce_value += $amount;
            }
            $order->invoice_value = round($invoce_value, 2);
            $order->local_or_central = 'Local';
            $order->invoice_type = 'Inventory';
            $order->GSTIN = !empty($order->gstin) ? $order->gstin : '';
            $order->products = $product_data;
        }

        $total = [
            'total_quantity' => round($total_quantity, 2),
            'total_amount' => round($total_amount, 2),
            'total_taxable_amount' => round($total_taxable_amount, 2),
            'total_taxable_amount' => round($total_sgst, 2),
            'total_cgst' => round($total_cgst, 2),
            'total_igst' => round($total_igst, 2),
            'total_cess' => round($total_cess, 2),
            'total_gst' => round($total_gst, 2),
        ];

        $b2b_nil_reted_invoices_data = [];
        foreach($b2b_nil_reted_invoices as $order)
        {
            if(!empty($order->products)){
                $b2b_nil_reted_invoices_data[] = $order;
            }
        }

        if(!empty($b2b_nil_reted_invoices->toArray())) {
            $b2b_nil_reted_invoices_data['total'] = $total;
        }

        return $b2b_nil_reted_invoices_data;
    }

    public static function get_order_detail_b2b_nil_reted_invoice($id)
    {
        $order_detail = DB::table('vendor_purchases_detail')
                        ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'vendor_purchases_detail.idproduct_master')
                        ->select('vendor_purchases_detail.hsn as HSN_code', 'vendor_purchases_detail.quantity', 'vendor_purchases_detail.unit_purchase_price as taxable_amount', 'product_master.sgst as SGST', 'product_master.cgst as CGST')
                        ->where('vendor_purchases_detail.idvendor_purchases', $id)
                        ->where('product_master.sgst', 0)
                        ->where('product_master.cgst', 0)
                        ->get();
        return $order_detail;                 
    }

    public static function get_purchase_data($start_date, $end_date)
    {
        $get_purchase_order_data = DB::table('purchase_order')
                              ->leftJoin('vendor', 'vendor.idvendor', '=', 'purchase_order.idvendor')
                              ->leftJoin('store_warehouse', 'store_warehouse.idstore_warehouse', '=', 'purchase_order.idstore_warehouse')
                              ->select('purchase_order.id as idpurchase_order', 'vendor.name as vendor_name', 'store_warehouse.name as warehouse_name', 'purchase_order.total_quantity', 'purchase_order.created_at as order_date');
        
        if(!empty($start_date) &&  !empty($end_date)) {
            $get_purchase_order_data->whereBetween('purchase_order.created_at',[$start_date, $end_date]);
        }
        $get_purchase_order = $get_purchase_order_data->get();
        
        foreach($get_purchase_order as $order) {
            $date = Carbon::parse($order->order_date);
            $order->order_date = $date->format('d-M-y');
            $order_detail = self::get_order_detail($order->idpurchase_order);
            $order->products = !empty($order_detail->toArray()) ? $order_detail : [];
        }
        return $get_purchase_order;
    }

    public static function get_order_detail($id)
    {
        $order_detail = DB::table('purchase_order_detail')
                        ->leftJoin('product_master', 'product_master.idproduct_master', 'purchase_order_detail.idproduct_master')
                        ->select('product_master.idproduct_master', 'product_master.name', 'product_master.barcode', 'purchase_order_detail.quantity')
                        ->where('idpurchase_order', $id)
                        ->get();
        return $order_detail;                
    }

    public static function get_grn_purchase_data($start_date, $end_date)
    {
        $get_purchase_order_data = DB::table('grn_purchase_order')
                              ->leftJoin('vendor', 'vendor.idvendor', '=', 'grn_purchase_order.idvendor')
                              ->leftJoin('store_warehouse', 'store_warehouse.idstore_warehouse', '=', 'grn_purchase_order.idstore_warehouse')
                              ->select('grn_purchase_order.id as idpurchase_order', 'vendor.name as vendor_name', 'store_warehouse.name as warehouse_name', 'grn_purchase_order.total_quantity', 'grn_purchase_order.note1', 'grn_purchase_order.note2', 'grn_purchase_order.image1', 'grn_purchase_order.image2', 'grn_purchase_order.created_at as order_date');
        
        if(!empty($start_date) &&  !empty($end_date)) {
            $get_purchase_order_data->whereBetween('grn_purchase_order.created_at',[$start_date, $end_date]);
        }
        $get_purchase_order = $get_purchase_order_data->get();
        
        foreach($get_purchase_order as $order) {
            $date = Carbon::parse($order->order_date);
            $order->order_date = $date->format('d-M-y');
            $order_detail = self::get_grn_order_detail($order->idpurchase_order);
            $order->products = !empty($order_detail->toArray()) ? $order_detail : [];
        }
        $data = self::grn_filter_data($get_purchase_order);
        return $data;
    }

    public static function get_grn_order_detail($id)
    {
        $order_detail = DB::table('grn_purchase_order_detail')
                        ->leftJoin('product_master', 'product_master.idproduct_master', 'grn_purchase_order_detail.idproduct_master')
                        ->select('product_master.idproduct_master', 'product_master.name', 'product_master.barcode', 'grn_purchase_order_detail.quantity', 'grn_purchase_order_detail.sent_quantity', 'grn_purchase_order_detail.extra_product', 'grn_purchase_order_detail.free_product', 'grn_purchase_order_detail.expired_product')
                        ->where('idgrn_purchase_order', $id)
                        ->get();
        return $order_detail;                
    }

    public static function check_sent_quantity($product_id)
    {
        $products = DB::table('purchase_order_detail')->select('quantity')->where('idproduct_master', $product_id)->get()->last();
        return !empty($products->quantity) ? $products->quantity : 0;
    }

    public static function grn_filter_data($data)
    {
        $filtered_data = [];

        foreach($data as $key => $item){
            $filtered_data[$key]['idpurchase_order'] = $item->idpurchase_order;
            $filtered_data[$key]['vendor_name'] = $item->vendor_name;
            $filtered_data[$key]['warehouse_name'] = $item->warehouse_name;
            $filtered_data[$key]['total_quantity'] = $item->total_quantity;
            $filtered_data[$key]['note1'] = $item->note1;
            $filtered_data[$key]['note2'] = $item->note2;
            $filtered_data[$key]['image1'] = $item->image1;
            $filtered_data[$key]['image2'] = $item->image2;
            $filtered_data[$key]['order_date'] = $item->order_date;
            foreach($item->products as $p_key => $product){
                if(empty($product->extra_product) && empty($product->free_product) && empty($product->expired_product)) {
                    $filtered_data[$key]['products'][$p_key]['name'] =  $product->name;
                    $filtered_data[$key]['products'][$p_key]['barcode'] =  $product->barcode;
                    $filtered_data[$key]['products'][$p_key]['quantity'] =  $product->quantity;
                    $filtered_data[$key]['products'][$p_key]['sent_quantity'] =  $product->sent_quantity;
                }else if(!empty($product->extra_product)) {
                    $filtered_data[$key]['extra_products'][$p_key]['name'] =  $product->name;
                    $filtered_data[$key]['extra_products'][$p_key]['barcode'] =  $product->barcode;
                    $filtered_data[$key]['extra_products'][$p_key]['quantity'] =  $product->quantity;
                } else if (!empty($product->free_product)) {
                    $filtered_data[$key]['free_products'][$p_key]['name'] =  $product->name;
                    $filtered_data[$key]['free_products'][$p_key]['barcode'] =  $product->barcode;
                    $filtered_data[$key]['free_products'][$p_key]['quantity'] =  $product->quantity;
                } else if (!empty($product->expired_product)) {
                    $filtered_data[$key]['expired_products'][$p_key]['name'] =  $product->name;
                    $filtered_data[$key]['expired_products'][$p_key]['barcode'] =  $product->barcode;
                    $filtered_data[$key]['expired_products'][$p_key]['quantity'] =  $product->quantity;
                }
            }
            $filtered_data[$key]['products'] = array_values($filtered_data[$key]['products']);
            $filtered_data[$key]['extra_products'] = array_values($filtered_data[$key]['extra_products']);
            $filtered_data[$key]['free_products'] = array_values($filtered_data[$key]['free_products']);
            $filtered_data[$key]['expired_products'] = array_values($filtered_data[$key]['expired_products']);
        }

        return $filtered_data;
    }
}
