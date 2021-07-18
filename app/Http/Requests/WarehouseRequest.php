<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WarehouseRequest extends FormRequest
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
            'name' => 'required|max:150',
            'short_code' => 'required|max:3',
            'address' => 'required|max:200',
            'supervisor_id' => 'integer|nullable',
            'warehouse_stock_id' => 'integer|nullable',
        ];
    }
}
