<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\User;
use App\Models\Warehouse;
use App\Traits\Helpers;
use Illuminate\Http\Client\Request;

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
