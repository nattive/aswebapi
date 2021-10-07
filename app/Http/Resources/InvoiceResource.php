<?php

namespace App\Http\Resources;

use App\Models\Customer;
use App\Models\Discount;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            "id" => $this->id,
            "store" => $this->store,
            "code" => $this->code,
            "generated_by" => User::find($this->generated_by_user_id),
            "customer" => Customer::find($this->customer_id),
            "store_id" => $this->store_id,
            "reversed_status" => $this->reversed_status,
            "reversed" => $this->reversed,
            "total_amount" => $this->total_amount,
            "discount_id" => $this->discount_id,
            "invoice_items" => $this->invoiceItems()->with("product")->get(),
            "paymentModes" => $this->paymentModes,
            "discount" => $this->discount_id ? Discount::find($this->discount_id) : null,
            "created_at" => Carbon::parse($this->created_at)->toDateTimeString()
        ];
    }
}
