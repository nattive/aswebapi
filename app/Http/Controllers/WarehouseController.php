<?php

namespace App\Http\Controllers;

use App\Http\Requests\WarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use Illuminate\Http\Client\Request;

class WarehouseController extends BaseController
{

    public function index()
    {
        $warehouse = Warehouse::with(['warehouseStocks.product'])->get();
         return $this->sendMessage(WarehouseResource::collection($warehouse));
    }

    public function store(WarehouseRequest $request)
    {
        $warehouse = $request->validated();
        $data = ['supervisor_id' => $request->supervisor_id ?? auth('sanctum')->user()->id];
        Warehouse::create(array_merge($warehouse, $data));
        return $this->sendMessage("Warehouse created successfully");

    }

    public function show($id)
    {
        $warehouse = Warehouse::where('id', $id)->with(['warehouseStocks.product','waybills.products'])->first();
       return $this->sendMessage($warehouse);

    }

    public function edit(Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouse->update($request->all());
        return $this->sendMessage("Warehouse updated successfully");

    }
}
