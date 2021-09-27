<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\Waybill;
use App\Traits\Helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;

class WaybillController extends BaseController
{
    use Helpers;
    public function index()
    {
        return $this->sendMessage(Waybill::with(['warehouse', 'products'])->get());
    }

    public function filter(Request $request)
    {
        $request->validate([
            'type' => 'in:today,this_week,this_month,this_year,dates,code',
            'dates' => 'required_if:type,dates',
            'code' => 'required_if:type,code',
        ]);
        switch ($request->type) {
            case 'today':
                $now = Carbon::now();
                $waybill = Waybill::whereDate('created_at', $now)->get();
                return $this->sendMessage($waybill);

            case 'this_week':
                $start = new Carbon('first day of this week');
                $end = Carbon::now();
                $waybill = $this->filterWaybillBetweenDates($start, $end);
                return $this->sendMessage($waybill);

                break;

            case 'this_month':
                $start = new Carbon('first day of this month');
                $end = Carbon::now();
                $waybill = $this->filterWaybillBetweenDates($start, $end);
                return $this->sendMessage($waybill);

                break;

            case 'this_year':
                $start = new Carbon('first day of this year');
                $end = Carbon::now();
                $waybill = $this->filterWaybillBetweenDates($start, $end);
                return $this->sendMessage($waybill);

                break;

            case 'dates':
                $start = Carbon::parse($request->dates[0]);
                if (array_key_exists(1, $request->dates)) {
                    $end = Carbon::parse($request->dates[1]);
                } else {
                    $end = Carbon::now();
                }
                // $end = Carbon::parse($request->to);
                $waybill = $this->filterWaybillBetweenDates($start, $end);
                return $this->sendMessage($waybill);

                break;

            case 'code':
                $waybill = Waybill::where('code', $request->code)->get();
                return $this->sendMessage($waybill);

                break;

            default:
                return $this->sendMessage('filter invalid', ['Filter values are invalid'], false, 422);

                break;
        }

    }
    public function store(Request $request)
    {
        $wbNt = count(Waybill::all()) + 1;
        $wayBillRequest = $request->validate([
            'from' => 'nullable',
            'warehouse_id' => 'required|integer',
        ]);
        $warehouse = Warehouse::find($request->warehouse_id);
        if (is_null($warehouse)) {
            return $this->sendMessage(["Warehouse doesn't exist"], false, 404);
        }

        $code = $warehouse->short_code . '/WB/' . Carbon::now()->format('m') . '0' . $wbNt;
        $waybill = Waybill::create([
            'code' => $code,
            'from' => $request->from,
            'warehouse_id' => $request->warehouse_id,
        ]);
        // notification
        $notification = [
            'type' => 'Waybill Created',
            "tablehead" => ["code", "from", "warehouse"],
            "tablebody" => [$code, $request->from, $waybill->warehouse()->first()->name],
            'body' => "A waybill has been created",
        ];
        try {
            $this->updateNotification($notification);
        } catch (\Throwable$th) {
            return $this->sendMessage($waybill, ["Waybill successfully, but mail notification error"]);
        }
        return $this->sendMessage($waybill);
    }

    public function show($id)
    {
        $waybill = Waybill::where('id', $id)->with(['warehouse', 'products.storeStocks.store', 'waybillHistory.product'])->first();
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
    public function chartData()
    {

        $start = new Carbon("first day of this year");
        $end = Carbon::now();
        $waybill = Waybill::whereBetween('created_at', [$start, $end])->get()->groupBy(function ($d) {
            return Carbon::parse($d->created_at)->format('m');
        });
        return $this->sendMessage($waybill);

    }
}
