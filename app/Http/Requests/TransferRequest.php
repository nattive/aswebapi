<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
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
            'to' => 'required',
            'from' => 'required',
            'transfer_type' => 'in:STORE_TO_STORE,STORE_TO_WAREHOUSE,WAREHOUSE_TO_STORE,WAREHOUSE_TO_WAREHOUSE',
            'products' => 'required',
        ];
    }
}
