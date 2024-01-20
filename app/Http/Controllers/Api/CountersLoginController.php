<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\CountersLogin;
use Illuminate\Http\Request;

use function PHPUnit\Framework\throwException;

class CountersLoginController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $counterslogin = CountersLogin::latest()->paginate(25);

        return $counterslogin;
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

        $counterslogin = CountersLogin::create($request->all());

        return response()->json($counterslogin, 201);
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
        $counterslogin = CountersLogin::findOrFail($id);

        return $counterslogin;
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

        $counterslogin = CountersLogin::findOrFail($id);
        $counterslogin->update($request->all());

        return response()->json($counterslogin, 200);
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
        CountersLogin::destroy($id);

        return response()->json(null, 204);
    }

    public function open(Request $request)
    {

        $req = json_decode($request->getContent());
        $customErr = "";
        try {
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
                ->where('store_warehouse.is_store', 1)
                ->where('store_warehouse.idstore_warehouse', $req->idstore_warehouse)
                ->first();

            if (isset($userAccess->idstore_warehouse)) {
                $currStatus = CountersLogin::where('idcounter', $req->idcounter)->where('status', 1)->first();
                if (isset($currStatus->idcounters_login)) {
                    if ($currStatus->idstaff != $user->id) {
                        $det = $currStatus;
                        $customErr = "Already Logged in by some user.";
                        throw new Exception("Already Logged in by some user.");
                    } else {
                        $det = $currStatus;
                    }
                } else {
                    $q = [
                        'idcounter' => $req->idcounter,
                        'idstaff' => $user->id,
                        'open_balance' => $req->total,
                        'close_balance' => $req->total,
                        'open_cash_detail' => json_encode($req->cashDet),
                        'close_cash_detail' => null,
                        'online_payments' => null,
                        'created_by' => $user->id,
                        'status' => 1,
                        'od_1' => $req->cashDet->n1,
                        'od_2' => $req->cashDet->n2,
                        'od_5' => $req->cashDet->n5,
                        'od_10' => $req->cashDet->n10,
                        'od_20' => $req->cashDet->n20,
                        'od_50' => $req->cashDet->n50,
                        'od_100' => $req->cashDet->n100,
                        'od_200' => $req->cashDet->n200,
                        'od_500' => $req->cashDet->n500,
                        'od_2000' => $req->cashDet->n2000,
                        'cd_1' => $req->cashDet->n1,
                        'cd_2' => $req->cashDet->n2,
                        'cd_5' => $req->cashDet->n5,
                        'cd_10' => $req->cashDet->n10,
                        'cd_20' => $req->cashDet->n20,
                        'cd_50' => $req->cashDet->n50,
                        'cd_100' => $req->cashDet->n100,
                        'cd_200' => $req->cashDet->n200,
                        'cd_500' => $req->cashDet->n500,
                        'cd_2000' => $req->cashDet->n2000,
                    ];
                    $det = CountersLogin::create($q);
                }
            } else {
                $det = 0;
                $customErr = "User don't have access.";
                throw new Exception("User don't have access.");
            }
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $det], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => $customErr, "err" => $e->getMessage()], 200);
        }
    }


    public function close(Request $request)
    {

        $req = json_decode($request->getContent());
        $customErr = "";
        try {
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
                ->where('store_warehouse.is_store', 1)
                ->where('store_warehouse.idstore_warehouse', $req->idstore_warehouse)
                ->first();

            if (isset($userAccess->idstore_warehouse)) {
                $currStatus = CountersLogin::where('idcounter', $req->idcounter)->where('idstaff', $user->id)->where('status', 1)->first();
                if (isset($currStatus->idcounters_login)) {
                    if ($currStatus->status === 0) {
                        $customErr = "Already logged-out.";
                        throw new Exception("Already logged-out.");
                    } else {
                        $q = [
                            'close_balance' => $req->total,
                            'close_cash_detail' => json_encode($req->cashDet),
                            'online_payments' => null,
                            'status' => 0,
                            'cd_1' => $req->cashDet->n1,
                            'cd_2' => $req->cashDet->n2,
                            'cd_5' => $req->cashDet->n5,
                            'cd_10' => $req->cashDet->n10,
                            'cd_20' => $req->cashDet->n20,
                            'cd_50' => $req->cashDet->n50,
                            'cd_100' => $req->cashDet->n100,
                            'cd_200' => $req->cashDet->n200,
                            'cd_500' => $req->cashDet->n500,
                            'cd_2000' => $req->cashDet->n2000
                        ];
                        CountersLogin::where('idcounter', $req->idcounter)
                            ->where('idstaff', $user->id)
                            ->where('idcounters_login', $currStatus->idcounters_login)
                            ->update($q);
                    }
                } else {
                    $customErr = "Unauthorized Access.";
                    throw new Exception("Unauthorized Access.");
                }
            } else {
                $customErr = "User don't have access.";
                throw new Exception("User don't have access.");
            }
            return response()->json(["statusCode" => 0, "message" => "Success"], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => $customErr, "err" => $e->getMessage()], 200);
        }
    }

    public function getLoginDetail(Request $request)
    {
        try {
            $user = auth()->guard('api')->user();
            if(!isset($user->id)){
                throw new Exception("Invalid Login.");
            }
            $userAccess = DB::table('staff_access')
                ->join('store_warehouse', 'staff_access.idstore_warehouse', '=', 'store_warehouse.idstore_warehouse')
                ->select(
                    'staff_access.idstore_warehouse',
                    'staff_access.idstaff_access',
                    'store_warehouse.is_store',
                    'staff_access.idstaff'
                )
                ->where('staff_access.idstaff', $user->id)
                ->where('store_warehouse.is_store', 1)
                ->first();

                $det = DB::table('counter')
                ->join('counters_login', 'counter.idcounter', '=', 'counters_login.idcounter')
                ->select(
                    'counter.idstore_warehouse',
                    'counters_login.*',
                )
                ->where('counter.idstore_warehouse', $userAccess->idstore_warehouse)
                ->where('counters_login.idstaff', $user->id)
                ->where('counters_login.status', 1)
                ->first();
                // $currStatus = CountersLogin::where('idstaff', $user->id)
                // ->where('status', 1)
                // ->first();
            
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $det], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

}
