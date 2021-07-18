<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseStockResource extends JsonResource
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
            "qty_in_stock" => $this->qty_in_stock,
            "product" => $this->product,
            "updated" =>Carbon::parse($this->updated_at)->toDateTimeString(),
            "created" =>Carbon::parse($this->created_at)->toDateTimeString(),
        ];
    }
}
