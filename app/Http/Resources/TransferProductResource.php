<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'created_at' =>  Carbon::parse($this->created_at)->format('d/M/y'),
            'id' => $this->id,
            'product_id' => $this->product_id,
            'qty' => $this->qty,
            'transfer' => new TransferResource($this->transfer)
        ];
    }
}
