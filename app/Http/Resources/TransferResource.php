<?php

namespace App\Http\Resources;

use App\Models\Store;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TransferResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $to = [];
        switch ($this->transfer_type) {
            case 'WAREHOUSE_TO_STORE':
                $to = Store::findOrFail($this->to);
                $from = Warehouse::findOrFail($this->from);
                break;
            case 'WAREHOUSE_TO_WAREHOUSE':
                $to = Warehouse::findOrFail($this->to);
                $from = Warehouse::findOrFail($this->from);
                break;
            case 'STORE_TO_STORE':
                $to = Store::findOrFail($this->to);
                $from = Store::findOrFail($this->from);
                break;
            case 'STORE_TO_WAREHOUSE':
                $to = Store::findOrFail($this->to);
                $from = Warehouse::findOrFail($this->from);
                break;
            default:
                break;
        }

        return [
            'id' => $this->id,
            'ref_code' => $this->ref_code,
            'to' => $to,
            'from' => $from,
            'products' => $this->transferProducts()->with('product')->get(),
            'created_at' => Carbon::parse($this->created_at)->diffForHumans(),
        ];
    }
}
