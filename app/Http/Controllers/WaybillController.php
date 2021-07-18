<?php

namespace App\Http\Controllers;

use App\Http\Requests\WayBillRequest;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Models\Waybill;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WaybillController extends BaseController
{
    public function index()
    {
        return $this->sendMessage(Waybill::with(['warehouse', 'products'])->get());
    }
    public function store(Request $request)
    {
        $wayBillRequest = $request->validate([
            'from' => 'nullable',
            'warehouse_id' => 'required|integer'
        ]);
        $warehouse = Warehouse::find($request->warehouse_id);
        if (is_null($warehouse)) {
            return $this->sendMessage(["Warehouse doesn't exist"], false, 404);
        }
        $allWaybills = count(Waybill::all());
        $code = $warehouse->short_code . '/' . Carbon::now()->format('M') . '/' . $allWaybills + 1;
        $waybill = Waybill::create([
            'code' => $code,
            'from' => $request->from,
            'warehouse_id' => $request->warehouse_id,
        ]);
        return $this->sendMessage( $waybill);
    }

    public function show($id)
    {
        $waybill = Waybill::where('id', $id)->with(['warehouse', 'products'])->first();
        return $this->sendMessage($waybill);
    }
    public function addProduct(Request $request, $id)
    {
        $data = $request->validate([
            'name' => 'required',
            'barcode' => 'required',
            'price' => 'nullable',
            'qty' => 'required',
        ]);
        $waybill = Waybill::where('id', $id)->first();
        $newProduct = Product::firstOrCreate([
            'name' => $request->name,
            'price' => $request->price,
        ]);
        $waybill->products()->create([
            'product_id' => $newProduct->id,
            'warehouse_id' => request('warehouse_id'),
            'qty_in_stock' => $request->qty,
        ]);
        return $this->sendMessage('Product uploaded successfully');
    }
}
