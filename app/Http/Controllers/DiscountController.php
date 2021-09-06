<?php

namespace App\Http\Controllers;

use App\Models\Discount;
use Illuminate\Http\Request;

class DiscountController extends BaseController
{
    public function index()
    {
        return $this->sendMessage(Discount::all());
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|in:percent,amount',
            'discount' => 'required',
            'code' => 'nullable',
        ]);
        Discount::create($data);
        return $this->sendMessage('Discount created!');
    }
    public function destroy($id)
    {
        Discount::find($id)->delete();
        return $this->sendMessage('Discount deleted!');
    }
}
