<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Warehouse;
use App\Traits\Helpers;
use Illuminate\Http\Request;
use Carbon\Carbon;

class WarehouseController extends BaseController
{
    use Helpers;
    public function index()
    {
        $warehouse = Warehouse::with(['warehouseStocks.product'])->get();
        return $this->sendMessage(WarehouseResource::collection($warehouse));
    }

    public function store(WarehouseRequest $request)
    {
        $user = auth("sanctum")->user();
        $supervisor_id = $request->supervisor_id ?? $user->id;
        $warehouseData = $request->validated();
        $data = compact("supervisor_id");
        $warehouse = Warehouse::create(array_merge($warehouseData, $data));
        $supervisor = User::find($supervisor_id);
        $date = date('dS F Y', strtotime($warehouse->updated_at));
        $WarehouseData = [
            "tablehead" => ["Warehouse Name", "Supervisor", "short code", "Address"],
            "tablebody" => [$warehouse?->name, $supervisor?->name, $warehouse?->short_code, $warehouse?->address],
            'type' => 'Warehouse Created',
            'body' => 'A warehouse has been created successfully.',
            'line1' => "A warehouse named {$warehouse->name} has been successfully created by {$user->name}, on {$date} See details below:",
        ];
        try {
            $this->updateNotification($WarehouseData);
        } catch (\Throwable$th) {
            return $this->sendMessage("Store created successfully", ["Store created, but mail notification error"], 500);
        }
        return $this->sendMessage("Warehouse created successfully");

    }

    public function show($id)
    {
        $warehouse = Warehouse::where('id', $id)->with(['warehouseStocks.product', 'waybills.products'])->first();
        return $this->sendMessage($warehouse);

    }

    public function transferHistory($id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $toStore = Transfer::where('from', $id)->where('transfer_type','WAREHOUSE_TO_STORE')->with('transferProducts.product')->get();
        $toWh = Transfer::where('from', $id)->where('transfer_type','WAREHOUSE_TO_WAREHOUSE')->with('transferProducts.product')->get();
        $fromStore = Transfer::where('to', $id)->where('transfer_type','STORE_TO_WAREHOUSE')->with('transferProducts.product')->get();
        $fromWH = Transfer::where('to', $id)->where('transfer_type','STORE_TO_WAREHOUSE')->with('transferProducts.product')->get();
        return $this->sendMessage(compact('toStore', 'toWh', 'fromStore', 'fromWH'));
    }

    public function filterTransfer(Request $request, $id)
    {
        $request->validate([
            'type' => 'in:today,this_week,this_month,this_year,dates,code',
            'dates' => 'required_if:type,dates',
            'code' => 'required_if:type,code',
            'transferTYpe' => "nullable|in:from,to"
        ]);
        switch ($request->type) {
            case 'today':
                $now = Carbon::now();
                $transfer = Transfer::where('from', $id)->where('transfer_type','WAREHOUSE_TO_STORE')->whereDate('created_at', $now)->with('transferProducts.product')->get();
                return $this->sendMessage($transfer);

            case 'this_week':
                $start = Carbon::now()->copy()->firstWeekDay();
                $end = Carbon::now();
                $transfer = $this->filterTransferBetweenDates($start, $end, $id);
                return $this->sendMessage($transfer);

            case 'this_month':
                $start = Carbon::now()->copy()->firstOfMonth();
                $end = Carbon::now();
                $transfer = $this->filterTransferBetweenDates($start, $end, $id);
                return $this->sendMessage($transfer);

            case 'this_year':
                $start = Carbon::now()->copy()->firstOfYear();
                $end = Carbon::now();
                $transfer = $this->filterTransferBetweenDates($start, $end, $id);
                return $this->sendMessage($transfer);

            case 'dates':
                $start = Carbon::parse($request->dates[0]);
                if (array_key_exists(1, $request->dates)) {
                    $end = Carbon::parse($request->dates[1]);
                } else {
                    $end = Carbon::now();
                }
                // $end = Carbon::parse($request->to);
                $transfer = $this->filterTransferBetweenDates($start, $end, $id);
                return $this->sendMessage($transfer);

            case 'code':
                $waybill = Transfer::where('ref_code', $request->code)->with('transferProducts.product')->get();
                return $this->sendMessage($waybill);

            default:
                return $this->sendMessage('filter invalid', ['Filter values are invalid'], false, 422);

        }
    }

    public function edit(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->update($request->all());
        $user = auth("sanctum")->user();
        $date = date('dS F Y', strtotime($warehouse->updated_at));
        $WarehouseData = [
            'type' => 'Warehouse Updated',
            'body' => "Warehouse {$warehouse->name} has been just been updated",
            'line1' => "A warehouse named {$warehouse->name} has been successfully created by {$user->name}.",
        ];
        try {
            $this->updateNotification($WarehouseData);
        } catch (\Throwable$th) {
            return $this->sendMessage("Store created successfully", ["Store created, but mail notification error"], 500);
        }
        return $this->sendMessage("Warehouse updated successfully");

    }
}
