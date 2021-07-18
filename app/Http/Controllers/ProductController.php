<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Requests\WayBillRequest;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreStock;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\Waybill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends BaseController
{
    public function index()
    {
        $products = Product::with(['warehouseStock.warehouse', 'waybill'])->get();
        return $this->sendMessage( $products);
      }
    public function uploadProducts(ProductRequest $request)
    {
        $waybill = Waybill::find($request->waybill_id);

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
            $newProduct =  $waybill->products()->firstOrCreate($p);
            /**
             * Update stock
             */
            $warehouseStore = WarehouseStock::where([['product_id', $newProduct->id], ['warehouse_id', request('warehouse_id')]])->first();
            if (is_null($warehouseStore)) {
                WarehouseStock::create([
                    'product_id' => $newProduct->id,
                    'warehouse_id' => $waybill->warehouse_id,
                    'qty_in_stock' => $product->qty,
                ]);
            } else {
                $warehouseStore->update([
                    'qty_in_stock' => $warehouseStore + $product->qty
                ]);
            }

        }
        return $this->sendMessage('Products uploaded successfully');

    }
    public function showStoreProducts($id)
    {
        $products = StoreStock::where('store_id', $id)->with('product')->get();
        return $this->sendMessage($products);

    }
    // public function warehouseStock($id)
    // {
    //     $warehouse = Warehouse::where('id', $id)->with(['warehouseStocks', 'waybills'])->get();
    //     return $this->sendMessage();
    // }
}
