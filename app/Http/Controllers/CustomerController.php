<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvoiceResource;
use App\Models\Customer;
use App\Models\Invoice;
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
    public function customerInvoices($id)
    {
        $invoices = Invoice::where('customer_id', $id)
        ->with(['invoiceItems', 'paymentModes', 'customer', 'store'])->get();
         return $this->sendMessage(InvoiceResource::collection($invoices));
    }
}
