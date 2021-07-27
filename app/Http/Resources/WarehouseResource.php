<?php

namespace App\Http\Resources;

use App\Models\Transfer;
use App\Models\User;
use App\Models\WarehouseStock;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'short_code' => $this->short_code,
            'address' => $this->address,
            'supervisor' => User::find($this->supervisor_id),
            "Transferred" => Transfer::where("from", $this->id)->get(),
            'warehouseStocks' =>   WarehouseStockResource::collection($this->warehouseStocks),
            "out_stocked" => $this->warehouseStocks()->where("qty_in_stock", "<", 0)->get(),
            'waybills' => $this->waybills,
        ];
    }
}
