<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\CustomerAddress;
use Illuminate\Support\Facades\DB;
use App\Models\OrderDetailTemp;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderTemp;
use App\Models\WalletTransaction;
use App\Models\OrderDetail;
use App\Models\DeliverySlots;
use App\Models\User;
use App\Models\ProductBatch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use stdClass;
use Illuminate\Support\Facades\Http;

class CustomerOrderTempController extends Controller
{

    public $total = [
        'total' => 0,
        'cgst' => 0,
        'sgst' => 0,
        'discount' => 0,
        'cdiscount' => 0,
        'extraDisc' => 0,
        'coupon' => 0,
        'grand' => 0,
        'totalQty' => 0,
    ];
    public $currOrder;
    public $actualDiscountPer = 0;
    public $actualDiscountAmount = 0;
    public $selectedNonGenPkgId = 0;
    public $applyOnlyDFD = false;
    public $currentUser = [];
    public $currCartItemArranged = [];
    public $activePkgList = [];
    public $tagProdsCart = [];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $customerordertemp = CustomerOrderTemp::latest()->paginate(25);

        return $customerordertemp;
    }

    public function addToCart(Request $request)
    {
        $req = json_decode($request->getContent());
        DB::beginTransaction();

        try {
            $idstore_warehouse = 0;
            $user = auth()->guard('api')->user();

            $cOrderTmp = CustomerOrderTemp::where('cart_id', $req->cart_id) //assuming cartID as device ID to open this cart api
                ->where('status', 3); //3 Temp Cart

            $isCounterOrder = true;
            if ($user) {
                //user is login and valid
                if ($user->user_type == 'C') {
                    $customer = $user;
                    $cOrderTmp->where('idcustomer', $user->id);
                    $idstore_warehouse = $req->idstore;
                    $isCounterOrder = false;
                } elseif ($user->user_type == 'ST') {
                    $userAccess = DB::table('staff_access')
                        ->join('store_warehouse', 'staff_access.idstore_warehouse', '=', 'store_warehouse.idstore_warehouse')
                        ->select(
                            'staff_access.idstore_warehouse',
                            'staff_access.idstaff_access',
                            'store_warehouse.is_store',
                            'staff_access.idstaff'
                        )
                        ->where('staff_access.idstaff', $user->id)
                        ->first();
                    $customer = User::where(['contact' => $req->contact, 'user_type' => 'C'])->first();
                    $idstore_warehouse = $userAccess->idstore_warehouse;
                } else {
                    throw new Exception("Invalid Access");
                }
            } else {
                //user is not login
                $idstore_warehouse = $req->idstore;
                $isCounterOrder = false;
                $customer = false;
                $user = new stdClass();
                $user->id = 0;
            }

            $customerOrder = $cOrderTmp->first();
            if (!isset($customerOrder->idcustomer_order_temp)) {
                $customerOrder = CustomerOrderTemp::create([
                    'idstore_warehouse' => $idstore_warehouse,
                    'idcustomer' => isset($customer->id) ? $customer->id : 0,
                    'cart_id' => $req->cart_id,
                    'is_online' => $isCounterOrder ? 0 : 1,
                    'is_pos' => $isCounterOrder ? 1 : 0,
                    'is_paid_online' => 0,
                    'is_paid' => 0,
                    'is_delivery' => 0,
                    'total_quantity' => 0,
                    'total_price' => 0,
                    'total_cgst' => 0,
                    'total_sgst' => 0,
                    'total_discount' => 0,
                //   'instant_discount' => $req->instant_discount ?? 0,
                //       'product_discount' => $req->product_discount ?? 0,
                //       'copartner_discount' => $req->copartner_discount ?? 0,
                //          'land_discount' => $req->land_discount ?? 0,
                    'discount_type' => 0,
                    'promocode' => null,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'status' => 3 // 1 Active is on hold 0 released // 2 Processed // 3 Temp Cart
                ]);
            }
            $itemInOrderDetTmp = OrderDetailTemp::where('idcustomer_order_temp', $customerOrder->idcustomer_order_temp)
                ->where('idproduct_master', $req->idproduct_master)
                ->first();
 
            $prodDetail = DB::table('product_master')
                ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
                ->select(
                    'product_master.idproduct_master',
                    'product_master.idcategory',
                    'product_master.idsub_category',
                    'product_master.idsub_sub_category',
                    'product_master.name AS prod_name',
                    'product_master.description',
                    'product_master.barcode',
                    'product_master.hsn',
                    'product_master.sgst',
                    'product_master.cgst',
                    'product_master.igst',
                    'inventory.product',
                   'inventory.copartner',
                    'inventory.land',
                    'inventory.quantity',
                    'inventory.idinventory',
                    'inventory.selling_price',
                    'inventory.mrp',
                    'inventory.discount'
                )
                ->where('product_master.idproduct_master', $req->idproduct_master)
                ->where('inventory.idstore_warehouse', $idstore_warehouse)
                ->first();
         //dd($prodDetail );
            $sgstAmtItem = 0;
            $cgstAmtItem = 0;
            $totalTaxPercent = floatval($prodDetail->cgst) + floatval($prodDetail->sgst);
            if ($totalTaxPercent > 0) {
                $preTaxAmount = $prodDetail->selling_price / (($totalTaxPercent + 100) / 100);
                $sgstAmtItem = ($preTaxAmount * $prodDetail->sgst) / 100;
                $cgstAmtItem = ($preTaxAmount * $prodDetail->cgst) / 100;
            }

            if (!$itemInOrderDetTmp) {
                $orderdetailtemp = OrderDetailTemp::insert([
                    'idcustomer_order_temp' => $customerOrder->idcustomer_order_temp,
                    'idproduct_master' => $req->idproduct_master,
                    'idinventory' => $req->idinventory,
                    'quantity' => $req->qty,
                    'total_price' => $req->qty * $prodDetail->selling_price,
                    'total_cgst' => $cgstAmtItem * $req->qty,
                    'total_sgst' => $sgstAmtItem * $req->qty,
                    'unit_mrp' => $prodDetail->mrp,
                    'unit_selling_price' => $prodDetail->selling_price,
                    'discount' => ($prodDetail->mrp - $prodDetail->selling_price), //TODO
                      'product_discount' => $prodDetail->product?? 0,
                       'copartner_discount' => $prodDetail->copartner ?? 0,
                         'land_discount' => $prodDetail->land ?? 0,
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'status' => 1
                ]);
            } else {
                OrderDetailTemp::where('idcustomer_order_temp', $customerOrder->idcustomer_order_temp)
                    ->where('idproduct_master', $req->idproduct_master)
                    ->update(
                        [
                            'quantity' => $req->qty,
                            'total_price' => $req->qty * $prodDetail->selling_price,
                            'total_cgst' => $cgstAmtItem * $req->qty,
                            'total_sgst' => $sgstAmtItem * $req->qty,
                            'unit_mrp' => $prodDetail->mrp,
                            'unit_selling_price' => $prodDetail->selling_price,
                            'discount' => ($prodDetail->mrp - $prodDetail->selling_price), //TODO
                      'product_discount' => $prodDetail->product ?? 0,
                       'copartner_discount' => $prodDetail->copartner ?? 0,
                         'land_discount' => $prodDetail->land ?? 0,
                            'updated_by' => $user->id,
                            'status' => 1
                        ]
                    );
            }
            $this->prepareCart($request);
            DB::commit();
            return response()->json([
                "statusCode" => 0,
                "message" => "Success",
                "tagProds" => $this->tagProdsCart,
                "cartItems" => $this->currOrder,
                "total" => $this->total
            ], 200);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["statusCode" => 1, "message" => $e->getMessage(), "err" => $e->getTrace()], 200);
        }
    }

    public function removeFromCart(Request $request)
    {
        $req = json_decode($request->getContent());
        DB::beginTransaction();

        try {
            $idstore_warehouse = 0;
            $user = auth()->guard('api')->user();
            
            $cOrderTmp = CustomerOrderTemp::where('cart_id', $req->cart_id)
                ->where('status', 3); //3 Temp Cart

            if ($user) {
                if ($user->user_type == 'C') {
                    $cOrderTmp->where('idcustomer', $user->id);
                } elseif ($user->user_type == 'ST') {
                    DB::table('staff_access')
                        ->join('store_warehouse', 'staff_access.idstore_warehouse', '=', 'store_warehouse.idstore_warehouse')
                        ->select(
                            'staff_access.idstore_warehouse',
                            'staff_access.idstaff_access',
                            'store_warehouse.is_store',
                            'staff_access.idstaff'
                        )
                        ->where('staff_access.idstaff', $user->id)
                        ->first();
                } else {
                    throw new Exception("Invalid Access");
                }
            } else {
                //user is not login open api request
            }

            $customerOrder = $cOrderTmp->first();
            if (!isset($customerOrder->idcustomer_order_temp)) {
                throw new Exception("Order not Found.");
            }
            $orderDet = OrderDetailTemp::where('idcustomer_order_temp', $customerOrder->idcustomer_order_temp)->get();
            if (count($orderDet) > 1) {
                OrderDetailTemp::where('idcustomer_order_temp', $customerOrder->idcustomer_order_temp)
                    ->where('idproduct_master', $req->idproduct_master)
                    ->delete();
                //$this->prepareCart($request);
            } else {
                CustomerOrderTemp::where('idcustomer_order_temp', $customerOrder->idcustomer_order_temp)
                    ->delete();
                OrderDetailTemp::where('idcustomer_order_temp', $customerOrder->idcustomer_order_temp)
                    ->delete();
            }
            DB::commit();
            return response()->json([
                "statusCode" => 0,
                "message" => "Success",
                "tagProds" => $this->tagProdsCart,
                "cartItems" => $this->currOrder,
                "total" => $this->total
            ], 200);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

    public function getCart(Request $request)
    {
        try {
            $this->prepareCart($request);
            return response()->json([
                "statusCode" => 0,
                "message" => "Success",
                "tagProds" => $this->tagProdsCart,
                "cartItems" => $this->currOrder,
                "total" => $this->total
            ], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => $e->getMessage(), "message2" => $e->getTraceAsString()], 200);
        }
    }

    public function prepareCart($request)
    {
        $req = json_decode($request->getContent());
        $user = auth()->guard('api')->user();
        $this->currentUser = $user;
        $this->calcDiscAndPackage($req);
        $this->calculateExtraDiscount($req);
        $this->calculateTotal($req);
        $this->checkAndApplyPackages();
        $this->calculateExtraDiscount($req);
        $this->calculateTotal($req);

        return true;
    }

    public function calcDiscAndPackage($req)
    {
        $this->currOrder = DB::table('customer_order_temp')
            ->leftJoin('users', 'customer_order_temp.idcustomer', '=', 'users.id')
            ->leftJoin('membership_plan', 'membership_plan.idmembership_plan', '=', 'users.idmembership_plan')
            ->select(
                'membership_plan.idmembership_plan',
                'membership_plan.instant_discount',
                'membership_plan.commission',
                'customer_order_temp.*'
            )
            ->where('customer_order_temp.cart_id', $req->cart_id)
            ->where('customer_order_temp.status', 3)
            ->where('customer_order_temp.idstore_warehouse', $req->idstore)
            ->first();
        if (!$this->currOrder) {
            throw new Exception("Order Not Found");
        }
        $this->currOrder->items = DB::table('order_detail_temp')
            ->leftJoin('product_master', 'order_detail_temp.idproduct_master', '=', 'product_master.idproduct_master')
            ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
            ->select(
                'product_master.idproduct_master',
                'product_master.name AS prod_name',
                'product_master.barcode',
                'product_master.hsn',
                'product_master.sgst',
                'product_master.cgst',
                'product_master.status',
                'inventory.idinventory',
                'inventory.selling_price',
                'inventory.mrp',
                'inventory.discount',
                'inventory.product',
                'inventory.copartner',
                'inventory.land',
                'inventory.instant_discount_percent',
                'order_detail_temp.*'
            )
            ->where('inventory.idstore_warehouse', $this->currOrder->idstore_warehouse)
            ->where('order_detail_temp.idcustomer_order_temp', $this->currOrder->idcustomer_order_temp)
            ->get();

        if ((isset($req->isAppliedDynFxDis) && $req->isAppliedDynFxDis) && $this->currentUser && ($this->currentUser['idmembership_plan'] == 1)) {
            $this->applyOnlyDFD = true;
        }
    }



    public function calculateTotal($req)
    {
        $totalTaxPercent = 0;
        $totalTaxAmount = 0;
        $preTaxAmount = 0;
        $sgstAmtItem = 0;
        $cgstAmtItem = 0;
        $targetSPforCalc = 0;
        $this->total['cgst'] = 0;
        $this->total['sgst'] = 0;
        $this->total['total'] = 0;
        $this->total['discount'] = 0;
        $this->total['grand'] = 0;
        $this->total['totalQty'] = 0;
        $this->total['instant_p'] = 0;
        $this->total['product_p'] = 0;
        $this->total['copartner_p'] = 0;
        $this->total['land_p'] = 0;

        $mplans = DB::table('membership_plan')
            ->where('status', 1)
            ->where('instant_discount', 0)
            ->get();

        foreach ($this->currOrder->items as $item) {
            $this->currCartItemArranged["p-" . $item->idproduct_master] = $item;
            $totalTaxPercent = floatval($item->cgst) + floatval($item->sgst);
            $targetSPforCalc = $item->selling_price;

            if($this->currentUser != null)
            {
            if ($this->currentUser->idmembership_plan == 2  || $this->currentUser->idmembership_plan == 3 || $this->currentUser->idmembership_plan == 4) {
                $targetSPforCalc = $item->mrp; //item
            }
            else
            {
                $targetSPforCalc = $item->selling_price;
            }
            }
            else
            {
                $targetSPforCalc = $item->selling_price;
            }
            if (isset($item->totSelling_price) && $item->totSelling_price > 0) {
                $targetSPforCalc = $item->totSelling_price / $item->quantity;
            }

            if ($this->actualDiscountPer > 0) {
                $item->postDiscountPrice = $targetSPforCalc - ($targetSPforCalc * $this->actualDiscountPer / 100);

                if ($this->applyOnlyDFD) {
                    if ($item->instant_discount_percent > 0) {
                        $item->postDiscountPrice = $item->selling_price - ($item->selling_price * $item->instant_discount_percent / 100);
                    } else {
                        $item->postDiscountPrice = $item->selling_price;
                    }
                }

                $this->total['extraDisc'] = $this->actualDiscountAmount;
            } else {
                $item->postDiscountPrice = $targetSPforCalc;
                $this->total['extraDisc'] = 0;
            }

            if ($totalTaxPercent > 0) {
                $preTaxAmount = $item->postDiscountPrice / (($totalTaxPercent + 100) / 100);
                $totalTaxAmount = $item->postDiscountPrice - $preTaxAmount;
                $sgstAmtItem = round((($preTaxAmount * $item->sgst) / 100), 2);
                $cgstAmtItem = round((($preTaxAmount * $item->cgst) / 100), 2);
            }

            $item->sgstAmt = $sgstAmtItem;
            $item->cgstAmt = $cgstAmtItem;
            $this->total['cgst'] += $cgstAmtItem * $item->quantity;
            $this->total['sgst'] += $sgstAmtItem * $item->quantity;
            $this->total['total'] += $item->mrp * $item->quantity;
            $this->total['discount'] += ($item->mrp - $targetSPforCalc) * $item->quantity;
            $this->total['grand'] += $item->postDiscountPrice * $item->quantity;
            $this->total['totalQty'] += $item->quantity;
            $this->total['instant_p'] +=(($item->mrp * $item->quantity)-($item->selling_price * $item->quantity));
            $this->total['product_p'] += (($item->mrp * $item->quantity)-($item->product * $item->quantity));
            $this->total['copartner_p'] += (($item->mrp * $item->quantity)-($item->copartner * $item->quantity));
            $this->total['land_p'] += (($item->mrp * $item->quantity)-($item->land * $item->quantity));
            /*
            $disc = [];
            foreach ($mplans as $membership) {
                $curDesc = [];
                $curDesc['idmembership_plan'] = $membership->idmembership_plan;
                $curDesc['name'] = $membership->name;
                $curDesc['commission'] = $membership->commission;
                $curDesc['selling_price'] = $item->selling_price - ($item->selling_price * ($membership->commission) / 100);
                $disc[] = $curDesc;
            }
            $item->member_price = $disc;*/
        }

        $pkgTotAmt = 0;
        $pkgGst = 0;
        foreach ($this->tagProdsCart as $pkg) {
            $pkgGst = (count($pkg['products']) > 0) ? ($pkg['products'][0]->cgst + $pkg['products'][0]->sgst) : 0;
            $pkgTotAmt = $pkg['amount'];
            if ($pkgGst > 0) {
                $preTaxAmount = $pkg['amount'] / (($pkgGst + 100) / 100);
                $totalTaxAmount = $pkg['amount'] - $preTaxAmount;
                $sgstAmtItem = round((($preTaxAmount * $pkg['products'][0]->sgst) / 100), 2);
                $cgstAmtItem = round((($preTaxAmount * $pkg['products'][0]->cgst) / 100), 2);
            }
            $this->total['cgst'] += $cgstAmtItem;
            $this->total['sgst'] += $sgstAmtItem;
            $this->total['total'] += $pkgTotAmt;
            $this->total['discount'] += 0;
            $this->total['grand'] += $pkgTotAmt;
            $this->total['totalQty'] += $pkg['totalPkgQty'];
        }

        // $this->total['discount'] =
        //     $this->total['discount'] + ($cdiscountamount + $couponDiscountAmount);
        // $this->total['grand'] =
        //     $this->total['grand'] - ($cdiscountamount + $couponDiscountAmount);
    }

    public function calculateExtraDiscount($req)
    {
        // die(print_r($req));
        $toGvDiscAmt = 0;
        $couponDiscountAmount = 0; //TODO coupon
        if (isset($req->discountAmount) && $req->discountAmount > 0) {
            $toGvDiscAmt = $req->discountAmount;
        }
        if (isset($req->discountPercentage) && $req->discountPercentage > 0) {
            $toGvDiscAmt = $this->total['total'] * (floatval($req->discountPercentage) / 100);
        } else if ($couponDiscountAmount > 0) {
            $toGvDiscAmt = $this->total['total'] * (floatval($couponDiscountAmount) / 100);
        } else if (isset($req->selectedNonGenPkgId) && !!$req->selectedNonGenPkgId) {
            $this->selectedNonGenPkgId = $req->selectedNonGenPkgId;
            $toGvDiscAmt = 0;
        } else if ((isset($req->isAppliedDynFxDis) && $req->isAppliedDynFxDis) && $this->currentUser && ($this->currentUser['idmembership_plan'] == 1)) {
            //$toGvDiscAmt = $this->total['total'] * (floatval($this->currOrder->commission) / 100);//
            foreach ($this->currOrder->items as $item) {
                if ($item->instant_discount_percent > 0)
                    $toGvDiscAmt += $item->selling_price * $item->instant_discount_percent / 100; //Actual discount amount on cart after DFD
            }
            // die(var_dump($toGvDiscAmt));
        }

        if ($toGvDiscAmt > 0) {
            $this->actualDiscountAmount = $toGvDiscAmt;
        } else {
            $this->actualDiscountAmount = 0;
        }
        $this->actualDiscountPer = ($this->total['total'] == 0) ? 0 : $this->actualDiscountAmount * (100 / $this->total['total']);
    }

    public function checkAndApplyPackages()
    {
        $activePkgList = [];
        $tagProdsCart = [];

        $packageListOrg = PackageController::getPkgDetails($this->currOrder->idstore_warehouse);
        $genMemPkg = []; //
        $memPkg = [];
        $genOpenPkg = []; //
        $openPkg = [];
        foreach ($packageListOrg as $pkg) {
            if ($pkg->bypass_make_gen == 1 && ($pkg->applicable_on == "BOTH" || $pkg->applicable_on == "M")) {
                $genMemPkg[] = $pkg;
            }
            if ($pkg->bypass_make_gen == 0 && ($pkg->applicable_on == "BOTH" || $pkg->applicable_on == "M")) {
                $memPkg[] = $pkg;
            }
            if ($pkg->bypass_make_gen == 1 && ($pkg->applicable_on == "BOTH" || $pkg->applicable_on == "N")) {
                $genOpenPkg[] = $pkg;
            }
            if ($pkg->bypass_make_gen == 0 && ($pkg->applicable_on == "BOTH" || $pkg->applicable_on == "N")) {
                $openPkg[] = $pkg;
            }
        }

        foreach ($this->currOrder->items as $item) {
            if ($this->currOrder->idcustomer > 0 && $this->currOrder->idmembership_plan > 1) {
                //user is member
                foreach ($genMemPkg as $pkg) {
                    $this->applyAllPackages($item, $pkg);
                }
            } else {
                //apply general
                //apply open discount
                foreach ($genOpenPkg as $pkg) {
                    $this->applyAllPackages($item, $pkg);
                }
            }
            $selectedNonGenPkg = null;
            if (isset($this->selectedNonGenPkgId) && $this->selectedNonGenPkgId > 0) {
                foreach ($openPkg as $pk) {
                    if ($pk->idpackage == $this->selectedNonGenPkgId) {
                        $selectedNonGenPkg = $pk;
                        break;
                    }
                }
            }
            if (!!$selectedNonGenPkg && $selectedNonGenPkg->idpackage > 0) {
                $this->applyAllPackages($item, $selectedNonGenPkg);
            }
        };
    }

    public function applyAllPackages($item, $pkg)
    {
        $currGTotal = $this->total['grand'];
        if ($pkg['idpackage_master'] == 1) { //Prod
            $this->applyProductPackage($pkg);
        }
        if ($pkg['idpackage_master'] == 2) { //Amt
            $this->applyAmountPackage($currGTotal, $pkg);
        }
        if ($pkg['idpackage_master'] == 3) { //Qty
            $this->applyQuantityPackage($item, $pkg);
        }
    }

    public function applyProductPackage($pkg)
    {
        $chkTrigProdRes = $this->checkTriggerItemPresent($pkg);
        if ($chkTrigProdRes['allTriggerItemPresent']) {
            $this->addTaggedItems($pkg, $chkTrigProdRes['tagProdsAvlCart']);
        }
    }
    public function applyAmountPackage($currGTotal, $pkg)
    {
        if ($currGTotal >= $pkg['base_trigger_amount']) {
            $this->addTaggedItems($pkg, []);
        }
    }
    public function applyQuantityPackage($pro, $pkg)
    {
        if ($pro->idproduct_master == $pkg->trigger_prod[0]->idproduct_master) {
            if ($pro->quantity > $pkg->trigger_prod[0]->package_item_qty) {
                $totQtyAtBSP = $pkg->trigger_prod[0]->package_item_qty;
                $totQtyAtATM = $pro->quantity - $pkg->trigger_prod[0]->package_item_qty;
                $totalSellingPrice = 0;

                if (!!$pkg->base_trigger_amount) {
                    $totalSellingPrice += $totQtyAtBSP * $pkg->base_trigger_amount;
                } else {
                    $totalSellingPrice += $totQtyAtBSP * $pro->selling_price;
                }

                if (!!$pkg->additional_tag_amount) {
                    $totalSellingPrice += $totQtyAtATM * $pkg->additional_tag_amount;
                } else {
                    $totalSellingPrice += $totQtyAtATM * $pro->selling_price;
                }
                $pro->totSelling_price = $totalSellingPrice;
            } else {
                if ($pro->quantity <= $pkg->trigger_prod[0]->package_item_qty && !!$pkg->base_trigger_amount) {
                    $pro->totSelling_price = $pro->quantity * $pkg->base_trigger_amount;
                } else {
                    $pro->totSelling_price = $pro->quantity * $pro->selling_price;
                }
            }
            $pro->description = 'First ' . $pkg->trigger_prod[0]->package_item_qty . ' Qty at ' . ((!!$pkg->base_trigger_amount) ? $pkg->base_trigger_amount : $pro->selling_price) . ' then at ' . ((!!$pkg->additional_tag_amount) ? $pkg->additional_tag_amount : $pro->selling_price);
            $pro->quantityPkg = $pkg['idpackage'];
        }
        return $pro;
    }
    public function checkTriggerItemPresent($pkg)
    {
        $allTriggerItemPresent = false;
        $trigProd = [];
        $tagProdsAvlCart = [];

        foreach ($pkg['trigger_prod'] as $tProd) {
            $trigProd["p-" .$tProd->idproduct_master] = $tProd->package_item_qty; //all trigger prod
        }
        //dd($this->currCartItemArranged);
        foreach ($trigProd as $key => $value) {
            if (isset($this->currCartItemArranged[$key]) && $this->currCartItemArranged[$key]->quantity >= $trigProd[$key]) {
                if ($pkg['frequency'] > 1) {
                    $tagProdsAvlCart[] = floor($this->currCartItemArranged[$key]->quantity / $trigProd[$key]);
                }
                $allTriggerItemPresent = true;
            } else {
                $allTriggerItemPresent = false;
                break;
            }
        }

        return ['allTriggerItemPresent' => $allTriggerItemPresent, 'tagProdsAvlCart' => $tagProdsAvlCart];
    }
    public function addTaggedItems($pkg, $tagProd)
    {
        if (in_array($pkg['idpackage_master'], $this->activePkgList)) {
            return;
        }
        $this->activePkgList[] = $pkg['idpackage_master'];
        $tagProds = [];
        $totalTagQtyAmt = 0;
        $totalTagQty = count($tagProd) > 0 ? min($tagProd) : 1;
        $totalPkgQty = 0;
        foreach ($pkg['tagged_prod'] as $taProd) {
            if ($pkg['additional_tag_amount'] == 0) {
                $totalTagQtyAmt = 0;
            } else {
                $totalTagQtyAmt = $pkg['additional_tag_amount'] * $totalTagQty;
            }
            $taProd->quantityToDeliver = $taProd->package_item_qty * $totalTagQty;
            $tagProds[] = $taProd; //add these tag products into a cart
            $totalPkgQty += $taProd->quantityToDeliver;
        }
        $this->tagProdsCart[] = [
            'amount' => $totalTagQtyAmt,
            'products' => $tagProds,
            'pkg' => $pkg['idpackage_master'],
            'totalPkgQty' => $totalPkgQty
        ];
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

        $req = json_decode($request->getContent());

        try {
            $coupon = $req->coupon;
            $contact = $req->contact;
            $customer = $req->customer;
            $counter = $req->counter;
            $order_det = $req->order_det;
            $total = $req->total;

            $user = auth()->guard('api')->user();
            $userAccess = DB::table('staff_access')
                ->join('store_warehouse', 'staff_access.idstore_warehouse', '=', 'store_warehouse.idstore_warehouse')
                ->select(
                    'staff_access.idstore_warehouse',
                    'staff_access.idstaff_access',
                    'store_warehouse.is_store',
                    'staff_access.idstaff'
                )
                ->where('staff_access.idstaff', $user->id)
                ->first();

            // $customer = Customer::where(['phone' => $contact])->first();

            $customerordertemp = CustomerOrderTemp::create([
                'idstore_warehouse' => $userAccess->idstore_warehouse,
                'idcustomer' => isset($customer->idcustomer) ? $customer->idcustomer : 0,
                'is_online' => 0,
                'is_pos' => 1,
                'is_paid_online' => 0,
                'is_paid' => 0,
                'is_delivery' => 0,
                'total_quantity' => $total->totalQty,
                'total_price' => $total->grand,
                'total_cgst' => $total->cgst,
                'total_sgst' => $total->sgst,
                'total_discount' => $total->discount,
                'discount_type' => 0,
                'promocode' => null,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'status' => 1 // 1 Active is on hold 0 released // 2 Processed
            ]);

            $ordDet = [];
            $totalTaxPercent = 0;
            $totalTaxAmount = 0;
            $preTaxAmount = 0;
            $cgstPer = 0;
            $sgstPer = 0;
            $sgstAmtItem = 0;
            $cgstAmtItem = 0;
            foreach ($order_det as $prod) {
                $qty = $prod->qty;
                $idproduct_master = $prod->idproduct_master;
                $mrp = $prod->mrp;
                $selling_price = $prod->selling_price;
                $cgst = $prod->cgst;
                $sgst = $prod->sgst;
                // $idproduct_batch = $prod->detail->idproduct_batch;
                $idinventory = $prod->detail->idinventory;

                $totalTaxPercent = $prod->cgst + $prod->sgst;
                if ($totalTaxPercent > 0) {
                    $preTaxAmount = $prod->selling_price / (($totalTaxPercent + 100) / 100);
                    $totalTaxAmount = $prod->selling_price - $preTaxAmount;
                    $cgstPer = $prod->cgst * 100 / $totalTaxPercent;
                    $sgstPer = $prod->sgst * 100 / $totalTaxPercent;
                    $sgstAmtItem = $totalTaxAmount * $sgstPer / 100;
                    $cgstAmtItem = $totalTaxAmount * $cgstPer / 100;
                }
                //   this.total.cgst += $cgstAmtItem * $prod->qty;
                //   this.total.sgst += $sgstAmtItem * $prod->qty;
                //   this.total.total += $prod->mrp * $prod->qty;
                //   this.total.discount += ($prod->mrp - $prod->selling_price) * $prod->qty;

                $ordDet[] = [
                    'idcustomer_order_temp' => $customerordertemp->idcustomer_order_temp,
                    'idproduct_master' => $idproduct_master,
                    'idinventory' => $idinventory,
                    'quantity' => $qty,
                    'total_price' => $qty * $selling_price,
                    'total_cgst' => $cgstAmtItem * $prod->qty,
                    'total_sgst' => $sgstAmtItem * $prod->qty,
                    'unit_mrp' => $prod->mrp,
                     'instant_discount' => $prod->instant_discount,
                      'product_discount' => $prod->product_discount,
                       'copartner_discount' => $prod->copartner_discount,
                        'land_discount' => $prod->land_discount,
                    'unit_selling_price' => $prod->selling_price,
                    'discount' => ($prod->mrp - $prod->selling_price),
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                    'status' => 1
                ];
            }
            $orderdetailtemp = OrderDetailTemp::insert($ordDet);
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage(), "dt" => $ordDet], 200);
        }
    }





    public function placeOrder(Request $request)
    {
        $req = json_decode($request->getContent());
       // $this->prepareCart($request);
        DB::beginTransaction();
        try {
            $idstore_warehouse = 0;
           
            $user = auth()->guard('api')->user();
            $this->currentUser = $user;
            $idcustomer_order_temp= DB::table('customer_order_temp')->select('idcustomer_order_temp')->where('cart_id',$req->cart_id)->first();
            
            $this->currOrder = DB::table('customer_order_temp')
            ->select(
                'customer_order_temp.*'
            )
            ->where('customer_order_temp.cart_id', $req->cart_id)
             ->where('customer_order_temp.status',3)
            ->first();
            
             $this->currOrder->items = DB::table('order_detail_temp')
            ->leftJoin('product_master', 'order_detail_temp.idproduct_master', '=', 'product_master.idproduct_master')
            ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
            ->select(
                'product_master.idproduct_master',
                'product_master.name AS prod_name',
                'product_master.barcode',
                'product_master.hsn',
                'product_master.sgst',
                'product_master.cgst',
                'product_master.status',
                'inventory.idinventory',
                'inventory.selling_price',
                'inventory.mrp',
                'inventory.discount',
                'inventory.product',
                'inventory.copartner',
                'inventory.land',
                'inventory.instant_discount_percent',
                'order_detail_temp.*'
            )
            ->where('inventory.idstore_warehouse', $this->currOrder->idstore_warehouse)
            ->where('order_detail_temp.idcustomer_order_temp', $this->currOrder->idcustomer_order_temp )
            ->get();
            //
            $this->calculateTotal($request);
            $isCounterOrder = 1;
            $counter = 0;
            $payMode = $req->pay_mode;
            $status = 1;
            $payRef = "";
            $deliverySlots = null;
            if (isset($req->payRef)) {
                $payRef = $req->payRef;
            }
            $payMode = strtolower($payMode);
            if ($user->user_type == 'C') {
                $customer = $user;
                $idstore_warehouse = $req->idstore;
                $isCounterOrder = 0;
                if (strtoupper($payMode) != 'CASH') {
                    $status = 3; //Pending Payment
                }
                $deliverySlots = DeliverySlots::where('idstore_warehouse', $idstore_warehouse)
                    ->where('iddelivery_slots', $req->iddelivery_slots)
                    ->where('is_servicable', 1)
                    ->where('available_slots', '>', 0)
                    ->where('status', 1)
                    ->first();
                if (!$deliverySlots) {
                    throw new Exception("Invalid Delivery Slot");
                } else {
                    DB::table('delivery_slots')
                    ->where('iddelivery_slots', $req->iddelivery_slots)
                        ->update([
                            'available_slots' => DB::raw('available_slots - ' . 1)
                        ]);
                }
            } elseif ($user->user_type == 'ST') {
                $userAccess = DB::table('staff_access')
                    ->join('store_warehouse', 'staff_access.idstore_warehouse', '=', 'store_warehouse.idstore_warehouse')
                    ->select(
                        'staff_access.idstore_warehouse',
                        'staff_access.idstaff_access',
                        'store_warehouse.is_store',
                        'staff_access.idstaff'
                    )
                    ->where('staff_access.idstaff', $user->id)
                    ->first();
                $customer = User::where(['contact' => $req->contact, 'user_type' => 'C'])->first();
                $idstore_warehouse = $userAccess->idstore_warehouse;
                $isCounterOrder = 1;
                $counter = $req->counter;
            } else {
                throw new Exception("Invalid Access");
            }


            // Helper::processDiscountOnOrder($req);
            if (isset($customer->id) && $customer->id > 0) {
                $mplans = DB::table('membership_plan')->where('status', 1)->get();
                $planComms = [];
                foreach ($mplans as $mplan) {
                    $mplan->wallet_amount = ($this->total['grand'] * $mplan->commission) / 100;
                    array_push($planComms, $mplan);
                }
                $customerLoggedIn = true;
            } else {
                $customerLoggedIn = false;
            }
            $givenAddDiscountType = null;
            $givenAddDiscountDetail = "";
            if ($request->discountAmount > 0) {
                $givenAddDiscountDetail = "Custom Discount of " . $request->discountAmount;
                $givenAddDiscountType = "CDA"; //Custom Discount Amount
            } else if ($request->discountPercentage > 0) {
                $givenAddDiscountDetail = "Custom Discount of " . $request->discountPercentage . " Percent.";
                $givenAddDiscountType = "CDP"; //Custom Discount Percentage
            } else if (!!$request->couponDiscountAmount) {
                $givenAddDiscountDetail = "Coupon.";
                $givenAddDiscountType = "COU";
            } else if (!!$request->selectedNonGenPkgId) {
                $givenAddDiscountDetail = "Non General Package.";
                $givenAddDiscountType = "PKG";
            } else if ($request->isAppliedDynFxDis) {
                $givenAddDiscountDetail = "Dynamic Fixed Discount.";
                $givenAddDiscountType = "DFD";
            }


            $customerorder = CustomerOrder::create([
                'idstore_warehouse' => $idstore_warehouse,
                'idcounter' => $counter,
                'idcustomer' => ($customerLoggedIn) ? $customer->id : 0,
                'idmembership_plan'=>$customer->idmembership_plan ?? 1,
                'is_online' => ($payMode == 'cash') ? 0 : 1, //Payment is online
                'is_pos' => ($isCounterOrder == 1) ? 1 : 0,
                'is_paid_online' => 0,
                'is_paid' => 0,
                'pay_mode' => $payMode,
                'pay_mode_ref' => $payRef,
                'is_delivery' => ($isCounterOrder == 1) ? 0 : 1,
                'total_quantity' => $this->total['totalQty'],
                'total_price' => $this->total['grand'],
                'total_cgst' => $this->total['cgst'],
                'total_sgst' => $this->total['sgst'],
                'total_discount' => $this->total['total'] - $this->total['grand'],
                'instant_discount' => $this->total['instant_p'],
                'product_discount' => $this->total['product_p'],
                'copartner_discount' => $this->total['copartner_p'],
                'land_discount' => $this->total['land_p'],
                'iddelivery_slots' => (!$deliverySlots) ? 0 : $deliverySlots->iddelivery_slots,
                'discount_type' => $givenAddDiscountType,
                'discount_detail' => $givenAddDiscountDetail,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'status' => $status // 1 Active is on hold 0 released // 2 Processed
            ]);
            
            if($customer->idmembership_plan == 2){
               $this->camount = $this->total['product_p']; 
            }elseif($customer->idmembership_plan == 3){
                 $this->camount = $this->total['land_p']; 
            }elseif($customer->idmembership_plan == 4){
                 $this->camount = $this->total['copartner_p']; 
            }else{
                 $this->camount = 0; 
            }

            if (isset($customer->id) && $customer->id > 0) {
               DB::table('wallet_balances')->insert([
                        'idcustomer'=>$customer->id,
                        'product_dis'=>$this->total['product_p'], 
                        'copartner_dis'=>$this->total['copartner_p'], 
                        'land_dis'=>$this->total['land_p'], 
                        'instant_dis'=>$this->total['instant_p'], 
                        'type'=>0,
                        'amount'=>$this->camount,
                        'dis_type'=>0, 
                        'idcustomer_order'=>$customerorder->idcustomer_order,
                        'membership_id'=>$customer->idmembership_plan
                   
                   ]);
            }
            $ordDet = [];
            foreach ($this->currOrder->items as $prod) {
                if (isset($prod->quantityPkg) && $prod->quantityPkg > 0) {
                    $prod->totSelling_price = (!!$prod->totSelling_price) ? $prod->totSelling_price : 0;
                    $ordDet[] = [
                        'idcustomer_order' => $customerorder->idcustomer_order,
                        'idproduct_master' => $prod->idproduct_master,
                        'idinventory' => $prod->detail->idinventory,
                        'quantity' => $prod->qty,
                        'total_price' => $prod->totSelling_price,
                        'total_cgst' => $prod->cgstAmt * $prod->quantity,
                        'total_sgst' => $prod->sgstAmt * $prod->quantity,
                        'unit_mrp' => $prod->mrp,
                        'unit_selling_price' => $prod->totSelling_price / $prod->qty,
                        'discount' => ($prod->mrp - ($prod->totSelling_price / $prod->qty)),
                        'instant_discount'=>$prod->selling_price,
                        'product_discount'=>$prod->product_discount,
                        'copartner_discount'=>$prod->copartner_discount,
                        'land_discount'=>$prod->land_discount,
                        'created_by' => $user->id,
                        'created_at' => Carbon::now(),
                        'updated_by' => $user->id,
                        'status' => 1,
                        'part_of_pkg' => 1,
                        'idpackage' => $prod->quantityPkg,
                        'pkg_amount' => 0.00,
                        'remark' => $prod->description
                    ];
                } else {
                    $ordDet[] = [
                        'idcustomer_order' => $customerorder->idcustomer_order,
                        'idproduct_master' => $prod->idproduct_master,
                        'idinventory' => $prod->idinventory,
                        'quantity' => $prod->quantity,
                        'total_price' => $prod->quantity * $prod->postDiscountPrice,
                        'total_cgst' => $prod->cgstAmt * $prod->quantity,
                        'total_sgst' => $prod->sgstAmt * $prod->quantity,
                        'unit_mrp' => $prod->mrp,
                        'unit_selling_price' => $prod->postDiscountPrice,
                        'discount' => ($prod->mrp - $prod->postDiscountPrice),
                        'instant_discount'=>$prod->selling_price,
                        'product_discount'=>$prod->product_discount,
                        'copartner_discount'=>$prod->copartner_discount,
                        'land_discount'=>$prod->land_discount,
                        'created_by' => $user->id,
                        'created_at' => Carbon::now(),
                        'updated_by' => $user->id,
                        'status' => 1,
                        'part_of_pkg' => 0,
                        'idpackage' => 0,
                        'pkg_amount' => 0.00,
                        'remark' => ''
                    ];
                }

                // if (isset($prod->detail->selected_batch) && isset($prod->detail->selected_batch->idproduct_batch)) {
                //     DB::table('product_batch')
                //         ->where('idproduct_batch', $prod->detail->selected_batch->idproduct_batch)
                //         ->update([
                //             'quantity' => DB::raw('quantity - ' . $prod->qty)
                //         ]);
                // }
                DB::table('inventory')
                    ->where('idproduct_master', $prod->idproduct_master)
                    ->where('idstore_warehouse', $idstore_warehouse)
                    ->update([
                        'quantity' => DB::raw('quantity - ' . $prod->quantity)
                    ]);
            }
            foreach ($this->tagProdsCart as $pkg) {
                foreach ($pkg['products'] as $prod) {
                    $ordDet[] = [
                        'idcustomer_order' => $customerorder->idcustomer_order,
                        'idproduct_master' => $prod->idproduct_master,
                        'idinventory' => $prod->idinventory,
                        'quantity' => $prod->quantityToDeliver,
                        'total_price' => 0.00,
                        'total_cgst' => 0.00,
                        'total_sgst' => 0.00,
                        'unit_mrp' => $prod->mrp,
                        'unit_selling_price' => 0.00,
                        'discount' => 0.00,
                        'instant_discount'=>0.00,
                        'product_discount'=>0.00,
                        'copartner_discount'=>0.00,
                        'land_discount'=>0.00,
                        'created_by' => $user->id,
                        'created_at' => Carbon::now(),
                        'updated_by' => $user->id,
                        'status' => 1,
                        'part_of_pkg' => 1,
                        'idpackage' => $pkg['pkg'],
                        'pkg_amount' => $pkg['amount'],
                        'remark' => 'Item is part of Package.'
                    ];
                }
                DB::table('inventory')
                    ->where('idproduct_master', $prod->idproduct_master)
                    ->where('idstore_warehouse', $idstore_warehouse)
                    ->update([
                        'quantity' => DB::raw('quantity - ' . $prod->quantityToDeliver)
                    ]);
            }
            $orderdetail = OrderDetail::insert($ordDet);

            $cord = DB::table('customer_order')
                ->leftJoin('users', 'customer_order.idcustomer', '=', 'users.id')
                ->select(
                    'users.name',
                    'users.contact',
                    'users.email',
                    'users.idmembership_plan',
                    'customer_order.*'
                )
                ->where('customer_order.idcustomer_order', $customerorder->idcustomer_order)->first();
            if ($cord->status == 3) {
                $cord->remark = "Order on Hold, please pay online to proceed.";
            }
            $cord->order_detail = DB::table('order_detail')
                ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'order_detail.idproduct_master')
                ->select(
                    'product_master.name AS prod_name',
                    'product_master.description',
                    'product_master.barcode',
                    'product_master.idproduct_master',
                    'product_master.hsn',
                    'product_master.cgst',
                    'product_master.sgst',
                    'order_detail.*'
                )->where('order_detail.idcustomer_order', $customerorder->idcustomer_order)->get();

            if (!!$deliverySlots) {
                DeliverySlots::where('idstore_warehouse', $idstore_warehouse)
                    ->where('iddelivery_slots', $req->iddelivery_slots)
                    ->update([
                        'available_slots' => DB::raw('available_slots - 1')
                    ]);
            }

            DB::table('customer_order_temp')->where('cart_id', $req->cart_id)
                ->where('idstore_warehouse', $idstore_warehouse)
                ->update([
                    "status" => 2
                ]);

            DB::commit();
             $p=$customer->phone;
             $name=$customer->name;
            $msg=rawurlencode('Dear '.$name.' , you have successfully placed your order having order id '.$customerorder->idcustomer_order.'. DRV GHAR GHAR BAZAR PVT LTD');
            $response = Http::get('http://sms1.mydnshost.in/api/SmsApi/SendSingleApi?UserID=DRVGGB&Password=rjqb7080RJ&SenderID=DRVGGB&Phno='.$p.'&Msg='.$msg.'&EntityID=1201169693784090732&TemplateID=1207169865485341420');
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $cord], 200);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage(), "err2" => $e->getTrace()], 200);
        }
    }

    public function getTempOrders()
    {
        $date = today()->format('Y-m-d');
        $user = auth()->guard('api')->user();
        $userAccess = DB::table('staff_access')
            ->join('store_warehouse', 'staff_access.idstore_warehouse', '=', 'store_warehouse.idstore_warehouse')
            ->select(
                'staff_access.idstore_warehouse',
                'staff_access.idstaff_access',
                'store_warehouse.is_store',
                'staff_access.idstaff'
            )
            ->where('staff_access.idstaff', $user->id)
            ->first();
        $data = CustomerOrderTemp::where(
            'idstore_warehouse',
            $userAccess->idstore_warehouse
        )->where(
            'updated_at',
            '>=',
            $date
        )->get();
        return response()->json(["statusCode" => 0, "message" => "Success", "data" => $data], 200);
    }

   public function getTempOrderDetail($id)
    {
        try {
            $data = OrderDetailTemp::where(
                'idcustomer_order_temp',
                $id
            )->get();

            $user = auth()->guard('api')->user();

            $userAccess = DB::table('staff_access')
                ->join('store_warehouse', 'staff_access.idstore_warehouse', '=', 'store_warehouse.idstore_warehouse')
                ->select(
                    'staff_access.idstore_warehouse',
                    'staff_access.idstaff_access',
                    'store_warehouse.is_store',
                    'staff_access.idstaff'
                )
                ->where('staff_access.idstaff', $user->id)
                ->first();

            $finalProds = [];
            foreach ($data as $pro) {
                $productDetail = DB::table('product_master')
                    ->join('sub_sub_category', 'sub_sub_category.idsub_sub_category', '=', 'product_master.idsub_sub_category')
                    ->join('sub_category', 'sub_category.idsub_category', '=', 'product_master.idsub_category')
                    ->join('category', 'category.idcategory', '=', 'product_master.idcategory')
                    ->join('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                    ->join('inventory', 'inventory.idproduct_master', '=', 'product_master.idproduct_master')
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
                        'inventory.land'

                    )
                    ->where('product_master.idproduct_master', $pro->idproduct_master)
                    ->where('inventory.idstore_warehouse', $userAccess->idstore_warehouse)
                    ->first();

                $productDetail->batches = ProductBatch::where('idstore_warehouse', $userAccess->idstore_warehouse)
                    ->where('idproduct_master', $productDetail->idproduct_master)
                    ->where('status', 1)
                    ->get();
                $productDetail->selected_batch = null;
                if (count($productDetail->batches) == 1) {
                    $productDetail->selected_batch = $productDetail->batches[0];
                }

                $productDetail->selQty = ($productDetail->quantity > $pro['quantity']) ? $pro['quantity'] : $productDetail->quantity;
                $finalProds[] = $productDetail;
            }


            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $finalProds], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "data" => $e->getMessage()], 200);
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
        $customerordertemp = CustomerOrderTemp::findOrFail($id);

        return $customerordertemp;
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

        $customerordertemp = CustomerOrderTemp::findOrFail($id);
        $customerordertemp->update($request->all());

        return response()->json($customerordertemp, 200);
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
        CustomerOrderTemp::destroy($id);

        return response()->json(null, 204);
    }
}
