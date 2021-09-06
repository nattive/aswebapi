<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRequest;
use App\Models\Store;
use App\Models\User;
use App\Traits\Helpers;
use Illuminate\Http\Request;

class StoreController extends BaseController
{
    use Helpers;
    public function index()
    {
        return $this->sendMessage(Store::with(['storeStocks.product'])->get());
    }
    public function store(StoreRequest $request)
    {
        $user = auth("sanctum")->user();
        $supervisor_id = $request->supervisor_id ?? auth("sanctum")->user()->id;
        $d = ["supervisor_id" => $supervisor_id];
        $store = Store::create(array_merge($request->except("supervisor_id"), $d));
        $date = date('dS F Y', strtotime($store->updated_at));
        $supervisor = User::find($supervisor_id);
        $invoiceData = [
            'greetings' => "Hi {$user->name}",
            "tablehead" => ["store Name", "Supervisor", "short code"],
            "tablebody" => [$store->name, $supervisor?->name, $store->short_code],
            'type' => 'store Created',
            'body' => 'A store has been created successfully.',
            'line1' => "A store named {$store->name} has been successfully created by {$user->name}, on {$date} See details below:",
        ];
        try {
            $this->updateNotification($invoiceData);
        } catch (\Throwable$th) {
            return $this->sendMessage("Store created successfully", ["Store created, but mail notification error"], 500);

        }

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
