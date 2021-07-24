<?php

namespace App\Http\Resources;

use App\Models\Customer;
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
            "code" => $this->code,
            "generated_by" => User::find($this->generated_by_user_id),
            "customer" => Customer::find($this->customer_id),
            "store_id" => $this->store_id,
            "reversed_status" => $this->reversed_status,
            "reversed" => $this->reversed,
            "total_amount" => $this->total_amount,
            "invoice_items" => $this->invoiceItems()->with("product")->get(),
            "paymentModes" => $this->paymentModes,
            "created_at" => Carbon::parse($this->create_at)->format('d/M/y')
        ];
    }
}
