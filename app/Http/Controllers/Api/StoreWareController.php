<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\Models\StoreWare;
use App\Models\DeliverySlots;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class StoreWareController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $storeWare = StoreWare::latest()->paginate(25);

        return $storeWare;
    }

    public function getByType(Request $request)
    {
        $req = json_decode($request->getContent());

        $storeWare = StoreWare::where('is_store', $req->is_store)->latest()->paginate(25);

        return $storeWare;
    }

    public function getExceptMine(Request $request)
    {
        $req = json_decode($request->getContent());
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
        try {
            $storeWare = StoreWare::where('idstore_warehouse', '!=', $userAccess->idstore_warehouse)
                ->orderBy('name', 'ASC');
            return response()->json(["statusCode" => 0, "message" => "Success", "data" => $storeWare->get()], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 403);
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

        $storeWare = StoreWare::create($request->all());

        return response()->json($storeWare, 201);
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
        $storeWare = StoreWare::findOrFail($id);

        return $storeWare;
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

        $storeWare = StoreWare::findOrFail($id);
        $storeWare->update($request->all());

        return response()->json($storeWare, 200);
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
        StoreWare::destroy($id);

        return response()->json(null, 204);
    }
    
    public function warehouseList(){
        $list = DB::table('store_warehouse')->select('idstore_warehouse','name')->where('is_store','!=',1)->get();
        return response()->json($list);
    }
    
     public function storeList(){
        $list = DB::table('store_warehouse')->select('idstore_warehouse','name')->where('is_store','=',1)->get();
        return response()->json($list);
    }

    public function getStores($lat, $long) //Added LatLong Condition
    {
        try {
            if (($lat > -180 && $lat < 180) && ($long > -90 && $long < 90)) {
                $storeWare = DB::select('SELECT 
                *
            FROM
                (SELECT 
                    *,
                        (ST_DISTANCE_SPHERE(POINT(`lat`, `long`), POINT(?, ?))) AS distance_in_m
                FROM
                    store_warehouse WHERE is_store = 1 AND status = 1) AS tb
            WHERE
            tb.distance_in_m < (service_distance * 1000)', [$lat, $long]);
                return response()->json(["statusCode" => 0, "message" => "Success", "data" => $storeWare], 200);
            } else {
                return response()->json(["statusCode" => 1, "message" => "Co-ordinated are invalid"], 200);
            }
        } catch (Exception $e) {
            return response()->json(["statusCode" => 1, "message" => "Error", "err" => $e->getMessage()], 200);
        }
    }

    public function getDeliverySlot($storeId)
    {
        try {
            $store = StoreWare::where('idstore_warehouse', $storeId)->where('is_store', 1)->first();
            if (!isset($store->idstore_warehouse)) {
                throw new Exception("Invalid Store ID.");
            }
            if ($store->support_delivery == 0) {
                throw new Exception("Currently this store is not supporting delivery.");
            }
            $deliverySlots = DeliverySlots::where('idstore_warehouse', $storeId)
                ->whereBetween('date', [Carbon::today(), Carbon::now()->addDays($store->advance_delivery_day)])
                ->where('status', 1)
                ->get();
            $presentDates = [];
            foreach ($deliverySlots as $slot) {
                $presentDates[$slot->date] = 1;
            }
            $isMisMatch = false;
            if (count($presentDates) != $store->advance_delivery_day) {
                $isMisMatch = true;
                $startDate = Carbon::now()->addDays(count($presentDates) - 1);
                for ($j = 1; $j <= $store->advance_delivery_day - count($presentDates); $j++) {
                    $today = Carbon::now();
                    $startDate->addDays(1);
                    $dt = Carbon::parse($startDate);
                    $open_time = strtotime($store->slot_time_start);
                    $close_time = strtotime($store->slot_time_end);
                    $now = time();
                    $tslots = [];
                    $slots = [];
                    for ($i = $open_time; $i < $close_time; $i += $store->slot_duration * 60 * 60) {
                        //if ($i < $now) continue;
                        $eTime = $i + $store->slot_duration * 60 * 60;
                        if ($eTime > $close_time) {
                            break;
                        }
                        DeliverySlots::insert([
                            "idstore_warehouse" => $store->idstore_warehouse,
                            "date" =>  $dt->format('Y-m-d'),
                            "is_servicable" => 1,
                            "slot_time_start" => date("H:i", $i),
                            "slot_time_end" => date("H:i", $eTime),
                            "max_orders" => $store->max_orders,
                            "available_slots" => $store->max_orders,
                            "created_by" => -1,
                            "updated_by" => -1,
                            "status" => 1
                        ]);
                    }
                }
            }
            if ($isMisMatch) {
                $deliverySlots = DeliverySlots::where('idstore_warehouse', $storeId)
                    ->whereBetween('date', [Carbon::today(), Carbon::now()->addDays($store->advance_delivery_day)])
                    ->where('status', 1)
                    ->get();
            }
            $slts = [];
            $now = time();
            foreach ($deliverySlots as $cSlot) {
                if ($cSlot->max_orders == 0) {
                    continue;
                }
                if (date($cSlot->date) == Carbon::now()->toDateString()) {
                    if (strtotime($cSlot->slot_time_start) < $now) continue;
                }
                $slts[$cSlot->date][] = [
                    "iddelivery_slots" => $cSlot->iddelivery_slots,
                    "start" => date("h:i a", strtotime($cSlot->slot_time_start)),
                    "end" => date("h:i a", strtotime($cSlot->slot_time_end)),
                    "available_slots" => $cSlot->available_slots
                ];
            }
            $res = [];
            foreach ($slts as $key => $aSlot) {
                $res[] = ["date" => $key, "slots" => $aSlot];
            }
            return response()->json(["statusCode" => 0, "message" => 'success', "data" => $res], 200);
        } catch (Exception $e) {
            return response()->json($e->getMessage(), 403);
        }
    }
}
