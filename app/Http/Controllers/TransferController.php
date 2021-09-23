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
use Carbon\Carbon;
use Illuminate\Http\Request;

class TransferController extends BaseController
{
    public function store(TransferRequest $request)
    {
        $prevTrans = Transfer::where('from', $request->from_id)->get()->count();
        $count = $prevTrans + 1;
        $c = $count < 99 ? '00' . $count : '0' . $count;
        switch ($request->transfer_type) {
            case 'STORE_TO_STORE':
                $code = 'TP-STS/' . Carbon::now()->format('M') . '0' . $c;
                break;
            case 'STORE_TO_WAREHOUSE':
                $code = 'TP-STW/' . Carbon::now()->format('M') . '0' . $c;
                break;
            case 'WAREHOUSE_TO_STORE':
                $code = 'TP-WTS/' . Carbon::now()->format('M') . '0' . $c;
                break;
            case 'WAREHOUSE_TO_WAREHOUSE':
                $code = 'TP-WTW/' . Carbon::now()->format('M') . '0' . $c;
                break;
            default:
                break;
        }
        $transfer = Transfer::create(array_merge($request->except('products'), ['ref_code' => $code]));
        foreach ($request->products as $product) {
            $transfer->transferProducts()->create([
                'product_id' => $product['product_id'],
                'qty' => $product['qty'],
            ]);
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
        $rq = $request->validate([
            'from_id' => 'required|exists:warehouses,id',
            'transfer_id' => 'required|exists:transfers,id',
        ]);
        $transfer = Transfer::where('id', $request->transfer_id)->first();
        switch ($transfer->transfer_type) {
            case 'STORE_TO_STORE':
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
                foreach ($transfer->transferProducts as $product) {
                    $ssp = $store->storeStocks()->where('product_id', $product->product_id)->first();
                    if (is_null($ssp) || $product->qty > $ssp->qty_in_stock) {
                        /**
                         *  Get the reason why transfer failed
                         */
                        if (is_null($ssp)) {
                            $reason = 'This product is not in the warehouse';
                        } elseif ($product->qty > $ssp->qty_in_stock) {
                            $reason = "The quantity requested ({$product->qty}) is more than the quality in the warehouse ({$ssp->qty_in_stock}).";
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
                return $this->sendMessage(['status_message' => 'Product transfer request accepted', 'unmoved' => $requestedPcsNA]);

                break;
            case 'WAREHOUSE_TO_STORE':
                /**
                 * Get all models
                 */
                $transfer = Transfer::where('id', $request->transfer_id)->first();
                $warehouse = Warehouse::where('id', $request->from_id)->first();
                $store = Store::where('id', $transfer->to)->first();

                /**
                 * Loop through the request and check if its available in-stock
                 */
                $requestedPcsNA = []; //Array of products not available
                foreach ($transfer->transferProducts as $product) {
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
                        $ss = $store->storeStocks()->where('product_id', $product->id)->first();
                        if (!is_null($ss)) {
                            $ss->update([
                                'qty_in_stock' => $ss->qty_in_stock + $product->qty,
                            ]);
                        } else {

                            StoreStock::create([
                                'store_id' => $store->id,
                                'qty_in_stock' => $product->qty,
                                'product_id' => $product->product_id,
                            ]);
                        }

                    }
                }
                $transfer->update(['approved_by_id' => auth()->user()->id]);
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
                foreach ($transfer->transferProducts as $product) {
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
            ->with('transferProducts.product')->get();
        return $this->sendMessage(TransferResource::collection($transfer));

    }
}
