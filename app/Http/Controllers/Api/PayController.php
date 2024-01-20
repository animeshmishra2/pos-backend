<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\CustomerOrder;
use Exception;
use App\Models\PackageMaster;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\DB;

class PayController extends Controller
{
    public static $MERCHANT_KEY = "rzp_test_gZjoHc7y5ZLSlk";
    public static $SEC = "yP6AoWmeEdhFlIFWESpigBqq";

    public function preparePayment($ordId)
    {
        try {
            $user = auth()->guard('api')->user();
            $api = new Api(PayController::$MERCHANT_KEY, PayController::$SEC); //KS
            $transId = date('ymdhis') . '_' . rand(100, 9999);

            $cord = DB::table('customer_order')
                ->leftJoin('users', 'customer_order.idcustomer', '=', 'users.id')
                ->select(
                    'users.name',
                    'users.contact',
                    'users.email',
                    'users.idmembership_plan',
                    'customer_order.*'
                )
                ->where('customer_order.idcustomer_order', $ordId)
                ->where('users.id', $user->id)
                ->where('customer_order.is_online', 1)
                ->where('customer_order.is_paid', 0)
                ->first();

            if (!$cord) {
                throw new Exception("Order not found.");
            }

            $order  = $api->order->create(array(
                'receipt' => $transId,
                'amount' => $cord->total_price * 100,
                'currency' => 'INR',
                'payment_capture' =>  '1'
            ));
            
            $order_id = $order->id;

            $pay = Payment::create([
                'idcustomer_order' => $ordId,
                'txn_id' => $order_id,
                'payment_complete' => 0,
                'payment_gateway_res' => null,
                'gateway_status' => null,
                'log' => null,
                'created_at' => Carbon::now(),
                'created_by' => -1,
                'status' => 0 //0 pending 1 success 2 failure
            ]);

            return ["statusCode" => 0, "message" => "Success", "data" => $pay,"amount"=>$cord->total_price * 100,'online_detail'=>$order_id];
        } catch (Exception $e) {
            return ["statusCode" => 1, "message" => "Error while processing", "emsg" => $e->getMessage()];
        }
    }

    public function confirmPayment(Request $request)
    {
        try {
            DB::beginTransaction();
            $req = json_decode($request->getContent());
            $user = auth()->guard('api')->user();

            $api = new Api(PayController::$MERCHANT_KEY, PayController::$SEC);

            $attributes  = array(
                'razorpay_signature'  => $req->razorpay_signature,
                'razorpay_payment_id'  => $req->razorpay_payment_id,
                'razorpay_order_id' => $req->razorpay_order_id
            );

            $api->utility->verifyPaymentSignature($attributes);

            $loc = Payment::where([
                ['txn_id', '=', $req->razorpay_order_id]
            ])->first();
            //dd($loc);
            $cord = DB::table('customer_order')
                ->leftJoin('users', 'customer_order.idcustomer', '=', 'users.id')
                ->select(
                    'users.name',
                    'users.contact',
                    'users.email',
                    'users.idmembership_plan',
                    'customer_order.*'
                )
                ->where('customer_order.idcustomer_order', $loc->idcustomer_order)
                ->first();
            if (!$cord) {
                throw new Exception("Order not found.");
            }
            Payment::where(
                [
                    ['idcustomer_order', '=', $loc->idcustomer_order],
                    ['txn_id', '=', $req->razorpay_order_id]
                ]
            )->update([
                'payment_complete' => 1,
                'payment_gateway_res' => 'SUCCESS',
                'log' => $request->getContent(),
            ]);

            CustomerOrder::where(
                [
                    ['idcustomer_order', '=', $loc->idcustomer_order]
                ]
            )->update([
                'is_paid' => 1,
                'is_paid_online' => 1,
                'pay_mode_ref' => $request->getContent(),
            ]);

            DB::commit();
            return ["statusCode" => 0, "message" => "Success"];
        } catch (Exception $e) {
            DB::rollBack();
            return ["statusCode" => 1, "message" => "Payment Unauthenticated.", "emsg" => $e->getMessage()];
        }
    }
}
