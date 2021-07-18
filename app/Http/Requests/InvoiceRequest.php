<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'address' => 'required_if:customerPhone,null',
            'customerId' => 'required_if:customerPhone,null',
            'customerName' => 'required_if:customerId,null',
            'customerPhone' => 'required_if:customerId,null',
            'store_id' => 'required',
            'invoiceItem' => 'required',
            'paymentInformation' => 'required',
            'totalAmount' => 'required',
        ];
    }
}
