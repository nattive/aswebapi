<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransferRequest;
use App\Http\Resources\TransferResource;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreStock;
use App\Models\Transfer;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Traits\Helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TransferController extends BaseController
{
    use Helpers;
    public function store(TransferRequest $request)
    {
        $prevTrans = Transfer::where('from', $request->from)->get()->count();
        $count = $prevTrans + 1;
        $c = $count < 99 ? '00' . $count : '0' . $count;
        $productsArray = [];
        switch ($request->transfer_type) {
            case 'STORE_TO_STORE':
                $code = 'TP/STS/' . Carbon::now()->format('m') . '0' . $c;
                break;
            case 'STORE_TO_WAREHOUSE':
                $code = 'TP/STW/' . Carbon::now()->format('m') . '0' . $c;
                break;
            case 'WAREHOUSE_TO_STORE':
                $code = 'TP/WTS/' . Carbon::now()->format('m') . '0' . $c;
                break;
            case 'WAREHOUSE_TO_WAREHOUSE':
                $code = 'TP/WTW/' . Carbon::now()->format('m') . '0' . $c;
                break;
            default:
                break;
        }
        $transfer = Transfer::create(array_merge($request->except('products'), ['ref_code' => $code]));
        foreach ($request->products as $product) {
            if (array_key_exists('product_id', $product)) {
                $transfer->transferProducts()->create([
                    'product_id' => $product['product_id'],
                    'qty' => $product['qty'],
                ]);
                $product = [Product::find($product['product_id'])->name, $product['qty']];
                array_push($productsArray, $product);
            }
        }
        $notification = [
            'type' => 'Product Transfer Request',
            "tablehead" => ["Product Name", "Product Qty"],
            "tablebody" => $productsArray,
            'body' => "A product transfer request has been made. ",
        ];
        try {
            $this->updateNotification($notification);
        } catch (\Throwable $th) {
            return $this->sendMessage("Request sent successfully, with notification error", ["Products uploaded successfully, but mail notification error", json_encode($th)], 500);
        }

        return $this->sendMessage('Request sent');
    }

    public function getWarehouse($warehouse_id)
    {
        $warehouse = Warehouse::findOrFail($warehouse_id);
        $requests = Transfer::where([['from', $warehouse->id], ['approved_by_id', null]])->get();
        return $this->sendMessage(TransferResource::collection($requests));
    }

    public function accept(Request $request)
    {
        // return $request->all();
        $request->validate([
            'from_id' => 'required|exists:warehouses,id',
            'transfer_id' => 'required|exists:transfers,id',
            'products' => 'required_if:isIndividual,true',
            'isIndividual' => 'nullable',
        ]);
        $transfer = Transfer::where('id', $request->transfer_id)->with('transferProducts.product')->first();

        switch ($transfer->transfer_type) {
            case 'STORE_TO_STORE':
                /**
                 * Get all models
                 */
                $from = Store::where('id', $request->from_id)->first();
                $to = Store::where('id', $transfer->to)->first();
                /**
                 * Loop through the request and check if its available in-stock
                 */
                $requestedPcsNA = []; //Array of products not available
                $products_to_transfer = []; //Products to transfer
                if (!is_null($request->products)) { //Check if product has beed manually edited
                    $products_to_transfer = json_decode($request->products);
                } else {
                    $products_to_transfer = $transfer->transferProducts;
                }

                foreach ($products_to_transfer as $product) {
                    $tp = $transfer->transferProducts()
                        ->where('product_id', $product->product_id)
                        ->first()
                        ->update(['qty' => $product->qty]);

                    $from_store_stock = $from->storeStocks()->where('product_id', $product->product_id)->first();
                    if (is_null($from_store_stock) || $product->qty > $from_store_stock->qty_in_stock) {
                        /**
                         *  Get the reason why transfer failed
                         */
                        if (is_null($from_store_stock)) {
                            $reason = 'This product is not in the store';
                        } elseif ($product->qty > $from_store_stock->qty_in_stock) {
                            $reason = "The quantity requested ({$product->qty}) is more than the quality in the store ({$from_store_stock->qty_in_stock}).";
                        } else {
                            $reason = 'The product is not available';
                        }
                        /**
                         * Pushed failed product abd reason into an array
                         */
                        array_push($requestedPcsNA, ['name' => Product::find($product->product_id)->name, 'reason' => $reason]);
                    } else {
                        $from_store_stock->update([
                            'qty_in_stock' => $from_store_stock->qty_in_stock - $product->qty,
                        ]);
                        $to_store_stock = $to->storeStocks()->where('product_id', $product->id)->first();
                        if (!is_null($to_store_stock)) {
                            $to_store_stock->update([
                                'qty_in_stock' => $to_store_stock->qty_in_stock + $product->qty,
                            ]);
                        } else {
                            StoreStock::create([
                                'product_id' => $product->product_id,
                                'store_id' => $to->id,
                                'qty_in_stock' => $product->qty,
                            ]);
                        }
                    }
                }

                $transfer->update(['approved_by_id' => auth()->user()->id]);
                $notification = [
                    'type' => 'Product Transfer Accepted.',
                    "tablehead" => ["From", "To", "Code"],
                    "tablebody" => [[$from->name, $to->name, $transfer->code]],
                    'body' => "A product transfer request has been made. From {$from->name} to {$to->name}",
                ];
                try {
                    $this->updateNotification($notification);
                } catch (\Throwable $th) {
                    return $this->sendMessage("Products uploaded successfully, with notification error", ["Products uploaded successfully, but mail notification error", json_encode($th)], 500);
                }

                return $this->sendMessage(['status_message' => 'Product transfer request accepted', 'unmoved' => $requestedPcsNA]);

                break;
            case 'STORE_TO_WAREHOUSE':
                /**
                 * Get all models
                 */
                $store = Warehouse::where('id', $request->from_id)->first();
                $warehouse = Store::where('id', $transfer->to)->first();
                /**
                 * Loop through the request and check if its available in-stock
                 */
                $requestedPcsNA = []; //Array of products not available
                $products_to_transfer = []; //Products to transfer
                if (!is_null($request->products)) { //Check if product has beed manually edited
                    $products_to_transfer = json_decode($request->products);
                } else {
                    $products_to_transfer = $transfer->transferProducts;
                }
                foreach ($products_to_transfer as $product) {
                    $transfer->transferProducts()
                        ->where('product_id', $product->product_id)
                        ->first()
                        ->update(['qty' => $product->qty]);
                    $ssp = $store->storeStocks()->where('product_id', $product->product_id)->first();
                    if (is_null($ssp) || $product->qty > $ssp->qty_in_stock) {
                        /**
                         *  Get the reason why transfer failed
                         */
                        if (is_null($ssp)) {
                            $reason = 'This product is not in the store';
                        } elseif ($product->qty > $ssp->qty_in_stock) {
                            $reason = "The quantity requested ({$product->qty}) is more than the quality in the store ({$ssp->qty_in_stock}).";
                        } else {
                            $reason = 'The product is not available';
                        }
                        /**
                         * Pushed failed product abd reason into an array
                         */
                        array_push($requestedPcsNA, ['name' => Product::find($product->product_id)->name, 'reason' => $reason]);
                    } else {
                        $ssp->update([
                            'qty_in_stock' => $ssp->qty_in_stock - $product->qty,
                        ]);
                        $ws = $warehouse->warehouseStocks()->where('product_id', $product->id)->first();
                        if (!is_null($ws)) {
                            $ws->update([
                                'qty_in_stock' => $ws->qty_in_stock + $product->qty,
                            ]);
                        } else {

                            WarehouseStock::create([
                                'product_id' => $product->product_id,
                                'warehouse_id' => $warehouse->id,
                                'qty_in_stock' => $product->qty,
                            ]);
                        }
                    }
                }
                $transfer->update(['approved_by_id' => auth()->user()->id]);
                $notification = [
                    'type' => 'Product Transfer Accepted.',
                    "tablehead" => ["From", "To", "Code"],
                    "tablebody" => [[$store->name, $warehouse->name, $transfer->code]],
                    'body' => "A product transfer request has been made. From {$store->name} to {$warehouse->name}",
                ];
                try {
                    $this->updateNotification($notification);
                } catch (\Throwable $th) {
                    return $this->sendMessage("Products uploaded successfully, with notification error", ["Products uploaded successfully, but mail notification error", json_encode($th)], 500);
                }
                return $this->sendMessage(['status_message' => 'Product transfer request accepted', 'unmoved' => $requestedPcsNA]);

                break;
            case 'WAREHOUSE_TO_STORE':
                /**
                 * Get all models
                 */
                $transfer = Transfer::where('id', $request->transfer_id)->with('transferProducts')->first();
                $warehouse = Warehouse::where('id', $request->from_id)->first();
                $store = Store::where('id', $transfer->to)->first();

                /**
                 * Loop through the request and check if its available in-stock
                 */
                $requestedPcsNA = []; //Array of products not available
                $products_to_transfer = []; //Products to transfer
                if (gettype($request->products) !== 'array' || count($request->products) < 1) { //Check if product has been manually edited
                    $products_to_transfer = $transfer->transferProducts;
                    Log::info($products_to_transfer);
                } else {
                    $products_to_transfer = json_decode($request->products);
                    Log::info($products_to_transfer);
                }

                foreach ($products_to_transfer as $product) {
                    // Update this product incase it has been adjusted
                    $transfer->transferProducts()
                        ->where('product_id', $product->product_id)
                        ->first()
                        ->update(['qty' => $product->qty]);

                    $wsp = $warehouse->warehouseStocks()->where('product_id', $product->product_id)->first();
                    if (!$wsp || $product->qty > $wsp->qty_in_stock) {
                        /**
                         *  Get the reason why transfer failed
                         */
                        if (!$wsp) {
                            $reason = 'This product is not in the warehouse';
                        } elseif ($product->qty > $wsp->qty_in_stock) {
                            $reason = "The quantity requested ({$product->qty}) is more than the quality in the warehouse ({$wsp->qty_in_stock}).";
                        } else {
                            $reason = 'The product is not available';
                        }
                        /**
                         * Pushed failed product abd reason into an array
                         */
                        array_push($requestedPcsNA, ['name' => Product::find($product->product_id)->name, 'reason' => $reason]);
                    } else {
                        $wsp->update([
                            'qty_in_stock' => $wsp->qty_in_stock - $product->qty,
                        ]);
                        Log::info('--- updated stock ----');
                        $ss = $store->storeStocks()->where('product_id', $product->id)->first();
                        Log::info($ss);
                        if (!is_null($ss)) {
                            $ss->update([
                                'qty_in_stock' => $ss->qty_in_stock + $product->qty,
                            ]);
                            Log::info('--- updated stock quantity----');
                        } else {

                            StoreStock::create([
                                'store_id' => $store->id,
                                'qty_in_stock' => $product->qty,
                                'product_id' => $product->product_id,
                            ]);
                            Log::info('---  stock created----');
                        }
                    }
                }
                $transfer->update(['approved_by_id' => auth()->user()->id]);
                Log::info('---  transfer approved----');

                $notification = [
                    'type' => 'Product Transfer Accepted.',
                    "tablehead" => ["From", "To", "Code"],
                    "tablebody" => [[$warehouse->name, $store->name, $transfer->code]],
                    'body' => "A product transfer request has been made. From {$store->name} to {$warehouse->name}",
                ];
                try {
                    $this->updateNotification($notification);
                } catch (\Throwable $th) {
                    return $this->sendMessage("Products uploaded successfully, with notification error", ["Products uploaded successfully, but mail notification error", json_encode($th)], 500);
                }

                return $this->sendMessage(['status_message' => 'Product transfer request accepted', 'unmoved' => $requestedPcsNA]);
                break;
            case 'WAREHOUSE_TO_WAREHOUSE':
                /**
                 * Get all models
                 */
                $warehouseFrom = Warehouse::where('id', $request->from_id)->first();
                $warehouseTo = Store::where('id', $transfer->to)->first();

                /**
                 * Loop through the request and check if its available in-stock
                 */
                $requestedPcsNA = []; //Array of products not available
                $products_to_transfer = []; //Products to transfer
                if (!is_null($request->products)) { //Check if product has beed manually edited
                    $products_to_transfer = json_decode($request->products);
                } else {
                    $products_to_transfer = $transfer->transferProducts;
                }

                foreach ($products_to_transfer as $product) {
                    $tp = $transfer->transferProducts()
                        ->where('product_id', $product->product_id)
                        ->first()
                        ->update(['qty' => $product->qty]);
                    $wsp = $warehouseFrom->warehouseStocks()->where('product_id', $product->product_id)->first();
                    if (!$wsp || $product->qty > $wsp->qty_in_stock) {
                        /**
                         *  Get the reason why transfer failed
                         */
                        if (!$wsp) {
                            $reason = 'This product is not in the warehouse';
                        } elseif ($product->qty > $wsp->qty_in_stock) {
                            $reason = "The quantity requested ({$product->qty}) is more than the quality in the warehouse ({$wsp->qty_in_stock}).";
                        } else {
                            $reason = 'The product is not available';
                        }
                        /**
                         * Pushed failed product abd reason into an array
                         */
                        array_push($requestedPcsNA, ['name' => Product::find($product->product_id)->name, 'reason' => $reason]);
                    } else {
                        $wsp->update([
                            'qty_in_stock' => $wsp->qty_in_stock - $product->qty,
                        ]);
                        $ss = $warehouseTo->warehouseStocks()->where('product_id', $product->id)->first();
                        if (!is_null($ss)) {
                            $ss->update([
                                'qty_in_stock' => $ss->qty_in_stock + $product->qty,
                            ]);
                        } else {
                            WarehouseStock::create([
                                'product_id' => $product->product_id,
                                'warehouse_id' => $warehouseTo->id,
                                'qty_in_stock' => $product->qty,
                            ]);
                        }
                    }
                }
                $transfer->update(['approved_by_id' => auth()->user()->id]);
                $notification = [
                    'type' => 'Product Transfer Accepted.',
                    "tablehead" => ["From", "To", "Code"],
                    "tablebody" => [[$warehouseFrom->name, $warehouseTo->name, $transfer->code]],
                    'body' => "A product transfer request has been made. From {$warehouseFrom->name} to {$warehouseTo->name}",
                ];
                try {
                    $this->updateNotification($notification);
                } catch (\Throwable $th) {
                    return $this->sendMessage("Products uploaded successfully, with notification error", ["Products uploaded successfully, but mail notification error", json_encode($th)], 500);
                }

                return $this->sendMessage(['status_message' => 'Product transfer request accepted', 'unmoved' => $requestedPcsNA]);
                break;
            default:
                break;
        }
    }
    public function deny(Request $request)
    {
        $rq = $request->validate([
            'transfer_id' => 'required|exists:transfers,id',
        ]);
        $transfer = Transfer::where('id', $request->transfer_id)->first();
        $transfer->delete();
        return $this->sendMessage("Deleted");
    }
    public function storeTransfer($id)
    {
        $transfer = Transfer::where('to', $id)
            ->where('transfer_type', 'WAREHOUSE_TO_STORE')
            ->with('transferProducts.product')->latest()->get();
        return $this->sendMessage(TransferResource::collection($transfer));
    }
    public function getRequest($type, $id)
    {
        $transfer = Transfer::where('from', $id)
            ->where('transfer_type', $type)
            ->where('approved_by_id', null)
            ->with('transferProducts.product')->latest()->get();
        return $this->sendMessage(TransferResource::collection($transfer));
    }
}
