<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends BaseController
{
    // use Helper;
    public function index()
    {
        return $this->sendMessage(Store::with(['storeStocks.product'])->get());
    }
    public function store(StoreRequest $request)
    {
        $d = ["supervisor_id" => $request->supervisor_id ?? auth("sanctum")->user()->id];
        Store::create(array_merge($request->except("supervisor_id"), $d) );
        return $this->sendMessage("Store created successfully");
    }

    public function show($id)
    {
        $store = Store::where('id', $id)->with(['storeStocks.product'])->first();
        return $this->sendMessage($store);
    }

    public function edit(Request $request, $id)
    {
        $warehouse = Store::findOrFail($id);
        $warehouse->update($request->all());
        return $this->sendMessage("Store updated successfully");
    }
    public function destroy($id)
    {
        $warehouse = Store::findOrFail($id);
        $warehouse->delete();
        return $this->sendMessage("Store deleted successfully");
    }
}
