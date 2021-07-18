<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends BaseController
{
    public function index()
    {
        return $this->sendMessage(Customer::all());
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:200',
            'phonenumer' => 'required|min:11',
            'email' => 'required|email',
            'address' => 'nullable',
        ]);
        Customer::create($data);
        return $this->sendMessage('Customer created successfully');
    }
}
