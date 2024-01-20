<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\CustomerOrder;
use App\Models\CustomerAddress;
use App\Models\ProductMaster;
use Illuminate\Http\Request;
use App\Models\CountersLogin;
use Illuminate\Support\Facades\DB;
use App\Models\OrderDetail;
use App\Models\User;
use App\Models\Payment;
use App\Models\WalletBalance;
use App\Models\WalletTransaction;
use Exception;
use DateTime;
use Illuminate\Support\Facades\Http;

class CustomerOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $customerorder = CustomerOrder::latest()->paginate(25);

        return $customerorder;
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

        DB::beginTransaction();
        try {
            $coupon = $req->coupon;
            $contact = $req->contact ?? "";
            $customer = $req->customer;
            $counter = $req->counter;
            $order_det = $req->order_det;
            $total = $req->total;
            $wallet_amount = 0;
            $isRedeemWallet = $req->redeemWallet;
            $amtRedeem = 0;
            $totalPayable = $total->grand;

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
            if ($contact != "") {
                $customer = User::where('contact', $contact)->where('user_type', 'C')->first();
            } else {
                //$customer = User::where('contact', 9876543210)->where('user_type', 'C')->first();
            }
            // Helper::processDiscountOnOrder($req);
            if (isset($customer->id) && $customer->id > 0) {
                $customer_data =
                    DB::table('users')
                    ->leftJoin('customer_address', 'users.id', '=', 'customer_address.idcustomer')
                    ->leftJoin('membership_plan', 'users.idmembership_plan', '=', 'membership_plan.idmembership_plan')
                    ->leftJoin('wallet_balance', 'users.idmembership_plan', '=', 'wallet_balance.idmembership_plan')
                    ->select(
                        'users.id AS idcustomer',
                        'users.name',
                        'users.contact',
                        'users.email',
                        'users.idmembership_plan',
                        'wallet_balance.current_amount AS wallet_balance',
                        'users.created_by',
                        'users.status',
                        'customer_address.address',
                        'customer_address.pincode',
                        'customer_address.landmark',
                        'membership_plan.name as membership_type',
                        'membership_plan.instant_discount',
                        'membership_plan.commission'
                    )
                    ->where('users.id', $customer->id)
                    ->first();

                $redWall = DB::table('wallet_balance')
                    ->where('idcustomer', $customer->id)
                    ->where('idmembership_plan',  0)
                    ->first();
                $redeemWallet = $redWall->current_amount;

                if ($isRedeemWallet && $redeemWallet > 0) {
                    if (($total->grand - $redeemWallet) < 0) {
                        $totalPayable = 0;
                        $amtRedeem = $total->grand;
                    } else {
                        $totalPayable = ($total->grand - $redeemWallet);
                        $amtRedeem = $redeemWallet;
                    }
                }
                $mplans = DB::table('membership_plan')->where('status', 1)->get();
                $planComms = [];
                foreach ($mplans as $mplan) {
                    $mplan->wallet_amount = ($totalPayable * $mplan->commission) / 100;
                    array_push($planComms, $mplan);
                }

                // if ($customer_data->instant_discount == 0) {
                // $wallet_amount = ($total->grand * $customer_data->commission) / 100;
                // }

                $customerLoggedIn = true;
            } else {
                $customerLoggedIn = false;
            }
            $givenAddDiscountType = null;
            $givenAddDiscountDetail = "";
            if ($request->discountAmt > 0) {
                $givenAddDiscountDetail = "Custom Discount of " . $request->discountAmt;
                $givenAddDiscountType = "CDA"; //Custom Discount Amount
            } else if ($request->discountPer > 0) {
                $givenAddDiscountDetail = "Custom Discount of " . $request->discountPer . " Percent.";
                $givenAddDiscountType = "CDP"; //Custom Discount Percentage
            } else if (!!$request->activeNonGenPkg) {
                $givenAddDiscountDetail = "Non General Package.";
                $givenAddDiscountType = "PKG";
            } else if ($request->isAppliedDynFxDis) {
                $givenAddDiscountDetail = "Dynamic Fixed Discount.";
                $givenAddDiscountType = "DFD";
            } else if (!!$request->coupon) {
                $givenAddDiscountDetail = "Coupon.";
                $givenAddDiscountType = "COU";
            }

            $customerorder = CustomerOrder::create([
                'idstore_warehouse' => $userAccess->idstore_warehouse,
                'idcounter' => $counter,
                'idcustomer' => ($customerLoggedIn) ? $customer->id : 0,
                'is_online' => 0,
                'is_pos' => 1,
                'is_paid_online' => 0,
                'is_paid' => 0,
                'pay_mode' => strtolower($req->paymentMode),
                'pay_mode_ref' => isset($req->payRef) ? $req->payRef : null,
                'is_delivery' => 0,
                'total_quantity' => $total->totalQty,
                'total_price' => $totalPayable,
                'redeemed_amt' => $amtRedeem,
                'total_before_redeem' => $total->grand,
                'total_cgst' => $total->cgst,
                'total_sgst' => $total->sgst,
                'total_discount' => $total->total - $total->grand,
                'instant_discount' => $total->instant_discount ?? 0,
                'product_discount' => $total->product_discount ?? 0,
                'copartner_discount' => $total->copartner ?? 0,
                'land_discount' => $total->land_discount ?? 0,
                'extraDisc' => $total->extraDisc ?? 0,
                'discount_type' => $givenAddDiscountType,
                'discount_detail' => $givenAddDiscountDetail,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'status' => 1 // 1 Active is on hold 0 released // 2 Processed
            ]);

            if (isset($customer->id) && $customer->id > 0) {
                foreach ($planComms as $planComm) {
                    if ($planComm->instant_discount == 1 && $givenAddDiscountType != 'DFD') {
                        continue;
                    }
                    WalletTransaction::create([
                        'idcustomer' => $customer->id,
                        'type' => 'Purchase',
                        'idmembership_plan' => $planComm->idmembership_plan,
                        'ref_id' => $customerorder->idcustomer_order,
                        'amount' => $planComm->wallet_amount,
                        'transaction_type' => 'CR',
                        'remark' => ($planComm->instant_discount == 0) ? "Purchased Goods" : "Purchased Goods With Instant Discount - " . $req->total->extraDisc,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'status' => 1
                    ]);
                    if ($planComm->instant_discount == 0) {
                        DB::table('wallet_balance')
                            ->where('idcustomer', $customer->id)
                            ->where('idmembership_plan',  $planComm->idmembership_plan)
                            ->update([
                                'current_amount' => DB::raw('current_amount + ' . $planComm->wallet_amount),
                                'total_incurred' => DB::raw('total_incurred + ' . $planComm->wallet_amount)
                            ]);
                    } else {
                        DB::table('wallet_balance')
                            ->where('idcustomer', $customer->id)
                            ->where('idmembership_plan',  $planComm->idmembership_plan)
                            ->update([
                                'total_incurred' => DB::raw('total_incurred + ' . $planComm->wallet_amount),
                                'redeemed' => DB::raw('redeemed + ' . $planComm->wallet_amount)
                            ]);
                    }
                }
                // WalletTransaction::create([ //3 entries to create
                //     'idcustomer' => $customer->id,
                //     'type' => 'Purchase',
                //     'idmembership_plan' => 0, // Need all three here
                //     'ref_id' => $customerorder->idcustomer_order,
                //     'amount' => $wallet_amount,
                //     'transaction_type' => 'CR',
                //     'remark' => "Purchased Goods",
                //     'created_by' => $user->id,
                //     'updated_by' => $user->id,
                //     'status' => 1
                // ]);
                // DB::table('customer')
                //     ->where('idcustomer', $customer->id)
                //     ->update([ //3 entries to update
                //         'wallet_balance' => DB::raw('wallet_balance + ' . $wallet_amount)
                //     ]);
                if ($isRedeemWallet && $amtRedeem > 0) {
                    DB::table('wallet_balance')
                        ->where('idcustomer', $customer->id)
                        ->where('idmembership_plan',  0)
                        ->update([
                            'current_amount' => DB::raw('current_amount - ' . $amtRedeem)
                        ]);

                    WalletTransaction::create([
                        'idcustomer' => $customer->id,
                        'type' => 'Purchase',
                        'idmembership_plan' => 0,
                        'ref_id' => $customerorder->idcustomer_order,
                        'amount' => $amtRedeem,
                        'transaction_type' => 'DR',
                        'remark' => "Purchased Goods With Wallet balance",
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'status' => 1
                    ]);
                }
            }

            // if ($givenAddDiscountType == 'DFD') {
            //     Wallet::create([
            //         'idcustomer' => ($customerLoggedIn) ? $customer->id : 0,
            //         'type' => 'Purchase',
            //         'ref_id' => $customerorder->idcustomer_order,
            //         'amount' => 0,
            //         'transaction_type' => 'CR',
            //         'remark' => "Purchased Goods With Instant Discount - " . $req->total->extraDisc,
            //         'created_by' => $user->id,
            //         'updated_by' => $user->id,
            //         'status' => 1
            //     ]);
            // }

            $ordDet = [];
            foreach ($order_det as $prod) {
                if (isset($prod->quantityPkg) && $prod->quantityPkg > 0) {
                    $prod->totSelling_price = (!!$prod->totSelling_price) ? $prod->totSelling_price : 0;
                    $ordDet[] = [
                        'idcustomer_order' => $customerorder->idcustomer_order,
                        'idproduct_master' => $prod->idproduct_master,
                        'idinventory' => $prod->detail->idinventory,
                        'quantity' => $prod->qty,
                        'total_price' => $prod->totSelling_price,
                        'total_cgst' => $prod->cgstAmt * $prod->qty,
                        'total_sgst' => $prod->sgstAmt * $prod->qty,
                        'unit_mrp' => $prod->mrp,
                        'unit_selling_price' => $prod->totSelling_price / $prod->qty,
                        'discount' => ($prod->mrp - ($prod->totSelling_price / $prod->qty)),
                        'instant_discount' => $prod->instant_discount ?? 0,
                        'product_discount' => $prod->product_discount ?? 0,
                        'copartner_discount' => $prod->copartner ?? 0,
                        'land_discount' => $prod->land_discount ?? 0,
                        'created_by' => $user->id,
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
                        'idinventory' => $prod->detail->idinventory,
                        'quantity' => $prod->qty,
                        'total_price' => $prod->qty * $prod->postDiscountPrice,
                        'total_cgst' => $prod->cgstAmt * $prod->qty,
                        'total_sgst' => $prod->sgstAmt * $prod->qty,
                        'unit_mrp' => $prod->mrp,
                        'unit_selling_price' => $prod->postDiscountPrice,
                        'discount' => ($prod->mrp - $prod->postDiscountPrice),
                        'instant_discount' => $prod->instant_discount ?? 0,
                        'product_discount' => $prod->product_discount ?? 0,
                        'copartner_discount' => $prod->copartner ?? 0,
                        'land_discount' => $prod->land_discount ?? 0,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'status' => 1,
                        'part_of_pkg' => 0,
                        'idpackage' => 0,
                        'pkg_amount' => 0.00,
                        'remark' => ''
                    ];
                }

                if (isset($prod->detail->selected_batch) && isset($prod->detail->selected_batch->idproduct_batch)) {
                    DB::table('product_batch')
                        ->where('idproduct_batch', $prod->detail->selected_batch->idproduct_batch)
                        ->update([
                            'quantity' => DB::raw('quantity - ' . $prod->qty)
                        ]);
                }
                DB::table('inventory')
                    ->where('idproduct_master', $prod->idproduct_master)
                    ->where('idstore_warehouse', $userAccess->idstore_warehouse)
                    ->update([
                        'quantity' => DB::raw('quantity - ' . $prod->qty)
                    ]);
            }
            foreach ($req->taggedProds as $pkg) {
                foreach ($pkg->products as $prod) {
                    $ordDet[] = [
                        'idcustomer_order' => $customerorder->idcustomer_order,
                        'idproduct_master' => $prod->idproduct_master,
                        'idinventory' => 0,
                        'quantity' => $prod->quantityToDeliver,
                        'total_price' => 0.00,
                        'total_cgst' => 0.00,
                        'total_sgst' => 0.00,
                        'unit_mrp' => $prod->mrp,
                        'unit_selling_price' => 0.00,
                        'discount' => 0.00,
                        'instant_discount' => $prod->instant_discount ?? 0,
                        'product_discount' => $prod->product_discount ?? 0,
                        'copartner_discount' => $prod->copartner ?? 0,
                        'land_discount' => $prod->land_discount ?? 0,
                        'created_by' => $user->id,
                        'updated_by' => $user->id,
                        'status' => 1,
                        'part_of_pkg' => 1,
                        'idpackage' => $pkg->pkg,
                        'pkg_amount' => $pkg->amount,
                        'remark' => ''
                    ];
                }
                // DB::table('product_batch')
                //     ->where('idproduct_batch', $prod->detail->idproduct_batch)
                //     ->update([
                //         'quantity' => DB::raw('quantity - ' . $prod->qty)
                //     ]);
            }
            $orderdetail = OrderDetail::insert($ordDet);

            if (isset($req->paymentMode) && $req->paymentMode === 'Cash') {
                if (isset($req->cashtTranDetail)) {
                    $det = DB::table('counter')
                        ->join('counters_login', 'counter.idcounter', '=', 'counters_login.idcounter')
                        ->select(
                            'counter.idstore_warehouse',
                            'counters_login.*'
                        )
                        ->where('counter.idstore_warehouse', $userAccess->idstore_warehouse)
                        ->where('counters_login.idstaff', $user->id)
                        ->where('counters_login.status', 1)
                        ->first();

                         
                        DB::statement( DB::raw( 'SET SQL_SAFE_UPDATES=0'));
                    $q = [
                        'cd_1' => DB::raw('cd_1 + ' . (($req->cashtTranDetail->n1 < 0 || !$req->cashtTranDetail->n1 ) ? 0 : $req->cashtTranDetail->n1)),
                        'cd_2' => DB::raw('cd_2 + ' . (($req->cashtTranDetail->n2 < 0 || !$req->cashtTranDetail->n2 ) ? 0 : $req->cashtTranDetail->n2)),
                        'cd_5' => DB::raw('cd_5 + ' . (($req->cashtTranDetail->n5 < 0 || !$req->cashtTranDetail->n5 ) ? 0 : $req->cashtTranDetail->n5)),
                        'cd_10' => DB::raw('cd_10 + ' . (($req->cashtTranDetail->n10 < 0 || !$req->cashtTranDetail->n10 ) ? 0 : $req->cashtTranDetail->n10)),
                        'cd_20' => DB::raw('cd_20 + ' . (($req->cashtTranDetail->n20 < 0 || !$req->cashtTranDetail->n20 ) ? 0 : $req->cashtTranDetail->n20)),
                        'cd_50' => DB::raw('cd_50 + ' . (($req->cashtTranDetail->n50 < 0 || !$req->cashtTranDetail->n50 ) ? 0 : $req->cashtTranDetail->n50)),
                        'cd_100' => DB::raw('cd_100 + ' . (($req->cashtTranDetail->n100 < 0 || !$req->cashtTranDetail->n100 ) ? 0 : $req->cashtTranDetail->n100)),
                        'cd_200' => DB::raw('cd_200 + ' . (($req->cashtTranDetail->n200 < 0 || !$req->cashtTranDetail->n200 ) ? 0 : $req->cashtTranDetail->n200)),
                        'cd_500' => DB::raw('cd_500 + ' . (($req->cashtTranDetail->n500 < 0 || !$req->cashtTranDetail->n500 ) ? 0 : $req->cashtTranDetail->n500)),
                        'cd_2000' => DB::raw('cd_2000 + ' . (($req->cashtTranDetail->n2000 < 0 || !$req->cashtTranDetail->n2000 ) ? 0 : $req->cashtTranDetail->n2000))
                    ];

                    $xs = CountersLogin::where('idcounters_login', $det->idcounters_login)
                        ->where('idstaff', $user->id)
                        ->where('counters_login.status', 1)
                        ->update($q);

                    $q = [
                        'cd_1' => DB::raw('cd_1 - ' . (($req->cashtTranDetail->recn1 < 0 || !$req->cashtTranDetail->recn1) ? 0 : $req->cashtTranDetail->recn1)),
                        'cd_2' => DB::raw('cd_2 - ' . (($req->cashtTranDetail->recn2 < 0 || !$req->cashtTranDetail->recn2) ? 0 : $req->cashtTranDetail->recn2)),
                        'cd_5' => DB::raw('cd_5 - ' . (($req->cashtTranDetail->recn5 < 0 || !$req->cashtTranDetail->recn5) ? 0 : $req->cashtTranDetail->recn5)),
                        'cd_10' => DB::raw('cd_10 - ' . (($req->cashtTranDetail->recn10 < 0 || !$req->cashtTranDetail->recn10) ? 0 : $req->cashtTranDetail->recn10)),
                        'cd_20' => DB::raw('cd_20 - ' . (($req->cashtTranDetail->recn20 < 0 || !$req->cashtTranDetail->recn20) ? 0 : $req->cashtTranDetail->recn20)),
                        'cd_50' => DB::raw('cd_50 - ' . (($req->cashtTranDetail->recn50 < 0 || !$req->cashtTranDetail->recn50) ? 0 : $req->cashtTranDetail->recn50)),
                        'cd_100' => DB::raw('cd_100 - ' . (($req->cashtTranDetail->recn100 < 0 || !$req->cashtTranDetail->recn100) ? 0 : $req->cashtTranDetail->recn100)),
                        'cd_200' => DB::raw('cd_200 - ' . (($req->cashtTranDetail->recn200 < 0 || !$req->cashtTranDetail->recn200) ? 0 : $req->cashtTranDetail->recn200)),
                        'cd_500' => DB::raw('cd_500 - ' . (($req->cashtTranDetail->recn500 < 0 || !$req->cashtTranDetail->recn500) ? 0 : $req->cashtTranDetail->recn500)),
                        'cd_2000' => DB::raw('cd_2000 - ' . (($req->cashtTranDetail->recn2000 < 0 || !$req->cashtTranDetail->recn2000) ? 0 : $req->cashtTranDetail->recn2000))
                    ];
                    CountersLogin::where('idcounters_login', $det->idcounters_login)
                        ->where('idstaff', $user->id)
                        ->where('counters_login.status', 1)
                        ->update($q);
                }
            }

            DB::commit();
            $p = $customer->contact;
            $name = $customer->name;

            $msg = rawurlencode('Dear ' . $name . ' , you have successfully placed your order having order id ' . $customerorder->idcustomer_order . '. DRV GHAR GHAR BAZAR PVT LTD');
            //$response = Http::get('http://sms1.mydnshost.in/api/SmsApi/SendSingleApi?UserID=DRVGGB&Password=rjqb7080RJ&SenderID=DRVGGB&Phno=' . $p . '&Msg=' . $msg . '&EntityID=1201169693784090732&TemplateID=1207169865485341420');


            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $customerorder->idcustomer_order], 200);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }



    public function getOrder(Request $request)
    {
        if ($request->input('order_id')) {
            try {

                $orderId = $request->input('order_id');

                $order = DB::table('order_detail')->where('idorder_detail', $orderId)->select('idcustomer_order', 'idproduct_master', 'idorder_detail')->first();  // Fetch order details by order_id

                $List_order = DB::table('customer_order')->where('idcustomer_order', $order['idcustomer_order'])->first();

                $Product = DB::table('product_master')->where('idproduct_master', $order['idproduct_master'])->first();

                $Purchase = DB::table('product_batch')->where('idproduct_master', $Product['idproduct_master'])->select('purchase_price')->first();

                $Counter_name = DB::table('counter')->where('idcounter', $List_order['idcounter'])->first();
                $User = User::where('id', $List_order['idcustomer'])->first();

                if (!$order) {
                    return response()->json(['error' => 'Order not found'], 404);
                }

                return response()->json(
                    [
                        'Order_No' => $order['idorder_detail'],
                        'Date' => $List_order['created_at'],
                        'Counter_Name' => $Counter_name['name'],
                        'Customer_name' => $User['name'],
                        'Biller_name' => $User['name'],
                        'Discount_Coupon' => $List_order['discount_type'],
                        'Profit_per_bill' => ($Product['mrp'] - ($Purchase['purchase_price'] + $Product['discount'] + $Product['cgst'])),
                    ]
                );
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to fetch order data', 'details' => $e->getMessage()], 500);
            }
        } else {
            try {
                $orderList = OrderDetail::all();  // Fetch all data from the users table
                return response()->json(['orders_list' => $orderList]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to order data', 'details' => $e->getMessage()], 500);
            }
        }
    }


    public function getOrderData(Request $request)
    {
        if ($request->has('customer_order_id')) {
            try {
                $orderId = $request->input('customer_order_id');

                $orderDetails = OrderDetail::where('idcustomer_order', $orderId)->get();  // Fetch order details by customer_order_id

                $details = [];

                foreach ($orderDetails as $orderDetail) {
                    $detail = [
                        'order_id' => $orderDetail->idorder_detail,
                        'product' => [],
                    ];

                    $product = ProductMaster::where('idproduct_master', $orderDetail->idproduct_master)->first();
                    if ($product) {
                        $detail['product'] = [
                            'name' => $product->name,
                            'img' => $product->image,
                            'mrp' => $product->mrp,
                            'discount' => $product->discount,
                            'cgst' => $product->cgst,
                            'sgst' => $product->sgst
                        ];
                    }

                    $details[] = $detail;
                }
                $customerId = CustomerOrder::where('idcustomer_order', $orderId)->select('idcustomer')->first();
                $userData = User::where('id', $customerId['idcustomer'])->first();
                $address = CustomerAddress::where('idcustomer', $customerId['idcustomer'])->first();

                return response()->json(
                    [
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'contact' => $userData['contact'],
                        'address' => [
                            'name' => $address['name'],
                            'address' => $address['address'],
                            'pincode' => $address['pincode'],
                            'landmark' => $address['landmark'],
                            'phone' => $address['phone']
                        ],
                        'order_details' => $details
                    ]
                );
            } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to fetch order data', 'details' => $e->getMessage()], 500);
            }
        } else {
            return response()->json(['error' => 'Failed to provide customer_order_id'], 400);
        }
    }



    public function getOrders()
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
        $data = CustomerOrder::where(
            'idstore_warehouse',
            $userAccess->idstore_warehouse
        )->where(
            'updated_at',
            '>=',
            $date
        )->get();
        return response()->json(["statusCode" => 0, "message" => "Success", "data" => $data], 200);
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
        $customerorder = CustomerOrder::findOrFail($id);

        return $customerorder;
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

        $customerorder = CustomerOrder::findOrFail($id);
        $customerorder->update($request->all());

        return response()->json($customerorder, 200);
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
        CustomerOrder::destroy($id);

        return response()->json(null, 204);
    }

    public function getOrderDetail($orderId)
    {
        try {
            $user = auth()->guard('api')->user();
            if ($user->user_type == 'C') {
                $order = DB::table('customer_order')
                    ->leftJoin('counter', 'customer_order.idcounter', '=', 'counter.idcounter')
                    ->leftJoin('users', 'customer_order.created_by', '=', 'users.id')
                    ->select(
                        'counter.idcounter',
                        'counter.name AS counterName',
                        'users.id as staffId',
                        'users.name as staffName',
                        'users.user_type',
                        'customer_order.*'
                    )
                    ->where('users.user_type', $user->user_type)
                    ->where('customer_order.idcustomer_order', $orderId)
                    ->where('users.id', $user->id)
                    ->first();
            } elseif ($user->user_type == 'ST') {
                $order = CustomerOrder::where('idcustomer_order', $orderId)->first();
            }
            if (!$order) {
                throw new Exception("Invalid Order ID.");
            }
            $ordDet = DB::table('order_detail')
                ->join('product_master', 'order_detail.idproduct_master', '=', 'product_master.idproduct_master')
                ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->select(
                    'brands.name AS brand',
                    'product_master.name AS prod_name',
                    'product_master.description AS prod_desc',
                    'product_master.barcode',
                    'product_master.hsn',
                    'product_master.sgst AS sgst_percent',
                    'product_master.cgst AS cgst_percent',
                    'category.name AS category',
                    'category.has_return_rule',
                    'category.return_type',
                    'category.return_duration',
                    'category.idcategory',
                    'order_detail.*'
                )
                ->where('order_detail.idcustomer_order', $order->idcustomer_order)
                ->get();

            $customerAddress = [];
            if ($order->idcustomer_address != NULL) {
                $customerAddress = DB::table('customer_address')->where('idcustomer_address', $order->idcustomer_address)->first();
            }

            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $ordDet, 'customerAddress' => $customerAddress], 200);
        } catch (Exception $e) {
            return response()->json($e->getTrace(), 403);
        }
    }
    
    
      public function getOrderDetaill($orderId)
    {
        try {
            $user = auth()->guard('api')->user();
    
                  $order = DB::table('customer_order')
                     ->leftJoin('users', 'customer_order.idcustomer', '=', 'users.id')
                    ->select(
                       
                        'users.id as staffId',
                        'users.name as staffName',
                        'users.user_type',
                        'customer_order.*'
                    )
                    ->where('users.user_type', $user->user_type)
                    ->where('customer_order.idcustomer_order', $orderId)
                    ->where('users.id', $user->id)
                    ->first();
                   
            $ordDet = DB::table('order_detail')
                ->join('product_master', 'order_detail.idproduct_master', '=', 'product_master.idproduct_master')
                ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->select(
                    'brands.name AS brand',
                    'product_master.name AS prod_name',
                    'product_master.description AS prod_desc',
                    'product_master.barcode',
                    'product_master.hsn',
                    'product_master.sgst AS sgst_percent',
                    'product_master.cgst AS cgst_percent',
                    'category.name AS category',
                    'category.has_return_rule',
                    'category.return_type',
                    'category.return_duration',
                    'category.idcategory',
                    'order_detail.*'
                )
                ->where('order_detail.idcustomer_order', $order->idcustomer_order)
                ->get();

             $customerAddress = [];
            if (isset($order->idcustomer_address) && $order->idcustomer_address !== NULL) {
                $customerAddress = DB::table('customer_address')->where('idcustomer_address', $order->idcustomer_address)->first();
            }

            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $ordDet, 'customerAddress' => $customerAddress], 200);
        } catch (Exception $e) {
            return response()->json($e->getTrace(), 403);
        }
    }

    public function getOrderDetailById($orderId)
    {
        try {
            $user = auth()->guard('api')->user();

            $cord = DB::table('customer_order')
                ->leftJoin('users', 'customer_order.idcustomer', '=', 'users.id')
                ->leftJoin('membership_plan', 'users.idmembership_plan', '=', 'membership_plan.idmembership_plan')
                ->leftJoin('wallet_balance', 'users.idmembership_plan', '=', 'wallet_balance.idmembership_plan')
                ->select(
                    'users.name',
                    'users.contact',
                    'users.email',
                    'users.idmembership_plan',
                    // 'customer.wallet_balance',
                    'wallet_balance.current_amount AS wallet_balance',
                    'membership_plan.name as membership_type',
                    'membership_plan.instant_discount',
                    'membership_plan.commission',
                    'customer_order.*'
                )
                ->where('customer_order.idcustomer_order', $orderId)->first();
            // $cord->order_detail = OrderDetail::where('idcustomer_order', $customerorder->idcustomer_order)->get();

            $cord->order_detail = DB::table('order_detail')
                ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'order_detail.idproduct_master')
                ->select(
                    'product_master.name AS prod_name',
                    'product_master.description',
                    'product_master.barcode',
                    'product_master.hsn',
                    'product_master.cgst',
                    'product_master.sgst',
                    'order_detail.*'
                )->where('order_detail.idcustomer_order', $orderId)->get();

            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $cord], 200);
        } catch (Exception $e) {
            return response()->json($e->getTrace(), 403);
        }
    }

    public function cancelOrder(Request $request)
    {
        $req = json_decode($request->getContent());
        DB::beginTransaction();
        try {
            $user = auth()->guard('api')->user();

            if ($user->user_type == 'C') {
                $custOrder = DB::table('customer_order')
                    ->leftJoin('counter', 'customer_order.idcounter', '=', 'counter.idcounter')
                    ->leftJoin('users', 'customer_order.created_by', '=', 'users.id')
                    ->select(
                        'counter.idcounter',
                        'counter.name AS counterName',
                        'users.id as staffId',
                        'users.name as staffName',
                        'users.user_type',
                        'customer_order.*'
                    )
                    ->where('users.user_type', $user->user_type)
                    ->where('customer_order.idcustomer_order', $req->idcustomer_order)
                    ->where('users.id', $user->id)
                    ->first();
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

                $custOrder = DB::table('customer_order')
                    ->where('customer_order.idcustomer_order', $req->idcustomer_order)
                    ->where('idstore_warehouse', $userAccess->idstore_warehouse)
                    ->first();
            } else {
                throw new Exception("Invalid Access");
            }
            if (!isset($custOrder->idcustomer_order)) {
                throw new Exception("Order ID not found.");
            }
            if ($custOrder->status == 0) {
                throw new Exception("Order is already Cancelled.");
            }

            $now = Date("Y-m-d");
            $date = Date($custOrder->created_at);
            $billDate = new DateTime($custOrder->created_at);
            $today = new DateTime(date('Y-m-d'));
            $interval = $billDate->diff($today);
            $daysPassed = $interval->format('%a');
            $cancelItemsAmount = 0;

            $custOrderDetail = DB::table('order_detail')
                ->join('product_master', 'order_detail.idproduct_master', '=', 'product_master.idproduct_master')
                ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                ->select(
                    'brands.name AS brand',
                    'product_master.name AS prod_name',
                    'product_master.description AS prod_desc',
                    'product_master.barcode',
                    'product_master.hsn',
                    'product_master.sgst AS sgst_percent',
                    'product_master.cgst AS cgst_percent',
                    'category.name AS category',
                    'category.has_return_rule',
                    'category.return_type',
                    'category.return_duration',
                    'category.idcategory',
                    'order_detail.*'
                )
                ->where('order_detail.idcustomer_order', $custOrder->idcustomer_order)
                ->where('part_of_pkg', 0)
                ->get();


            if (isset($custOrder->idcustomer) && $custOrder->idcustomer > 0) {
                $customer_data =
                    DB::table('users')
                    ->leftJoin('membership_plan', 'users.idmembership_plan', '=', 'membership_plan.idmembership_plan')
                    ->select(
                        'users.id AS idcustomer',
                        'users.name',
                        'users.contact',
                        'users.email',
                        'users.idmembership_plan',
                        // 'customer.wallet_balance',
                        'users.created_by',
                        'users.status',
                        'membership_plan.name as membership_type',
                        'membership_plan.instant_discount',
                        'membership_plan.commission'
                    )
                    ->where('users.id', $custOrder->idcustomer)
                    ->first();

                if ($customer_data->instant_discount == 0) {
                    $wallet_amount = ($custOrder->total_price * $customer_data->commission) / 100;
                }
                $customerLoggedIn = true;
            } else {
                $customerLoggedIn = false;
            }

            // if (count($custOrderDetail) == count($req->order_to_cancel)) {
            //     //cancel complete order
            //     CustomerOrder::where('idcustomer_order', $req->idcustomer_order)
            //         ->where('idstore_warehouse', $custOrder->idstore_warehouse)
            //         ->update([
            //             'status' => 0,
            //             'updated_by' => $user->id
            //         ]);
            //     OrderDetail::where('idcustomer_order', $req->idcustomer_order)
            //         ->update([
            //             'status' => 0,
            //             'updated_by' => $user->id
            //         ]);
            // } else {
            // 
            //throw new Exception("Part cancel not supported.");
            foreach ($req->order_to_cancel as $itemToCancel) {
                $itemDetailDB = DB::table('order_detail')
                    ->join('product_master', 'order_detail.idproduct_master', '=', 'product_master.idproduct_master')
                    ->leftJoin('category', 'category.idcategory', '=', 'product_master.idcategory')
                    ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                    ->select(
                        'brands.name AS brand',
                        'product_master.name AS prod_name',
                        'product_master.description AS prod_desc',
                        'product_master.barcode',
                        'product_master.hsn',
                        'product_master.sgst AS sgst_percent',
                        'product_master.cgst AS cgst_percent',
                        'category.name AS category',
                        'category.has_return_rule',
                        'category.return_type',
                        'category.return_duration',
                        'category.idcategory',
                        'order_detail.*'
                    )
                    ->where('order_detail.idcustomer_order', $itemToCancel->idcustomer_order)
                    ->where('idorder_detail', $itemToCancel->idorder_detail)
                    ->where('order_detail.status', 1)
                    ->first();

                if (!isset($itemDetailDB) || !$itemDetailDB->idorder_detail) {
                    throw new Exception("Item is already cancelled");
                }

                // return response()->json(["statusCode" => 0, "message" => $itemDetailDB->quantity], 200);
                $cancelAllQty = false;
                $qtytoCancel = 0;
                if (isset($itemToCancel->cancel_quantity)) {
                    //part quantity cancel
                    if ($itemToCancel->cancel_quantity == 0) {
                        continue;
                    } else if ($itemDetailDB->quantity < $itemToCancel->cancel_quantity) {
                        $qtytoCancel = $itemDetailDB->quantity;
                    } else if ($itemDetailDB->quantity == $itemToCancel->cancel_quantity) {
                        $cancelAllQty = true;
                        $qtytoCancel = $itemDetailDB->quantity;
                    } else if ($itemDetailDB->quantity < $itemToCancel->cancel_quantity) {
                        $cancelAllQty = true;
                        $qtytoCancel = $itemDetailDB->quantity;
                    }
                } else {
                    $cancelAllQty = true;
                    $qtytoCancel = $itemDetailDB->quantity;
                }
                if ($itemDetailDB->part_of_pkg == 1) {
                    throw new Exception("Item part of package and cancel not be cancelled.");
                }

                if ($itemDetailDB->has_return_rule == 'Y' && $daysPassed < $itemDetailDB->return_duration) {

                    if ($itemDetailDB->return_type == 'RET' || $itemDetailDB->return_type == 'EXCH') {
                        if ($qtytoCancel == $itemDetailDB->quantity) {
                            OrderDetail::where('idorder_detail', $itemToCancel->idorder_detail)
                                ->update([
                                    'status' => 0,
                                    'updated_by' => $user->id,
                                    'remark' => "Item cancelled"
                                ]);
                        } else {
                        }
                        $cancelItemsAmount += $itemDetailDB->total_price;
                        CustomerOrder::where('idcustomer_order', $itemToCancel->idcustomer_order)
                            ->where('idstore_warehouse', $custOrder->idstore_warehouse)
                            ->update([
                                'total_quantity' => DB::raw('total_quantity - ' . $itemDetailDB->quantity),
                                'total_price' => DB::raw('total_price - ' . $itemDetailDB->total_price),
                                'total_cgst' => DB::raw('total_cgst - ' . $itemDetailDB->total_cgst),
                                'total_sgst' => DB::raw('total_sgst - ' . $itemDetailDB->total_sgst),
                                // 'discount' => DB::raw('discount - ' . $itemDetailDB->discount),
                                'updated_by' => $user->id
                            ]);
                        DB::table('inventory')
                            ->where('idproduct_master', $itemDetailDB->idproduct_master)
                            ->where('idstore_warehouse', $custOrder->idstore_warehouse)
                            ->update([
                                'quantity' => DB::raw('quantity + ' . $itemDetailDB->quantity)
                            ]);
                    }

                    switch ($itemDetailDB->return_type) {
                        case 'RET':
                            $mplans = DB::table('membership_plan')->where('status', 1)->get();
                            $planComms = [];
                            foreach ($mplans as $mplan) {
                                $mplan->wallet_amount = ($itemDetailDB->total_price * $mplan->commission) / 100;
                                array_push($planComms, $mplan);
                            }
                            foreach ($planComms as $planComm) {
                                if ($planComm->instant_discount == 1) {
                                    continue;
                                }
                                WalletTransaction::create([
                                    'idcustomer' => $custOrder->idcustomer,
                                    'type' => 'Refund',
                                    'idmembership_plan' => $planComm->idmembership_plan,
                                    'ref_id' => $itemToCancel->idcustomer_order,
                                    'amount' => $planComm->wallet_amount,
                                    'transaction_type' => 'DR',
                                    'remark' => "Items Cancelled",
                                    'created_by' => $user->id,
                                    'updated_by' => $user->id,
                                    'status' => 1
                                ]);
                                DB::table('wallet_balance')
                                    ->where('idcustomer', $custOrder->idcustomer)
                                    ->where('idmembership_plan',  $planComm->idmembership_plan)
                                    ->update([
                                        'current_amount' => DB::raw('current_amount - ' . $planComm->wallet_amount),
                                        'total_incurred' => DB::raw('total_incurred - ' . $planComm->wallet_amount)
                                    ]);
                            }
                            break;
                        case 'EXCH':
                            if (!$customerLoggedIn) {
                                if (!isset($req->contact)) {
                                    throw new Exception("No number linked to order, please provide to continue");
                                } else {
                                    $dx =
                                        DB::table('users')
                                        ->where('users.user_type', 'C')
                                        ->where('users.contact', $req->contact)
                                        ->first();
                                    if (!!$dx) {
                                        $customerLoggedIn = true;
                                        $idCustomer = $dx->id;
                                    } else {
                                        $newPass = base64_encode(random_bytes(8));
                                        $user = User::create(
                                            [
                                                'name' => "",
                                                'email' => "",
                                                'password' => bcrypt($newPass),
                                                'contact' => $req->contact,
                                                'user_type' => 'C',
                                                'idmembership_plan' => 1,
                                                'created_by' => -1,
                                                'updated_by' => -1,
                                                'status' => 1
                                            ]
                                        );
                                        $idCustomer = $user->id;
                                        $customerLoggedIn = true;
                                        $mplans = DB::table('membership_plan')
                                            ->where('status', 1)
                                            ->get();
                                        foreach ($mplans as $mplan) {
                                            WalletBalance::create([
                                                'idcustomer' => $user->id,
                                                'idmembership_plan' => $mplan->idmembership_plan,
                                                'current_amount' => 0,
                                                'total_incurred' => 0,
                                                'redeemed' => 0,
                                                'created_by' => -1,
                                                'updated_by' => -1,
                                                'status' => 1
                                            ]);
                                        }
                                        WalletBalance::create([
                                            'idcustomer' => $user->id,
                                            'idmembership_plan' => 0,
                                            'current_amount' => 0,
                                            'total_incurred' => 0,
                                            'redeemed' => 0,
                                            'created_by' => -1,
                                            'updated_by' => -1,
                                            'status' => 1
                                        ]);
                                    }
                                }
                            } else {
                                $idCustomer = $custOrder->idcustomer;
                            }
                            WalletTransaction::create([
                                'idcustomer' => $idCustomer,
                                'type' => 'Exchange',
                                'ref_id' => $itemToCancel->idorder_detail,
                                'amount' => $cancelItemsAmount,
                                'transaction_type' => 'CR',
                                'idmembership_plan' => 0,
                                'remark' => "Exchange of item from order",
                                'created_by' => -1,
                                'updated_by' => -1,
                                'status' => 1
                            ]);

                            DB::table('wallet_balance')
                                ->where('idcustomer', $idCustomer)
                                ->where('idmembership_plan',  0)
                                ->update([
                                    'current_amount' => DB::raw('current_amount + ' . $cancelItemsAmount),
                                    'total_incurred' => DB::raw('total_incurred + ' . $cancelItemsAmount)
                                ]);
                            break;
                        default:
                            throw new Exception("Type of return not defined.");
                            break;
                    }
                }
            }
            // }

            // if ((isset($custOrder->idcustomer) && $custOrder->idcustomer > 0) && (count($custOrderDetail) == count($req->order_to_cancel))) {
            //     $wallTans = DB::table('wallet_transaction')
            //         ->join('membership_plan', 'wallet_transaction.idmembership_plan', '=', 'membership_plan.idmembership_plan')
            //         ->select(
            //             'membership_plan.name as membership_type',
            //             'membership_plan.instant_discount',
            //             'membership_plan.commission',
            //             'wallet_transaction.*'
            //         )
            //         ->where('idcustomer', $custOrder->idcustomer)
            //         ->where('type', 'Purchase')
            //         ->where('ref_id',  $req->idcustomer_order)
            //         ->where('transaction_type',  'CR')
            //         ->get();

            //     foreach ($wallTans as $wallTan) {
            //         if ($wallTan->instant_discount == 0) {
            //             WalletTransaction::create([
            //                 'idcustomer' => $custOrder->idcustomer,
            //                 'type' => 'Refund',
            //                 'idmembership_plan' => $wallTan->idmembership_plan,
            //                 'ref_id' => $req->idcustomer_order,
            //                 'amount' => $wallTan->amount,
            //                 'transaction_type' => 'DR',
            //                 'remark' => "Order or Items Cancelled",
            //                 'created_by' => $user->id,
            //                 'updated_by' => $user->id,
            //                 'status' => 1
            //             ]);
            //             DB::table('wallet_balance')
            //                 ->where('idcustomer', $custOrder->idcustomer)
            //                 ->where('idmembership_plan',  $wallTan->idmembership_plan)
            //                 ->update([
            //                     'current_amount' => DB::raw('current_amount - ' . $wallTan->amount),
            //                     'total_incurred' => DB::raw('total_incurred - ' . $wallTan->amount)
            //                 ]);
            //         } else {
            //             WalletTransaction::where('idwallet_transaction', $wallTan->idwallet_transaction)->update(['status' => 0, 'remark' => "Order Cancelled."]);
            //             DB::table('wallet_balance')
            //                 ->where('idcustomer', $custOrder->idcustomer)
            //                 ->where('idmembership_plan',  $wallTan->idmembership_plan)
            //                 ->update([
            //                     'total_incurred' => DB::raw('total_incurred - ' . $wallTan->amount),
            //                     'redeemed' => DB::raw('redeemed - ' . $wallTan->amount)
            //                 ]);
            //         }
            //     }
            // }

            // if ($custOrder->discount_type == 'DFD') {
            //     Wallet::create([
            //         'idcustomer' => ($custOrder->idcustomer) ? $custOrder->idcustomer : 0,
            //         'type' => 'Cancel',
            //         'ref_id' => $custOrder->idcustomer_order,
            //         'amount' => 0,
            //         'transaction_type' => 'DR',
            //         'remark' => "Retured Goods With Instant Discount - " . $req->extraDisc,
            //         'created_by' => $user->id,
            //         'updated_by' => $user->id,
            //         'status' => 1
            //     ]);
            // }
            DB::commit();
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            DB::rollback();
            return response()->json(["statusCode" => 1, "message" => $e->getMessage(), "err" => $e->getTraceAsString()], 200);
        }
    }

    public function changePayMode(Request $request)
    {
        $req = json_decode($request->getContent());
        try {

            $user = auth()->guard('api')->user();

            if ($user->user_type == 'C') {
                $cord = DB::table('customer_order')
                    ->leftJoin('counter', 'customer_order.idcounter', '=', 'counter.idcounter')
                    ->leftJoin('users', 'customer_order.created_by', '=', 'users.id')
                    ->select(
                        'counter.idcounter',
                        'counter.name AS counterName',
                        'users.id as staffId',
                        'users.name as staffName',
                        'users.user_type',
                        'customer_order.*'
                    )
                    ->where('users.user_type', $user->user_type)
                    ->where('customer_order.idcustomer_order', $req->idcustomer_order)
                    ->where('users.id', $user->id)
                    ->first();
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

                $cord = DB::table('customer_order')
                    ->where('customer_order.idcustomer_order', $req->idcustomer_order)
                    ->where('idstore_warehouse', $userAccess->idstore_warehouse)
                    ->first();
            } else {
                throw new Exception("Invalid Access");
            }

            if (strtolower($req->pay_mode) != 'cash') {
                throw new Exception("Order can only be converted in CASH mode.");
            }
            if ($cord->pay_mode == 'cash') {
                throw new Exception("Order is alreaady in Cash Payment Mode.");
            }
            if ($cord->status == 0) {
                throw new Exception("Order is alreaady cancelled");
            }
            if ($cord->status > 4) { //Order is prepared
                throw new Exception("Order is prepared or out for delivery or delivered.");
            }

            CustomerOrder::where('idcustomer_order', $req->idcustomer_order)
                ->update([
                    'pay_mode' => 'cash'
                ]);

            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            return response()->json($e->getTrace(), 403);
        }
    }
    
     public function getOnlineOrder()
    {
        $user = auth()->guard('api')->user();
        if($user){
            $userAccess = DB::table('staff_access')
                        ->leftJoin('store_warehouse', 'staff_access.idstore_warehouse', '=', 'store_warehouse.idstore_warehouse')
                        ->select(
                            'staff_access.idstore_warehouse',
                            'staff_access.idstaff_access',
                            'store_warehouse.is_store',
                            'staff_access.idstaff'
                        )
                        ->where('staff_access.idstaff', $user->id)
                        ->first();
            $idstore_warehouse = $userAccess->idstore_warehouse;
            
            $order = Customer_order::where('idstore_warehouse', $idstore_warehouse)->where('is_online', 1)->orderBy('idcustomer_order','desc')->get();
            $i=0;
            $orderData=[];
            foreach($order as $o){
                $orderData[$i]=$o;
                $orderDetails = order_detail::where('idcustomer_order', $o->idcustomer_order)->get();
            
                $productQuery = Helper::prepareProductQuery();
                $Products = $productQuery->leftJoin('order_detail','product_master.idproduct_master','=','order_detail.idproduct_master')
                ->selectRaw('order_detail.*,product_master.idbrand,brands.name AS brand,product_master.idproduct_master,product_master.idcategory,category.name AS category,product_master.idsub_category,sub_category.name AS scategory,product_master.idsub_sub_category,sub_sub_category.name AS sscategory,product_master.name AS prod_name,product_master.description,
                product_master.barcode,product_master.hsn')
                ->where('inventory.idstore_warehouse', $o->idstore_warehouse)
                ->where('order_detail.idcustomer_order', $o->idcustomer_order)
                ->get();

                $orderData[$i]['order_detail']=$Products;
                
                $i++;
            }
            return response()->json([
                'statusCode' => '0',
                'message' => 'success',
                'data'=>$orderData
            ]);
        }else{
            return response()->json([
                'statusCode' => '1',
                'message' => 'user authentication required'
            ]);
        }
    }
    
      public function updateOrderStatus(Request $request)
    {
        $validator = \Validator::make($request->all(),[
            'status' => 'required',
            'idcustomer_order' => 'required',
            'updated_by'=>'required'
        ]);        
        if ($validator->fails()) { 
            $errors = $validator->errors();
            return response()->json([
                'statusCode' => '1',
                'message' => 'All fields are required',
                'data' => $errors->toJson()
            ]);
        }

        try{            
            $updateOrdStatus = DB::table('customer_order')->where('idcustomer_order',$request->idcustomer_order)->update([
                'status'=>trim($request->status),'updated_by'=>trim($request->updated_by),'updated_at' => date('Y-m-d H:i:s')
            ]);

            // enable if update status of order details also
            // $updateOrdDetStatus = DB::table('order_detail')->where('idcustomer_order',$request->idcustomer_order)->update([
            //     'status'=>trim($request->status),'updated_by'=>trim($request->updated_by),'updated_at' => date('Y-m-d H:i:s')
            // ]);
            
            return response()->json([
                'statusCode' => '0',
                'message' => 'success'
            ]);
        }
        catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update order status', 'details' => $e->getMessage()], 500);
        }
    }
}
