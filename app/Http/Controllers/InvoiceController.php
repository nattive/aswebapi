<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Store;
use App\Models\StoreStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class InvoiceController extends BaseController
{
    public function index()
    {
        $invoices = Invoice::with(["invoiceItems.product", "paymentModes"])->get();
        return $this->sendMessage($invoices);
    }
    public function store(InvoiceRequest $request)
    {
        $customer = '';
        if ($request->customerId) {
            $customer = Customer::find($request->customerId);
        } else {
            $customer = Customer::where('phonenumer', $request->customerId)->orWhere('email', $request->customerEmail)->first();
            if (is_null($customer)) {
                $customer = Customer::findOrNew([
                    'phonenumer' => $request->customerPhone,
                    'email' => $request->customerEmail,
                    'name' => $request->customerName,
                    'address' => $request->address,
                ]);

            }

        }
        $invoicesCount = Invoice::all()->count();
        $code = 'ONI' . '/' . Carbon::now()->format('m') . '0' . $invoicesCount + 1;
        $invoice = $customer->invoices()->create([
            'code' => $code,
            'generated_by_user_id' => auth('sanctum')->user()->id,
            'customer_id' => $customer->id,
            'total_amount' => $request->totalAmount,
            'store_id' => $request->store_id,
        ]);
        foreach ($request->invoiceItem as $inv) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $inv['productId'],
                'qty' => $inv['quantity'],
                'amount' => $inv['total'],
            ]);
            $ssp = StoreStock::where([["store_id", $request->store_id], ['product_id', $inv['productId']]])->first();
            if ($ssp->qty_in_stock < $inv['quantity']) {
                return $this->sendMessage(null, [compact('ssp')], false, 422);
            }
            $ssp->update([
                'qty_in_stock' => $ssp->qty_in_stock - $inv['quantity'],
            ]);
        }
        Log::info("invoice created");
        foreach ($request->paymentInformation as $paymentInfo) {
            $invoice->paymentModes()->create([
                'amount' => $paymentInfo['amount'],
                'type' => $paymentInfo['type'],
                'due_date' => Arr::exists($paymentInfo, 'due_date') ?? $paymentInfo['due_date'],
            ]);
        }

        return $this->sendMessage('Invoice created!');
    }
    public function storeinvoice($id)
    {
        $invoices = Invoice::where('store_id', $id)->with(["invoiceItems.product", "paymentModes"])->get();
        return $this->sendMessage($invoices);
    }
    public function retract(Request $request, $id)
    {
        $data = $request->validate([
            'invoice_id' => 'required',
            'reason' => 'required',
        ]);
        if (auth("sanctum")->user()->id !== Store::find($id)->supervisor_id) {
            $this->sendMessage(null, ['You are not authorized to perform this action'], false, 401);
        }
        $invoice = Invoice::find($request->invoice_id);
        $invoice->update([
            "reversed_status" => "Pending",
            "reason" => $request->reason,
        ]);
        return $this->sendMessage("Invoice reversed request sent");
    }

    public function retractRequest()
    {
        $invoices = Store::where("supervisor_id", auth("sanctum")->user()->id)->with(["invoices" => function ($query) {
            return $query->where('reversed_status', '=', "Pending");
        }])->get();
        return $this->sendMessage($invoices);
    }
    public function acceptRetract($invoice_id)
    {
        $invoice = Invoice::find($invoice_id);
        foreach ($invoice->invoiceItems as $invoiceItem) {
            $ssp = StoreStock::where([["store_id", $invoice->store_id], ["product_id", $invoiceItem->product_id]])->first();
            if (!is_null($ssp)) {
                $ssp->update([
                    "qty_in_stock" => $ssp->qty_in_stock + $invoiceItem->qty,
                ]);
            }
        }
        if (!is_null($invoice)) {
            $invoice->update([
                "reversed_approved_id" => auth("sanctum")->user()->id,
                "reversed_status" => "successful",
                "reversed" => true,
            ]);

            return $this->sendMessage("Invoice reversed");
        }
        return $this->sendMessage(null, ['This invoice is not valid'], false);

    }
}
