<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\WalletBalance;
use App\Models\CustomerAddress;
use App\Models\MembershipChangeReq;
use Illuminate\Http\Request;
use Exception;
use GrahamCampbell\ResultType\Success;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $customer = Customer::latest()->paginate(25);

        return [];
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

        // $customer = Customer::create($request->all());

        return [];
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
        // $customer = Customer::findOrFail($id);

        return [];
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

        // $customer = Customer::findOrFail($id);
        // $customer->update($request->all());

        return [];
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
        // Customer::destroy($id);

        return [];
    }

    public function getCustomerByContact($contact)
    {
        try {
            $user = auth()->guard('api')->user();
            $customer = User::where('contact', $contact)->where('user_type', 'C')->first();
            $data =
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
                ->where('users.user_type', 'C')
                ->where('wallet_balance.idcustomer', $customer->id)
                ->where('users.contact', $contact)
                ->first();

                $redWall = DB::table('wallet_balance')
                ->where('idcustomer', $customer->id)
                ->where('idmembership_plan',  0)
                ->first();    
                $data->redeemWallet = $redWall->current_amount;
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $data], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

    public function register(Request $request)
    {
        $req = json_decode($request->getContent());
        try {
            $authUser = auth()->guard('api')->user();
            $dx =
                DB::table('users')
                ->where('users.user_type', 'C')
                ->where('users.contact', $req->contact)
                ->first();
            if (!!$dx) {
                throw new Exception("User Already Registered.");
            }

            $newPass = base64_encode(random_bytes(8));
            $selectedPlan = (!!($req->membership) ? $req->membership : 1);
            $user = User::create(
                [
                    'name' => $req->name,
                    'email' => $req->email,
                    'password' => bcrypt($newPass),
                    'contact' => $req->contact,
                    'user_type' => 'C',
                    'idmembership_plan' => $selectedPlan ?? 1,
                    'created_by' => $authUser->id,
                    'updated_by' => $authUser->id,
                    'status' => 1
                ]
            );
            $mplans = DB::table('membership_plan')
                ->where('status', 1)
                ->get();
            $activePlan = [];
            foreach ($mplans as $mplan) {
                WalletBalance::create([
                    'idcustomer' => $user->id,
                    'idmembership_plan' => $mplan->idmembership_plan,
                    'current_amount' => 0,
                    'total_incurred' => 0,
                    'redeemed' => 0,
                    'created_by' => $authUser->id,
                    'updated_by' => $authUser->id,
                    'status' => 1
                ]);
                if ($mplan->idmembership_plan == $selectedPlan) {
                    $activePlan = $mplan;
                }
            }
            WalletBalance::create([
                'idcustomer' => $user->id,
                'idmembership_plan' => 0,
                'current_amount' => 0,
                'total_incurred' => 0,
                'redeemed' => 0,
                'created_by' => $authUser->id,
                'updated_by' => $authUser->id,
                'status' => 1
            ]);

            CustomerAddress::create(
                [
                    'idcustomer' => $user->id,
                    'name' => $req->name,
                    'address' => $req->address,
                    'pincode' => $req->pin,
                    'landmark' => $req->landmark,
                    'is_default' => 1,
                    'phone' => $req->contact,
                    'created_by' => $authUser->id,
                    'status' => 1
                ]
            );

            $res = [
                'idcustomer' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'contact' => $user->contact,
                'user_type' => 'C',
                'idmembership_plan' => $user->membership,
                'status' => 1,
                'membership_type' => $activePlan->name,
                'instant_discount' => $activePlan->instant_discount,
                'commission' => $activePlan->commission,
                'wallet_balance' => 0,
            ];
            //TODO Send SMS
            return response()->json(["statusCode" => 0, "message" => 'success', "data" => $res], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => $e->getMessage()], 200);
        }
    }
    public function getMembershipMaster()
    {
        $data =
            DB::table('membership_plan')
            ->select(
                'idmembership_plan',
                'name',
                'price',
                'description',
                'instant_discount',
                'is_popular',
                'commission'
            )
            ->where('status', 1)->get();
        return response()->json(["statusCode" => 0, "message" => "Success", "data" => $data], 200);
    }

    public function getPassbook(Request $request)
    {
        $req = json_decode($request->getContent());
        $user = auth()->guard('api')->user();
        try {

            $wallBals = DB::table('wallet_balance')
                ->leftJoin('membership_plan', 'wallet_balance.idmembership_plan', '=', 'membership_plan.idmembership_plan')
                ->select(
                    'membership_plan.name as membership_type',
                    'membership_plan.instant_discount',
                    'membership_plan.commission',
                    'wallet_balance.current_amount',
                    'wallet_balance.total_incurred',
                    'wallet_balance.idmembership_plan',
                    'wallet_balance.redeemed'
                )
                ->where('wallet_balance.idcustomer', $user->id)->get();

            foreach ($wallBals as $wall) {
                $wallTans = DB::table('wallet_transaction')
                    ->leftJoin('membership_plan', 'wallet_transaction.idmembership_plan', '=', 'membership_plan.idmembership_plan')
                    ->select(
                        'membership_plan.name as membership_type',
                        'membership_plan.instant_discount',
                        'membership_plan.commission',
                        'wallet_transaction.*'
                    )
                    ->where('wallet_transaction.idcustomer', $user->id)
                    ->where('wallet_transaction.idmembership_plan', $wall->idmembership_plan)
                    ->where('wallet_transaction.status', 1);

                if (!!$req->valid_from) {
                    $wallTans->whereBetween('wallet_transaction.created_at', [$req->valid_from, $req->valid_till]);
                }
                $wallTans->orderBy('wallet_transaction.idwallet_transaction', 'DESC');
                $wall->transactions = $wallTans->get();
            }



            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $wallBals], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 403);
        }
    }

    public function changeMembershipp(Request $request)
    {
        $req = json_decode($request->getContent());
        $user = User::where('contact',$req->contact)->first();
        $ud = auth()->guard('api')->user();
        try {
            if ($user->idmembership_plan == $req->idmembership_plan) {
                throw new Exception("Already member of plan.");
            }
            $mem = DB::table('membership_plan')
                ->select(
                    'idmembership_plan',
                    'name',
                    'price',
                    'description',
                    'instant_discount',
                    'is_popular',
                    'commission',
                    'status'
                )
                ->where('idmembership_plan', $req->idmembership_plan)->first();
            if (!isset($mem)) {
                throw new Exception("Invalid Membership Plan ");
            }
            if ($mem->status == 0) {
                throw new Exception("Membership Plan is no longer active.");
            }
            
            
           $reqdata =[
                  'id'=>$user->id,
                   'from_membership'=>$user->idmembership_plan?? 0,
                    'to_membership'=>$req->idmembership_plan,
                     'remark'=>'',
                     'created_by'=>$ud->id,
                      'created_by'=>$ud->id
                ];
            MembershipChangeReq::create($reqdata);
            $us = User::where('id', $user->id)
                ->where('user_type', 'C')
                ->update(['idmembership_plan' => $req->idmembership_plan]);

            return response()->json(["statusCode" => 0, "message" => 'Success'], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => $e->getMessage()], 200);
        }
    }

   //only for mobile app
    public function changeMembership(Request $request)
    {
        $req = json_decode($request->getContent());
        $user = auth()->guard('api')->user();
        try {
            if ($user->idmembership_plan == $req->idmembership_plan) {
                throw new Exception("Already member of plan.");
            }
            $mem = DB::table('membership_plan')
                ->select(
                    'idmembership_plan',
                    'name',
                    'price',
                    'description',
                    'instant_discount',
                    'is_popular',
                    'commission',
                    'status'
                )
                ->where('idmembership_plan', $req->idmembership_plan)->first();
            if (!isset($mem)) {
                throw new Exception("Invalid Membership Plan ID ");
            }
            if ($mem->status == 0) {
                throw new Exception("Membership Plan is no longer active.");
            }
            if ($mem->instant_discount == 1) {
                throw new Exception("Cannot change to Instant Discount plan.");
            }
            $us = User::where('id', $user->id)
                ->where('user_type', 'C')
                ->update(['idmembership_plan' => $req->idmembership_plan]);

            return response()->json(["statusCode" => 0, "message" => "success"], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => $e->getMessage()], 200);
        }
    }
}
