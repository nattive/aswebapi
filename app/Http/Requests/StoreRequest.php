<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
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
            'name' => 'required | max:150 |unique:stores,name',
            'short_code' => 'required|max:3|unique:stores,short_code',
            'address' => 'nullable',
            'supervisor_id' => 'nullable|integer',
            'store_stock_id' => 'nullable|integer',
        ];
    }
}
