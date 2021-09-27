<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreStock;
use App\Models\WarehouseStock;
use App\Models\Waybill;
use App\Models\WayBillHistory;
use App\Traits\Helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends BaseController
{
    use Helpers;
    public function index()
    {
        $products = Product::with(['warehouseStock.warehouse', 'waybill'])->get();
        $start = new Carbon("first day of this month");
        $end = Carbon::now();
        $today = Product::whereBetween('created_at', [$start, $end])->get();
        return $this->sendMessage(compact('products', 'today'));
    }
    public function uploadProducts(ProductRequest $request)
    {
        $waybill = Waybill::find($request->waybill_id);
        $user = auth("sanctum")->user();

        // return $request->all();
        if (is_null($waybill)) {
            return $this->sendMessage(['Create a waybill first'], false, 404);
        }
        $products = json_decode($request->products);
        /**
         * Create the product
         */
        foreach ($products as $product) {
            $p = [
                'name' => $product->name,
                'price' => $product->amount,
            ];
            $singleProduct = Validator::make($p, [
                'name' => 'string|required',
                'price' => 'required|integer',
            ]);
            if ($singleProduct->fails()) {
                return $this->sendMessage(null, $singleProduct->errors(), false, 500);
            }
            $newProduct = $waybill->products()->firstOrCreate($p);
            /**
             * Update stock
             */
            WayBillHistory::create([
                'waybill_id' => $waybill->id,
                'product_id' => $newProduct->id,
                'qty' => $product->qty,
            ]);
            $warehouseStore = WarehouseStock::where([['product_id', $newProduct->id], ['warehouse_id', request('warehouse_id')]])->first();
            if (is_null($warehouseStore)) {
                WarehouseStock::create([
                    'product_id' => $newProduct->id,
                    'warehouse_id' => $waybill->warehouse_id,
                    'qty_in_stock' => $product->qty,
                ]);
            } else {
                $warehouseStore->update([
                    'qty_in_stock' => $warehouseStore->qty_in_stock + $product->qty,
                ]);
            }
            $pArray = [$product->name, $product->qty, $product->amount];
            array_push($pArray);
        }
        $date = date('dS F Y', strtotime($waybill->created_at));
        $c = count($products);
        $warehouse = $waybill->warehouse()->first();
        $notification = [
            'type' => 'Product Upload',
            "tablehead" => ["Product Name", "Product Qty", "Product Amount"],
            "tablebody" => $pArray,
            'body' => "products has been uploaded into {$warehouse->name}",
            'line1' => "A total of {$c} Product(s) have been uploaded. These products with waybill {$waybill->code} are attached to warehouse {$warehouse->name} has been successfully uploaded by {$user->name}, on {$date} See details below:",
        ];
        try {
            $this->updateNotification($notification);
        } catch (\Throwable$th) {
            return $this->sendMessage("Products uploaded successfully, with notification error", ["Products uploaded successfully, but mail notification error", json_encode($th)], 500);
        }
        return $this->sendMessage('Products uploaded successfully');
    }
    public function edit(Request $request)
    {
        $user = auth("sanctum")->user();

        $request->validate([
            'id' => 'integer|required',
            'name' => 'required|string',
            'price' => 'required|integer',
        ]);
        $product = Product::find($request->id);
        $product->update(
            array_merge(['last_edit_by_id' => $user->id], $request->only([
                'name',
                'price',
            ]))
        );

        return $this->sendMessage('Product Updated');

    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'string|required',
            'price' => 'required|integer',
            'warehouse_id' => 'required|integer',
            'waybill_id' => 'required|integer',
            'qty' => 'required|integer',
        ]);

        $waybill = Waybill::find($request->waybill_id);

        if (is_null($waybill)) {
            return $this->sendMessage(['Create a waybill first'], false, 404);
        }

        $newProduct = $waybill->products()
            ->firstOrCreate($request->only([
                'name',
                'price',
            ]));
        /**
         * Update stock
         */
        $warehouseStore = WarehouseStock::where([['product_id', $newProduct->id], ['warehouse_id', request('warehouse_id')]])->first();
        if (is_null($warehouseStore)) {
            WarehouseStock::create([
                'product_id' => $newProduct->id,
                'warehouse_id' => $waybill->warehouse_id,
                'qty_in_stock' => $request->qty,
            ]);
        } else {
            $warehouseStore->update([
                'qty_in_stock' => $warehouseStore + $request->qty,
            ]);
        }
        return $this->sendMessage('Products uploaded successfully');

    }
    public function showStoreProducts($id)
    {
        $products = StoreStock::where('store_id', $id)->with('product')->get();
        return $this->sendMessage($products);

    }
    public function show($id)
    {
        $product = Product::where('id', $id)->with(["warehouseStock", "waybill", 'storeStocks', "transferProducts.transfer"])->first();
        return $this->sendMessage(new ProductResource($product));
    }
    public function destroy($id)
    {
        Product::find($id)->delete();
        return $this->sendMessage("Product Deleted");
    }
    // public function warehouseStock($id)
    // {
    //     $warehouse = Warehouse::where('id', $id)->with(['warehouseStocks', 'waybills'])->get();
    //     return $this->sendMessage();
    // }
}
