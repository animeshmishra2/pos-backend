<?php

namespace App\Http\Controllers\API;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use  App\Helpers\Helper;
use App\Models\ProductBatch;
use App\Models\AutoTransferRequest;
use App\Models\AutoTransferRequestDetail;

class InventoryThresholdController extends Controller
{
     public function index()
    {
        $data =  DB::table('inventory_threshold')->select('*')->get();
        return response()->json(["statusCode" => 0, "message" => "Inventory Threshold Geted Sucessfully.", "data" => $data], 200);
    }

    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'idproduct_master' => 'required|integer',
                'idstore_warehouse' => 'required|integer',
                'threshold_quantity' => 'required|integer',
                'sent_quantity' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            } 
            
            $data = [
                'idproduct_master' => $request->idproduct_master,
                'idstore_warehouse' =>  $request->idstore_warehouse,
                'threshold_quantity' =>  $request->threshold_quantity,
                'sent_quantity' =>  $request->sent_quantity,
                'created_at' => now(),
                'updated_at' => now(),
            ];
    
            $id = DB::table('inventory_threshold')->insertGetId($data);
            $createdData = [];
            if(!empty($id)) {
                $createdData = DB::table('inventory_threshold')->find($id);
            }
            
            return response()->json(["statusCode" => 0, "message" => "Inventory Threshold Added Sucessfully.", "data" => $createdData], 200);
        } catch(\Exception $e) {
            return response()->json(["statusCode" => 1, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        $data = [];
        if(!empty($id)) {
            $data = DB::table('inventory_threshold')->find($id);
        }

        return response()->json(["statusCode" => 0, "message" => "Inventory Threshold Geted Sucessfully.", "data" => $data], 200);
    }

    public function update(Request $request, string $id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'idproduct_master' => 'required|integer',
                'idstore_warehouse' => 'required|integer',
                'threshold_quantity' => 'required|integer',
                'sent_quantity' => 'required|integer',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            } 
            
            $data = [
                'idproduct_master' => $request->idproduct_master,
                'idstore_warehouse' =>  $request->idstore_warehouse,
                'threshold_quantity' =>  $request->threshold_quantity,
                'sent_quantity' =>  $request->sent_quantity,
                'updated_at' => now(),
            ];
    
            $update = DB::table('inventory_threshold')->where('id', $id)->update($data);
            if(empty($update)) {
                return response()->json(["statusCode" => 0, "message" => "Record Not Found"], 200);
            }

            $updatedData = [];
            if(!empty($id)) {
                $updatedData = DB::table('inventory_threshold')->find($id);
            }
            
            return response()->json(["statusCode" => 0, "message" => "Inventory Threshold Updated Sucessfully.", "data" => $updatedData], 200);
        } catch(\Exception $e) {
            return response()->json(["statusCode" => 1, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
             $delete = DB::table('inventory_threshold')->delete($id);
             if(empty($update)) {
                return response()->json(["statusCode" => 0, "message" => "Record Not Found"], 200);
            }
             return response()->json(["statusCode" => 0, "message" => "Inventory Threshold Deleted Sucessfully."], 200);
        } catch(\Exception $e) {
            return response()->json(["statusCode" => 1, 'message' => $e->getMessage()], 500);
        }     
    }

    public function get_inventory_threshold_products()
    {
        $idstore_warehouse = !empty($_GET['idstore_warehouse']) ? $_GET['idstore_warehouse'] : null;

        $threshold_data = DB::table('inventory_threshold')
                                ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'inventory_threshold.idproduct_master')
                                ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'inventory_threshold.idproduct_master')
                                ->leftJoin('vendor_purchases_detail', 'vendor_purchases_detail.idproduct_master', '=', 'inventory_threshold.idproduct_master')
                                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                                ->leftJoin('vendor_purchases', 'vendor_purchases.idvendor_purchases', '=', 'vendor_purchases_detail.idvendor_purchases')
                                ->leftJoin('vendor', 'vendor.idvendor', '=', 'vendor_purchases.idvendor')
                                ->select('inventory.idstore_warehouse', 'inventory_threshold.idproduct_master','product_master.name', 'product_master.barcode', 'brands.name As brand_name', 'inventory_threshold.threshold_quantity', 'inventory_threshold.sent_quantity','vendor_purchases_detail.expiry', 'inventory.quantity','vendor.idvendor', 'vendor.name As vendor_name')
                                ->groupBy('inventory.idstore_warehouse', 'inventory_threshold.idproduct_master','product_master.name', 'product_master.barcode', 'brands.name', 'inventory_threshold.threshold_quantity', 'inventory_threshold.sent_quantity', 'vendor_purchases_detail.expiry', 'inventory.quantity', 'vendor.idvendor', 'vendor.name');
       
        if(!empty($idstore_warehouse)) {
            $threshold_data->where('inventory.idstore_warehouse', $idstore_warehouse);
        }

       $inventory_threshold = $threshold_data->get();                         
       $expiry_in_10days = $this->get_near_by_expried_product($idstore_warehouse); 
       $expiry_in_10days = $this->data_formatting($expiry_in_10days);   
       $data = [];              
       foreach($inventory_threshold as $product) {
         if(!empty($product->quantity) && !empty($product->threshold_quantity)) {
            if($product->quantity <= $product->threshold_quantity) {
                $data[] = $product;
            }
         }
       }               
       $data = $this->data_formatting($data);

       $filterData = $this->filtered_data($data, $expiry_in_10days);
       return response()->json(["statusCode" => 0, "message" => "Success", "data" => $filterData], 200);      
    }

    public function data_formatting($data)
    {
        $transformedData = [];

        foreach ($data as $item) {
            $idstore_warehouse = $item->idstore_warehouse;
            $warehouse_name = $this->get_warehouse_name($idstore_warehouse);
            
            $key = "{$item->idstore_warehouse}";
            if (!isset($transformedData[$key])) {
                $transformedData[$key] = [
                    'idstore_warehouse' => $idstore_warehouse,
                    'warehouse_name' => $warehouse_name,
                    'products' => [],
                ];
            }
            $batchDetail = DB::table('product_batch')
                ->where('idproduct_master', $item->idproduct_master)
                ->where('status', 1)
                ->orderBy('created_at','asc')->get();
            $transformedData[$key]['products'][] = [
                'idproduct_master' => $item->idproduct_master,
                'product_name' => $item->name,
                'barcode' => $item->barcode,
                'idproduct_batch'=>$batchDetail?$batchDetail[0]->idproduct_batch:'',
                'batch_name'=>$batchDetail?$batchDetail[0]->name:'',
                'batch_details'=>$batchDetail,
                'brand' => $item->brand_name,
                'expiry' => $item->expiry,
                'threshold_quantity' => $item->threshold_quantity,
                'sent_quantity' => !empty($item->sent_quantity) ? $item->sent_quantity : 0,
                'quantity' => $item->quantity,
                'idvendor' => !empty($item->idvendor) ? $item->idvendor : ' ',
                'vendor_name' => !empty($item->vendor_name) ? $item->vendor_name : ' '
            ];
        }

        $transformedData = array_values($transformedData);

        return $transformedData;
    }

    public function get_warehouse_name($id)
    {
        $warehouse = DB::table('store_warehouse')->where('idstore_warehouse', $id)->first();
        return !empty($warehouse) ? $warehouse->name : ''; 
    }

    public function get_near_by_expried_product($idstore_warehouse = null)
    {
        $get_product = DB::table('vendor_purchases_detail')
                       ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'vendor_purchases_detail.idproduct_master')
                       ->leftJoin('inventory_threshold', 'inventory_threshold.idproduct_master', '=', 'vendor_purchases_detail.idproduct_master')
                       ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'vendor_purchases_detail.idproduct_master')
                       ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                       ->leftJoin('vendor_purchases', 'vendor_purchases.idvendor_purchases', '=', 'vendor_purchases_detail.idvendor_purchases')
                       ->leftJoin('vendor', 'vendor.idvendor', '=', 'vendor_purchases.idvendor')
                       ->select('vendor_purchases_detail.idproduct_master', 'inventory.idstore_warehouse', 'product_master.name', 'product_master.barcode', 'brands.name As brand_name', 'inventory_threshold.threshold_quantity', 'inventory.quantity', 'vendor_purchases_detail.expiry', 'vendor.idvendor')
                       ->where('expiry', '>', now()->toDateString())
                       ->where('expiry', '<', now()->addDays(10));
        if(!empty($idstore_warehouse)) {
            $data = $get_product->where('inventory.idstore_warehouse', $idstore_warehouse);
        }               
        $data = $get_product->get();
        return $data;               
    }

    public function place_order_threshold_product(Request $request)
    {
        try{
            
            $validator = Validator::make($request->all(), [
                'order_data' => 'required|array',
            ],[
               'order_data.required' => 'Please enter order data.', 
               'order_data.array' => 'Please enter order data in array format.',
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            } 

            foreach($request->order_data  as $order) {
                $purchase_order_data = [
                    'idvendor' => $order["idvendor"],
                    'idstore_warehouse' =>$order["idstore_warehouse"]
                ];
                $total_quantity = 0;
                $purchase_order_detail_data = [];
                foreach($order["products"] as $product) {
                    $total_quantity += $product["quantity"];
                    $purchase_order_detail_data[] = [
                        'idproduct_master' => $product["idproduct_master"],
                        'quantity' => $product["quantity"],
                        'status' => 1,
                        "created_at" => now(),
                        "updated_at" => now() 
                    ];
                }
                $purchase_order_data += [
                    'total_quantity' => $total_quantity,
                    'status' => 1,
                    "created_at" => now(),
                    "updated_at" => now()
                ];

                $idpurchase_order = DB::table('purchase_order')->insertGetId($purchase_order_data);

                foreach($purchase_order_detail_data as $data) {
                    $data['idpurchase_order'] = $idpurchase_order;
                    $idpurchase_order_detail = DB::table('purchase_order_detail')->insertGetId($data);
                }
            }
            
            return response()->json(["statusCode" => 0, "message" => "Order Placed Sucessfully."], 200);

        } catch(\Exception $e) {
            return response()->json(["statusCode" => 1, 'message' => $e->getMessage()], 500);
        }     
    }
    
    public function get_threshold_order()
    {
        $idstore_warehouse = !empty($_GET['idstore_warehouse']) ? $_GET['idstore_warehouse'] : null;
        $idvendor = !empty($_GET['idvendor']) ? $_GET['idvendor'] : null;

        $get_data = DB::table('purchase_order')
                    ->select('id as idpurchase_order', 'idvendor', 'idstore_warehouse','total_quantity');
        
        if(!empty($idstore_warehouse)) {
            $get_data->where('idstore_warehouse', $idstore_warehouse);
        } 
        if(!empty($idvendor)) {
            $get_data->where('idvendor', $idvendor);
        }            
        $threshold_order = $get_data->get();
        
        foreach($threshold_order as $order) {
            $order->name = $this->get_warehouse_name($order->idstore_warehouse);
            $order->vedor_name = $this->get_vendor_name($order->idvendor);
            $warehouse = DB::table('store_warehouse')->select('warehouse_connected')->where('idstore_warehouse', $order->idstore_warehouse)->first();
            $order->warehouse = !empty($warehouse->warehouse_connected) ? $warehouse->warehouse_connected : '';
            $order_detail = $this->get_order_detail($order->idpurchase_order);
            $order->products = $order_detail;
        } 
        $threshold_order = $this->data_formatting_vendor_store_wise($threshold_order);
        return response()->json(["statusCode" => 0, "message" => "success", "data" => $threshold_order], 200); 
    }

    public function get_order_detail($id)
    {
        $get_detail_data = DB::table('purchase_order_detail')
                           ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'purchase_order_detail.idproduct_master') 
                           ->select('purchase_order_detail.idproduct_master', 'product_master.name', 'purchase_order_detail.quantity')
                           ->where('purchase_order_detail.idpurchase_order', $id) 
                           ->get();
        return $get_detail_data;                                     
    }

    public function filtered_data($threshold_products, $expiry_in_10days_products)
    {
        $result = [];
        foreach ($threshold_products as $threshold_store) {
            $store_id = $threshold_store['idstore_warehouse'];
            $result[$store_id]['idstore_warehouse'] = $store_id;
            $result[$store_id]['warehouse_name'] = $threshold_store['warehouse_name'];
            $result[$store_id]['products']['threshold_products'] = $threshold_store['products'];
            $result[$store_id]['products']['expiry_in_10days_products'] = [];
        }


        foreach ($expiry_in_10days_products as $expiry_store) {
            $store_id = $expiry_store['idstore_warehouse'];
            $result[$store_id]['idstore_warehouse'] = $store_id;
            $result[$store_id]['warehouse_name'] = $expiry_store['warehouse_name'];
            $result[$store_id]['products']['expiry_in_10days_products'] = $expiry_store['products'];
        }

        $result = array_values($result);
        foreach($result as $array) {
            if(empty($array['products']['threshold_products'])) {
                $array['products']['threshold_products'] = [];
            }
            if(empty($array['products']['expiry_in_10days_products'])) {
                $array['products']['expiry_in_10days_products'] = [];
            }
        }
        return $result;
    }

    public function sync_inventory_with_purchase_order()
    {
        try{
            
            $get_warehouse_wise_store = $this->get_warehouse_wise_store();
            foreach($get_warehouse_wise_store as $warehouse) {
                foreach($warehouse->stores as $store) {
                    $inventory_threshold = $this->get_inventory_threshold_store_wise($store->idstore_warehouse);
                    if(!empty($inventory_threshold[0]['products'])) {
                        $inventory_threshold_data = $inventory_threshold[0]['products'];
                        $store->products['threshold_products'] = !empty($inventory_threshold_data['threshold_products']) ? $inventory_threshold_data['threshold_products'] : [];
                        $store->products['expiry_in_10days_products'] = !empty($inventory_threshold_data['expiry_in_10days_products']) ? $inventory_threshold_data['expiry_in_10days_products'] : [];
                    } else {
                        $store->products['threshold_products'] = [];
                        $store->products['expiry_in_10days_products'] = [];
                    }
                }
            }

            $get_warehouse_wise_store = $this->formating_vedeor_wise_data($get_warehouse_wise_store);
            $this->get_sync_inventory_order($get_warehouse_wise_store);
            return response()->json(["statusCode" => 0, "message" => "Thresold Product Order Placed Sucessfully.", "data" => $get_warehouse_wise_store], 200);         
        
        } catch(\Exception $e) {
            return response()->json(["statusCode" => 1, 'message' => $e->getMessage()], 500);
        }
    }

    public function get_warehouse_wise_store()
    {
        $data =  DB::table('store_warehouse')
                 ->select('idstore_warehouse', 'name')
                 ->where('is_store', 0)
                 ->where('status', 1)
                 ->get();

        foreach($data as $warehouse){
            $get_store = DB::table('store_warehouse')->select('idstore_warehouse', 'name')->where('warehouse_connected', $warehouse->idstore_warehouse)->where('status', 1)->get();
            $warehouse->stores = $get_store;
        }

        // foreach ($data as &$warehouse) {
        //     $idstore_warehouse = $warehouse->idstore_warehouse;
        //     $name = $warehouse->name;
        //     $newStore = [
        //         "idstore_warehouse" => $idstore_warehouse,
        //         "name" => $name,
        //     ];
        //     $warehouse->stores[] = (object)$newStore;
        // }

        return $data;
    }

    public function get_inventory_threshold_store_wise($idstore_warehouse)
    {
        $inventory_threshold = DB::table('inventory_threshold')
                                ->leftJoin('inventory', 'inventory.idproduct_master', '=', 'inventory_threshold.idproduct_master')
                                ->leftJoin('product_master', 'product_master.idproduct_master', '=', 'inventory_threshold.idproduct_master')
                                ->leftJoin('vendor_purchases_detail', 'vendor_purchases_detail.idproduct_master', '=', 'inventory_threshold.idproduct_master')
                                ->leftJoin('brands', 'brands.idbrand', '=', 'product_master.idbrand')
                                ->leftJoin('vendor_purchases', 'vendor_purchases.idvendor_purchases', '=', 'vendor_purchases_detail.idvendor_purchases')
                                ->leftJoin('vendor', 'vendor.idvendor', '=', 'vendor_purchases.idvendor')
                                ->select('inventory.idstore_warehouse', 'inventory_threshold.idproduct_master','product_master.name', 'product_master.barcode', 'brands.name As brand_name', 'inventory_threshold.threshold_quantity', 'inventory_threshold.sent_quantity', 'vendor_purchases_detail.expiry', 'inventory.quantity', 'vendor.idvendor', 'vendor.name As vendor_name')
                                ->groupBy('inventory.idstore_warehouse', 'inventory_threshold.idproduct_master','product_master.name', 'product_master.barcode', 'brands.name', 'inventory_threshold.threshold_quantity', 'inventory_threshold.sent_quantity', 'vendor_purchases_detail.expiry', 'inventory.quantity', 'vendor.idvendor', 'vendor.name')
                                ->where('inventory.idstore_warehouse', $idstore_warehouse)
                                ->get();
                               
       $expiry_in_10days = $this->get_near_by_expried_product($idstore_warehouse); 
       $expiry_in_10days = $this->data_formatting($expiry_in_10days);   
       $data = [];              
       foreach($inventory_threshold as $product) {
         if(!empty($product->quantity) && !empty($product->threshold_quantity)) {
            if($product->quantity <= $product->threshold_quantity) {
                $data[] = $product;
            }
         }
       }               
       $data = $this->data_formatting($data);

       $filterData = $this->filtered_data($data, $expiry_in_10days);
       return $filterData;      
    }
    public function formating_vedeor_wise_data($data)
    {
        $result = [];

        foreach ($data as $warehouse) {
            $newWarehouse = [
                "idstore_warehouse" => $warehouse->idstore_warehouse,
                "name" => $warehouse->name,
                "stores" => []
            ];

            foreach ($warehouse->stores as $store) {
                $vendorProducts = [];

                foreach ($store->products["threshold_products"] as $product) {
                    $vendorId = $product["idvendor"];
                    $vendorName = $product["vendor_name"];

                    if (!isset($vendorProducts[$vendorId])) {
                        $vendorProducts[$vendorId] = [
                            "idvendor" => $vendorId,
                            "vendor_name" => $vendorName,
                            "products" => [
                                "threshold_products" => [],
                                "expiry_in_10days_products" => []
                            ]
                        ];
                    }

                    $vendorProducts[$vendorId]["products"]["threshold_products"][] = $product;
                }

                foreach ($store->products["expiry_in_10days_products"] as $product) {
                    $vendorId = $product["idvendor"];
                    $vendorName = $product["vendor_name"];

                    if (!isset($vendorProducts[$vendorId])) {
                        $vendorProducts[$vendorId] = [
                            "idvendor" => $vendorId,
                            "vendor_name" => $vendorName,
                            "products" => [
                                "threshold_products" => [],
                                "expiry_in_10days_products" => []
                            ]
                        ];
                    }

                    $vendorProducts[$vendorId]["products"]["expiry_in_10days_products"][] = $product;
                }

                $newStore = [
                    "idstore_warehouse" => $store->idstore_warehouse,
                    "name" => $store->name,
                    "vedor_products" => array_values($vendorProducts)
                ];

                $newWarehouse["stores"][] = $newStore;
            }

            $result[] = $newWarehouse;
        }
        return $result;
    }
    
    public function get_sync_inventory_order($inventory_data)
    {
        foreach($inventory_data as $warehouse_wise_data) {
            foreach($warehouse_wise_data['stores'] as $store_wise_data) {
                foreach($store_wise_data['vedor_products'] as $vendor_vise_data) {
                    if($vendor_vise_data['idvendor'] !== " " && $vendor_vise_data['vendor_name'] !== " ") {
                        if(!empty($vendor_vise_data['products']['threshold_products'])) {
                            $total_sent_quantity = 0;
                            $order_detail_data = [];
                            foreach($vendor_vise_data['products']['threshold_products'] as $product) {
                                $total_sent_quantity += $product['sent_quantity']; 
                                $order_detail_data[] = [
                                    'idproduct_master' => $product['idproduct_master'],
                                    'quantity' => $product['sent_quantity'],
                                    'status' => 1,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ];
                            }
                            $order_data = [
                                'idvendor' => $vendor_vise_data['idvendor'],
                                'idstore_warehouse' => $store_wise_data['idstore_warehouse'],
                                'total_quantity' => $total_sent_quantity,
                                'status' => 0,
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                            $order_id = $this->place_order($order_data);
                            $this->set_order_detail($order_detail_data, $order_id);
                        }
                        if(!empty($vendor_vise_data['products']['expiry_in_10days_products'])) {
                            $total_sent_quantity = 0;
                            $order_detail_data = [];
                            foreach($vendor_vise_data['products']['expiry_in_10days_products'] as $product) {
                                $total_sent_quantity += $product['sent_quantity']; 
                                $order_detail_data[] = [
                                    'idproduct_master' => $product['idproduct_master'],
                                    'quantity' => $product['sent_quantity'],
                                    'status' => 1,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ];
                            }
                            $order_data = [
                                'idvendor' => $vendor_vise_data['idvendor'],
                                'idstore_warehouse' => $store_wise_data['idstore_warehouse'],
                                'total_quantity' => $total_sent_quantity,
                                'status' => 0,
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                            $order_id = $this->place_order($order_data);
                            $this->set_order_detail($order_detail_data, $order_id);
                        }
                    }
                }
            }
        }
    }

    public function place_order($data) 
    {
        $id = DB::table('purchase_order')->insertGetId($data);
        return $id;
    }

    public function set_order_detail($data, $order_id)
    {
        foreach($data as $key => $order) {
            $order['idpurchase_order'] = $order_id;
            $id = DB::table('purchase_order_detail')->insertGetId($order);
        }
    }

    public function data_formatting_vendor_store_wise($data)
    {
        $newStructure = [];

        foreach ($data as $item) {
            $warehouseId = $item->warehouse;
            $orderId = $item->idpurchase_order;
            $vendorId = $item->idvendor;
            $storeWarehouseId = $item->idstore_warehouse;

            if (!isset($newStructure[$warehouseId])) {
                $newStructure[$warehouseId] = [
                    'idstore_warehouse' => $warehouseId,
                    'name' => $this->get_warehouse_name($warehouseId),
                    'order' => []
                ];
            }

            $orderIndex = array_search($orderId, array_column($newStructure[$warehouseId]['order'], 'idpurchase_order'));
            if ($orderIndex === false) {
                $newStructure[$warehouseId]['order'][] = [
                    'idpurchase_order' => $orderId,
                    'total_quantity' => $item->total_quantity,
                    'vendor_store' => []
                ];
                $orderIndex = count($newStructure[$warehouseId]['order']) - 1;
            }

            $vendorIndex = array_search($vendorId, array_column($newStructure[$warehouseId]['order'][$orderIndex]['vendor_store'], 'idvendor'));
            if ($vendorIndex === false) {
                $newStructure[$warehouseId]['order'][$orderIndex]['vendor_store'][] = [
                    'idvendor' => $vendorId,
                    'vedor_name' => $item->vedor_name,
                    'stores' => []
                ];
                $vendorIndex = count($newStructure[$warehouseId]['order'][$orderIndex]['vendor_store']) - 1;
            }

            $storeIndex = array_search($storeWarehouseId, array_column($newStructure[$warehouseId]['order'][$orderIndex]['vendor_store'][$vendorIndex]['stores'], 'idstore_warehouse'));
            if ($storeIndex === false) {
                $newStructure[$warehouseId]['order'][$orderIndex]['vendor_store'][$vendorIndex]['stores'][] = [
                    'idstore_warehouse' => $storeWarehouseId,
                    'name' => $item->name,
                    'products' => $item->products
                ];
            }
        }

        $newStructure = array_values($newStructure);
        return $newStructure;
    }

    public function get_vendor_name($id) 
    {
        $vendor = DB::table('vendor')->select('name')->where('idvendor', $id)->first();
        return !empty($vendor->name) ? $vendor->name:'';
    }

    public function autoStockTransfer(Request $request){
        try {
            DB::beginTransaction();
            $req = json_decode($request->getContent(),true);
            $user = auth()->guard('api')->user();
            //$user=json_decode('{"id":1}');
            
          
                $storeWarehouseDetail = DB::table('store_warehouse')
                ->where('idstore_warehouse', $req['id_warehouse'])
                ->first();
                if($storeWarehouseDetail){
                    $AutoTransferRequest = array(
                        'idstore_warehouse_from' => $req['id_warehouse'],
                        'idstore_warehouse_to' => $req['id_store'],
                        'dispatch_date'=>date("Y-m-d"),
                        'dispatched_by'=> $user->id, // replace 1 with $user->id
                        'created_by' => $user->id, // replace 1 with $user->id
                        'updated_by' => $user->id, // replace 1 with $user->id
                        'status' => 1
                    );
                    $createAutoTransfer = AutoTransferRequest::create($AutoTransferRequest);
                    
                    if($createAutoTransfer){
                        if(empty($req['threshold_products'])){
                            foreach ($req['expiry_in_10days_products'] as $pro) {
                                
                                $productBatchDetail = DB::table('product_batch')
                                ->where('idproduct_master', $pro['idproduct_master'])
                                ->where('idstore_warehouse', $req['id_store'])
                                ->where('mrp', $pro['mrp'])
                                ->where('name', $pro['batch'])
                                ->first();
    
                                $productInvDetail = DB::table('inventory')
                                    ->where('idproduct_master', $pro['idproduct_master'])
                                    ->where('idstore_warehouse', $req['id_store'])
                                    ->first();
    
                                $ware_productInvDetail = DB::table('inventory')
                                    ->where('idproduct_master', $pro['idproduct_master'])
                                    ->where('idstore_warehouse', $req['id_warehouse'])
                                    ->first();
                                if($ware_productInvDetail)
                                {
                                        $updatedQty=$pro['sent_quantity'];
                                        if($ware_productInvDetail->quantity < $updatedQty){ // check if warehouse Qty lessthan threshold then only available warehose qty will transfer
                                            $updatedQty=$ware_productInvDetail->quantity;
                                        }
    
                                        if (isset($productBatchDetail->idproduct_batch)) {
                                            DB::table('product_batch')
                                                ->where('idproduct_batch', $productBatchDetail->idproduct_batch)
                                                ->update([
                                                    'quantity' => DB::raw('quantity + ' . $updatedQty),
                                                    'selling_price' => $ware_productInvDetail->selling_price!=''?$ware_productInvDetail->selling_price:0,
                                                    'purchase_price' => $ware_productInvDetail->purchase_price!=''?$ware_productInvDetail->purchase_price:0,
                                                    'mrp' => $pro['mrp'],
                                                    'product'=>$ware_productInvDetail->product,
                                                    'copartner'=>$ware_productInvDetail->copartner,
                                                    'land'=>$ware_productInvDetail->land,
                                                ]);
                                                $batch_id=$productBatchDetail->idproduct_batch;
                                        } else { 
                                            $batch = array(
                                                'idstore_warehouse' => $req['id_store'],
                                                'idproduct_master' => $pro['idproduct_master'],
                                                'name' => $pro['batch'],
                                                'purchase_price' => floatval($ware_productInvDetail->purchase_price),
                                                'selling_price' => floatval($ware_productInvDetail->selling_price),
                                                'mrp' => floatval($pro['mrp']),
                                                'product'=>$ware_productInvDetail->product,
                                                'copartner'=>$ware_productInvDetail->copartner,
                                                'land'=>$ware_productInvDetail->land,
                                                'discount' => 0,
                                                'quantity' =>  $ware_productInvDetail->quantity,
                                                'expiry' => '',
                                                'created_by' => $user->id, // replace 1 with $user->id
                                                'updated_by' => $user->id, // replace 1 with $user->id
                                                'status' => 1
                                            );
                                            $pb = ProductBatch::create($batch);
                                            $batch_id=$pb->idproduct_batch;
                                        }
                                        
                                    if ($productInvDetail) {   
                                        DB::table('inventory')
                                        ->where('idproduct_master', $pro['idproduct_master'])
                                        ->where('idstore_warehouse', $req['id_store'])
                                        ->update([
                                            'quantity' => DB::raw('quantity + ' . $updatedQty),
                                        ]);
                                    }else {
                                        $inv = array(
                                            'idstore_warehouse' => $req['id_store'],
                                            'idproduct_master' => $pro['idproduct_master'],
                                            'purchase_price' => $ware_productInvDetail->purchase_price!=''?$ware_productInvDetail->unit_purchase_price:0,
                                            'selling_price' => $ware_productInvDetail->selling_price!=''?$ware_productInvDetail->selling_price:0,
                                            'mrp' => floatval($pro['mrp']),
                                            'product'=>$ware_productInvDetail->product,
                                            'copartner'=>$ware_productInvDetail->copartner,
                                            'land'=>$ware_productInvDetail->land,
                                            'discount' => 0,
                                            'quantity' => $updatedQty,
                                            'only_online' => 0,
                                            'only_offline' => 0,
                                            'created_by' => $user->id, // replace 1 with $user->id
                                            'updated_by' => $user->id, // replace 1 with $user->id
                                            'status' => 1
                                        );
                                        $inv = Inventory::create($inv);
                                    }
    
                                        // add request details
                                        $autotransferRequestDetail = array(
                                            'idauto_transfer_requests' => $createAutoTransfer->id,
                                            'idproduct_master' => $pro['idproduct_master'],
                                            'idproduct_batch' => $pro['idproduct_batch'],
                                            'quantity'=>$ware_productInvDetail->quantity,
                                            'quantity_sent'=>$updatedQty,
                                            'quantity_received'=>$updatedQty,
                                            'created_by' => $user->id, // replace 1 with $user->id
                                            'updated_by' => $user->id, // replace 1 with $user->id
                                            'status' => 1
                                        );
                                        $createAutoTransferDetail = AutoTransferRequestDetail::create($autotransferRequestDetail);
                                        // update from qty
                                        DB::table('inventory')
                                            ->where('idproduct_master', $pro['idproduct_master'])
                                            ->where('idstore_warehouse', $req['id_warehouse'])
                                            ->update([
                                                'quantity' => DB::raw('quantity - ' . $updatedQty)
                                            ]);
                                        // update from qty
                                        DB::table('product_batch')
                                            ->where('idproduct_master', $pro['idproduct_master'])
                                            ->where('idstore_warehouse', $req['id_warehouse'])
                                            ->where('idproduct_batch',$pro['idproduct_batch'])
                                            ->update([
                                                'quantity' => DB::raw('quantity - ' . $updatedQty)
                                            ]);
                                    
                                }else {
                                    return response()->json(["statusCode" => 1, "message" => '', "err" => 'warehouse product inventory does not exist'], 200);
                                }
                            }
                        }else{
                            foreach ($req['threshold_products'] as $pro) {
                                
                                $productBatchDetail = DB::table('product_batch')
                                ->where('idproduct_master', $pro['idproduct_master'])
                                ->where('idstore_warehouse', $req['id_store'])
                                ->where('mrp', $pro['mrp'])
                                ->where('name', $pro['batch'])
                                ->first();
    
                                $productInvDetail = DB::table('inventory')
                                    ->where('idproduct_master', $pro['idproduct_master'])
                                    ->where('idstore_warehouse', $req['id_store'])
                                    ->first();
    
                                $ware_productInvDetail = DB::table('inventory')
                                    ->where('idproduct_master', $pro['idproduct_master'])
                                    ->where('idstore_warehouse', $req['id_warehouse'])
                                    ->first();
                                if($ware_productInvDetail)
                                {
                                        $updatedQty=$pro['threshold_quantity'];
                                        if($ware_productInvDetail->quantity < $updatedQty){ // check if warehouse Qty lessthan threshold then only available warehose qty will transfer
                                            $updatedQty=$ware_productInvDetail->quantity;
                                        }
    
                                        if (isset($productBatchDetail->idproduct_batch)) {
                                            DB::table('product_batch')
                                                ->where('idproduct_batch', $productBatchDetail->idproduct_batch)
                                                ->update([
                                                    'quantity' => DB::raw('quantity + ' . $updatedQty),
                                                    'selling_price' => $ware_productInvDetail->selling_price!=''?$ware_productInvDetail->selling_price:0,
                                                    'purchase_price' => $ware_productInvDetail->purchase_price!=''?$ware_productInvDetail->purchase_price:0,
                                                    'mrp' => $pro['mrp'],
                                                    'product'=>$ware_productInvDetail->product,
                                                    'copartner'=>$ware_productInvDetail->copartner,
                                                    'land'=>$ware_productInvDetail->land,
                                                ]);
                                                $batch_id=$productBatchDetail->idproduct_batch;
                                        } else { 
                                            $batch = array(
                                                'idstore_warehouse' => $req['id_store'],
                                                'idproduct_master' => $pro['idproduct_master'],
                                                'name' => $pro['batch'],
                                                'purchase_price' => floatval($ware_productInvDetail->purchase_price),
                                                'selling_price' => floatval($ware_productInvDetail->selling_price),
                                                'mrp' => floatval($pro['mrp']),
                                                'product'=>$ware_productInvDetail->product,
                                                'copartner'=>$ware_productInvDetail->copartner,
                                                'land'=>$ware_productInvDetail->land,
                                                'discount' => 0,
                                                'quantity' =>  $ware_productInvDetail->quantity,
                                                'expiry' => '',
                                                'created_by' => $user->id, // replace 1 with $user->id
                                                'updated_by' => $user->id, // replace 1 with $user->id
                                                'status' => 1
                                            );
                                            $pb = ProductBatch::create($batch);
                                            $batch_id=$pb->idproduct_batch;
                                        }
                                        
                                    if ($productInvDetail) {   
                                        DB::table('inventory')
                                        ->where('idproduct_master', $pro['idproduct_master'])
                                        ->where('idstore_warehouse', $req['id_store'])
                                        ->update([
                                            'quantity' => DB::raw('quantity + ' . $updatedQty),
                                        ]);
                                    }else {
                                        $inv = array(
                                            'idstore_warehouse' => $req['id_store'],
                                            'idproduct_master' => $pro['idproduct_master'],
                                            'purchase_price' => $ware_productInvDetail->purchase_price!=''?$ware_productInvDetail->unit_purchase_price:0,
                                            'selling_price' => $ware_productInvDetail->selling_price!=''?$ware_productInvDetail->selling_price:0,
                                            'mrp' => floatval($pro['mrp']),
                                            'product'=>$ware_productInvDetail->product,
                                            'copartner'=>$ware_productInvDetail->copartner,
                                            'land'=>$ware_productInvDetail->land,
                                            'discount' => 0,
                                            'quantity' => $updatedQty,
                                            'only_online' => 0,
                                            'only_offline' => 0,
                                            'created_by' => $user->id, // replace 1 with $user->id
                                            'updated_by' => $user->id, // replace 1 with $user->id
                                            'status' => 1
                                        );
                                        $inv = Inventory::create($inv);
                                    }
    
                                        // add request details
                                        $autotransferRequestDetail = array(
                                            'idauto_transfer_requests' => $createAutoTransfer->id,
                                            'idproduct_master' => $pro['idproduct_master'],
                                            'idproduct_batch' => $pro['idproduct_batch'],
                                            'quantity'=>$ware_productInvDetail->quantity,
                                            'quantity_sent'=>$updatedQty,
                                            'quantity_received'=>$updatedQty,
                                            'created_by' => $user->id, // replace 1 with $user->id
                                            'updated_by' => $user->id, // replace 1 with $user->id
                                            'status' => 1
                                        );
                                        $createAutoTransferDetail = AutoTransferRequestDetail::create($autotransferRequestDetail);
                                        // update from qty
                                        DB::table('inventory')
                                            ->where('idproduct_master', $pro['idproduct_master'])
                                            ->where('idstore_warehouse', $req['id_warehouse'])
                                            ->update([
                                                'quantity' => DB::raw('quantity - ' . $updatedQty)
                                            ]);
                                        // update from qty
                                        DB::table('product_batch')
                                            ->where('idproduct_master', $pro['idproduct_master'])
                                            ->where('idstore_warehouse', $req['id_warehouse'])
                                            ->where('idproduct_batch',$pro['idproduct_batch'])
                                            ->update([
                                                'quantity' => DB::raw('quantity - ' . $updatedQty)
                                            ]);
                                    
                                }else {
                                    return response()->json(["statusCode" => 1, "message" => '', "err" => 'warehouse product inventory does not exist'], 200);
                                }
                            }
                        }
                        DB::commit();
                        return response()->json(["statusCode" => 0, "message" => "Success"], 200);
                    }else{
                        return response()->json(["statusCode" => 1, "message" => '', "err" => 'issue while creating auto transfer request'], 200);
                    }
                }else{
                    return response()->json(["statusCode" => 1, "message" => '', "err" => 'Warehouse does not exist'], 200);
                }
            
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(["statusCode" => 1, "message" => '', "err" => $e->getMessage()], 200);
        }
        
    }
}