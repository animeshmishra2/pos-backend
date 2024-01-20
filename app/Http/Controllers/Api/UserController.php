<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CountersLogin;
use App\Models\User;
use App\Models\StaffAccess;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $staff = User::where('user_type', 'ST')->latest()->paginate(50);
        return $staff;
    }

    public function getUserBySW($swId)
    {
        try {
            $rec = DB::table('users')
                ->leftJoin('staff_access', 'users.id', '=', 'staff_access.idstaff')
                ->select(
                    'users.name',
                    'users.email',
                    'users.contact',
                    'staff_access.*'
                )
                ->where('staff_access.idstore_warehouse', $swId)
                ->where('users.user_type', 'ST')
                ->orderBy('name', 'ASC')
                ->get();
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $rec], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
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
        if ($req->password == '' ||  $req->password == null) {
            $req->password = $req->contact;
        }
        $req->password = bcrypt($req->password);
        $req->status = 1;
        $req->user_type = 'ST';
        $staff = User::create((array) $req);
        return response()->json($staff, 201);
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
        $req = json_decode($request->getContent());
        $staff = User::findOrFail($id);

        if ($req->password == '') {
            $req->password = $staff->password;
        } else {
            $req->password = bcrypt($req->password);
        }
        $req->user_type = 'ST';

        if ($staff->user_type === 'ST') {
            $staff->update((array) $req);
        }
        return response()->json($staff, 200);
    }

    public function addStaffAccess(Request $request)
    {
        $req = json_decode($request->getContent());
        try {
            $user = auth()->guard('api')->user();
            $rec = DB::table('staff_access')
                ->leftJoin('store_warehouse', 'store_warehouse.idstore_warehouse', '=', 'staff_access.idstore_warehouse')
                ->select(
                    'store_warehouse.name',
                    'store_warehouse.is_store',
                    'staff_access.*'
                )
                ->where('staff_access.status', 1)
                ->where('staff_access.idstaff', $req->idstaff)
                ->first();
            if (!isset($rec->idstaff_access)) {
                $r = [
                    'idstaff' =>  $req->idstaff,
                    'idstore_warehouse' =>  $req->idstore_warehouse,
                    'idaccess_level' =>  1,
                    'created_by' => $user->id,
                    'status' => 1
                ];
                StaffAccess::create($r);
            } else {
                $er = "Staff already has access to ";
                if ($rec->is_store == 1) {
                    $er .= 'Store: ';
                } else {
                    $er .= 'Warehouse: ';
                }
                $er .= $rec->name;
                throw new Exception($er);
            }
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

    public function updateAccess(Request $request)
    {
        $req = json_decode($request->getContent());
        // try {
        if ($req->store > 0) {
            $userAccess = DB::table('staff_access')
                ->join('store_warehouse', 'staff_access.idstore_warehouse', '=', 'store_warehouse.idstore_warehouse')
                ->select(
                    'staff_access.idstaff_access',
                    'store_warehouse.is_store',
                    'staff_access.idstaff'
                )
                ->where('staff_access.idstaff', $req->user_id)
                ->where('store_warehouse.is_store', 1)
                ->get();

            if (isset($userAccess[0]) && $userAccess[0]->idstaff_access > 0) {
                StaffAccess::where('idstaff_access', $userAccess[0]->idstaff_access)
                    ->where('idstaff', $req->user_id)
                    ->update(['idstore_warehouse' => $req->store]);
            } else {
                StaffAccess::create([
                    'idstaff' => $req->user_id,
                    'idstore_warehouse' => $req->store,
                    'idaccess_level' => 1,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }
        }
        if ($req->warehouse > 0) {
            $userAccess = DB::table('staff_access')
                ->join('store_warehouse', 'staff_access.idstore_warehouse', '=', 'store_warehouse.idstore_warehouse')
                ->select(
                    'staff_access.idstaff_access',
                    'store_warehouse.is_store',
                    'staff_access.idstaff'
                )
                ->where('staff_access.idstaff', $req->user_id)
                ->where('store_warehouse.is_store', 0)
                ->get();
            // print_r($userAccess);
            if (isset($userAccess[0]) && $userAccess[0]->idstaff_access > 0) {
                StaffAccess::where('idstaff_access', $userAccess[0]->idstaff_access)
                    ->where('idstaff', $req->user_id)
                    ->update(['idstore_warehouse' => $req->warehouse]);
            } else {
                StaffAccess::create([
                    'idstaff' => $req->user_id,
                    'idstore_warehouse' => $req->warehouse,
                    'idaccess_level' => 1,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }
        }
        return response()->json(true, 200);
        // } catch (Exception $e) {
        //     return response()->json($e->getTrace(), 403);
        // }
    }
    
    
        public function myyOrder(Request $request)
    {
         $req = json_decode($request->getContent());
        $user = auth()->guard('api')->user();
    try {
                $orderMaster = DB::table('customer_order')
                    ->leftJoin('users', 'customer_order.created_by', '=', 'users.id')
                    ->leftJoin('store_warehouse','store_warehouse.idstore_warehouse', '=', 'customer_order.idstore_warehouse');
                    if($req->valid_from !=null && $req->valid_till!=null){
                         $orderMaster->whereBetween(DB::raw('DATE(customer_order.created_at)'), array($req->valid_from, $req->valid_till))->where('customer_order.idcustomer',$user->id);
                    }else{
                        $orderMaster->where('customer_order.idcustomer',$user->id);
                    }
                   
                return response()->json(["statusCode" => 0, "message" => "Success", "data" => $orderMaster->get()], 200);
            }  catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "data" => $e->getMessage()], 200);
        }
    }
    public function myOrders(Request $request)
    {
        $req = json_decode($request->getContent());
        $user = auth()->guard('api')->user();
        $isDateSet = false;
try {
            $orderMaster = DB::table('customer_order')
                ->leftJoin('counter', 'customer_order.idcounter', '=', 'counter.idcounter')
                ->leftJoin('users', 'customer_order.created_by', '=', 'users.id')
                ->select(
                    'counter.idcounter',
                    'counter.name AS counterName',
                    'users.id as staffId',
                    'users.name as staffName',
                    'users.user_type',
                    'customer_order.*'
                );
                if (isset($req->order_number) && $req->order_number > 0) {
                    $orderMaster->where('customer_order.idcustomer_order', explode("/", $req->order_number)[1]);
                } 

                if ($user->user_type == 'C') {
                    $orderMaster->where('users.user_type', $user->user_type)->or;
                    $orderMaster->where('users.id', $user->id);
                } elseif ($user->user_type == 'ST') {
                    if ($req->idcounter > 0) {
                        $orderMaster->where('customer_order.idcounter', $req->idcounter);
                    } 
                }
    
                if (isset($req->order_type) && $req->order_type >= 0) {
                    $orderMaster->where('customer_order.is_online', $req->order_type);
                }
                if ($req->idcounter > 0) {
                    $orderMaster->where('customer_order.idcounter', $req->idcounter);
                } 

                if (isset($req->pay_mode) && $req->pay_mode =='cash') {
                    $orderMaster->where('customer_order.pay_mode', 'cash');
                }elseif(isset($req->pay_mode) && $req->pay_mode =='upi'){
                    $orderMaster->where('customer_order.pay_mode', 'upi');
                }elseif(isset($req->pay_mode) && $req->pay_mode =='card'){
                    $orderMaster->where('customer_order.pay_mode', 'card');
                }elseif(isset($req->pay_mode) && $req->pay_mode =='qr'){
                    $orderMaster->where('customer_order.pay_mode', 'qr');
                }else{
                    $orderMaster->orderBy('customer_order.pay_mode');
                }
    
            
            $orderMaster->whereBetween(DB::raw('DATE(customer_order.created_at)'), array($req->valid_from, $req->valid_till))->where('customer_order.status',1)->orderBy('customer_order.idcustomer_order', 'DESC');

            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $orderMaster->get()], 200);
        }  catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "data" => $e->getMessage()], 200);
        }
    }
    public function myOnlineOrders(Request $request)
    {
        $req = json_decode($request->getContent());
        $user = auth()->guard('api')->user();
        $isDateSet = false;

        try {
            $orderMaster = DB::table('customer_order')
                ->leftJoin('counter', 'customer_order.idcounter', '=', 'counter.idcounter')
                ->leftJoin('users', 'customer_order.created_by', '=', 'users.id')
                ->select(
                    'counter.idcounter',
                    'counter.name AS counterName',
                    'users.id as staffId',
                    'users.name as staffName',
                    'users.user_type',
                    'customer_order.*'
                );

            if (isset($req->order_number) && $req->order_number > 0) {
                $orderMaster->where('customer_order.idcustomer_order', explode("/", $req->order_number)[1]);
            } else {
                $orderMaster->whereBetween('customer_order.created_at', [$req->valid_from, $req->valid_till]);
            }

            
             $orderMaster->where('customer_order.is_pos', 0);
            
            if ($user->user_type == 'C') {
                $orderMaster->where('users.user_type', $user->user_type);
                $orderMaster->where('users.id', $user->id);
            } elseif ($user->user_type == 'ST') {
                if ($req->idcounter > 0) {
                  //  $orderMaster->where('customer_order.idcounter', $req->idcounter);
                } 
            }
            $orderMaster->orderBy('customer_order.idcustomer_order', 'DESC');


            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $orderMaster->get()], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "data" => $e->getMessage()], 200);
        }
    }

    public function updateDetails($swId)
    {
        try {
            $rec = DB::table('users')
                ->leftJoin('staff_access', 'users.id', '=', 'staff_access.idstaff')
                ->select(
                    'users.name',
                    'users.email',
                    'users.contact',
                    'staff_access.*'
                )
                ->where('staff_access.idstore_warehouse', $swId)
                ->where('users.user_type', 'ST')
                ->orderBy('name', 'ASC')
                ->get();
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $rec], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
    
      public function cities($id)
    {
        $cities =DB::table('cities')->where('state_id',$id)->get();
        return response()->json(["statusCode" => 1, "data" => $cities]);
    }

    public function states()
    {
        $states =DB::table('states')->get();
        return response()->json(["statusCode" => 1, "data" => $states]);
    }
}
