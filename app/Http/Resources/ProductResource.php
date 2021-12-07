<?php

namespace App\Http\Resources;

use App\Models\InvoiceItem;
use App\Models\Waybill;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $sales = InvoiceItem::where('product_id', $this->id)
        ->whereHas('invoice', function ($query) {
            $query->where('reversed', '!=', true);
        })
        ->with("invoice.store")->get();
        // $waybill = Waybill::wherehas('products',  $this->id) -> with('warehouse')->get()
        $id = $this->id;
        return [
            'created_at' => Carbon::parse($this->created_at)->toDateTimeString(),
            'id' => $this->id,
            'last_edit_by_id' => $this->last_edit_by_id,
            'name' => $this->name,
            'price' => $this->price,
            'pcs_per_ctn' => $this->pcs_per_ctn,
            'storeStocks' => $this->storeStocks()->with('store')->get(),
            'warehouseStock' => $this->warehouseStock()->with('warehouse')->get(),
            'transferProducts' => TransferProductResource::collection($this->transferProducts),
            'updated_at' => Carbon::parse($this->created_at)->toDateTimeString(),
            'waybill' => $this->waybills()->with(["warehouse", "waybillHistory" => function($query) use ($id) {
                $query->where("product_id", $id);
            }])->get(),
            "sales" => $sales,
        ];
    }
}
