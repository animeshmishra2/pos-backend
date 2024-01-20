<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Counter;
use App\Models\CountersLogin;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
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
            ->first();
        //print_r($userAccess);die;
        // $counter = DB::table('counter')
        //     ->leftJoin('counters_login', 'counter.idcounter', '=', 'counters_login.idcounter')
        //     ->select(
        //         'counter.*',
        //         'counters_login.idstaff',
        //         'counters_login.open_balance',
        //         'counters_login.close_balance',
        //         'counters_login.open_cash_detail',
        //         'counters_login.close_cash_detail',
        //         'counters_login.online_payments',
        //         'counters_login.od_1',
        //         'counters_login.od_2',
        //         'counters_login.od_5',
        //         'counters_login.od_10',
        //         'counters_login.od_20',
        //         'counters_login.od_50',
        //         'counters_login.od_100',
        //         'counters_login.od_200',
        //         'counters_login.od_500',
        //         'counters_login.od_2000',
        //         'counters_login.cd_1',
        //         'counters_login.cd_2',
        //         'counters_login.cd_5',
        //         'counters_login.cd_10',
        //         'counters_login.cd_20',
        //         'counters_login.cd_50',
        //         'counters_login.cd_100',
        //         'counters_login.cd_200',
        //         'counters_login.cd_500',
        //         'counters_login.cd_2000'
        //     )
        //     ->where('counter.idstore_warehouse', $userAccess->idstore_warehouse)
        //     ->orderBy('counters_login.updated_at DESC')
        //     ->get();
        $counter = Counter::where('idstore_warehouse', $userAccess->idstore_warehouse)->latest()->paginate(25);
        foreach ($counter as $currCount) {
            $currCount['last_login'] = CountersLogin::where('idcounter', $currCount->idcounter)
                // ->orderBy('counters_login.updated_at DESC')
                ->latest()->first();
        }
        return $counter;
    }


    public function getAllCounterBySW($id)
    {
        try {
            $user = auth()->guard('api')->user();
            $counter = Counter::where('idstore_warehouse', $id)->get();
            // $counter = DB::table('counter')
            //     ->leftJoin('counters_login', 'counters_login.idcounter', '=', 'counter.idcounter')
            //     ->select(
            //         'counters_login.created_at AS open_from',
            //         'counters_login.idstaff',
            //         'counter.*'
            //     )
            //     ->where('counters_login.status', 1)
            //     ->where('counter.idstore_warehouse', $id)
            //     ->toSql();

            $ret = [];
            foreach ($counter as $cou) {
                $ctr = DB::table('counters_login')
                    ->where('status', 1)
                    ->where('idcounter', $cou['idcounter'])
                    ->first();

                $cou->open_at = !!$ctr ? $ctr->created_at : 0;
                $cou->idstaff = !!$ctr ? $ctr->idstaff : 0;
                $ret[] = $cou;
            }

            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $ret], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => $e->getMessage()], 200);
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
        $user = auth()->guard('api')->user();
        $r = [
            'idstore_warehouse' => $req->idstore_warehouse,
            'name' =>  $req->name,
            'live_status' => null,
            'created_by' => $user->id,
            'status' => $req->status
        ];
        $counter = Counter::create($r);

        return response()->json(["statusCode" => 0, "message" => "Success"], 200);
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
        $counter = Counter::findOrFail($id);

        return $counter;
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
        $user = auth()->guard('api')->user();
        $r = [
            'idstore_warehouse' => $req->idstore_warehouse,
            'name' =>  $req->name,
            'live_status' => null,
            'created_by' => $user->id,
            'status' => $req->status
        ];

        Counter::where('idcounter', $req->idcounter)->update($r);

        return response()->json(["statusCode" => 0, "message" => "Success"], 200);
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
        Counter::destroy($id);

        return response()->json(null, 204);
    }

    public function getSWCounter($id)
    {
        try {
            $counter = Counter::where('idstore_warehouse', $id)->latest();
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $counter], 200);
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }
}
