<?php

namespace App\Http\Resources;

use App\Models\Product;
use App\Models\StoreStock;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // 'store_stock' => $this->store_stock_id,
        $stock = [];
        $ss = StoreStock::findOrFail($this->store_stock_id);
        foreach ($ss as $stock) {
            array_push($stock, ['product' => Product::find($stock->product_id), 'Quantity' => $stock->qty_in_stock]);
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'short_code' => $this->short_code,
            'address' => $this->address,
            'supervisor' => User::find($this->supervisor_id),
            'store_stock' => $this->storeStocks,
            'stock' => $stock,
        ];
    }
}
