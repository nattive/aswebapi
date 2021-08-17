<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Store;
use App\Models\StoreStock;
use App\Traits\Helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class InvoiceController extends BaseController
{
    use Helpers;
    public function index()
    {
        $invoices = Invoice::with(["invoiceItems.product", "paymentModes"])->get();
        return $this->sendMessage($invoices);
    }
    public function store(InvoiceRequest $request)
    {
        $customer = '';
        $store = Store::find($request->store_id);
        if ($request->customerId) {
            $customer = Customer::find($request->customerId);
        } else {
            $customer = Customer::where('phonenumer', $request->customerPhone)->orWhere('email', $request->customerEmail)->first();
            if (is_null($customer)) {
                $customer = Customer::create([
                    'phonenumer' => $request->customerPhone,
                    'email' => $request->customerEmail,
                    'name' => $request->customerName,
                    'address' => $request->address,
                ]);

            }

        }
        $invoicesCount = count(Invoice::all())  + 1;
        $code =  $store->short_code . '/' . Carbon::now()->format('m') . '0' . $invoicesCount;
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
                return $this->sendMessage('Product out of stock',['Product out of stock'], false, 422);
                // return $this->sendMessage('Product out of stock', [compact('ssp')], false, 422);
            }
            $ssp->update([
                'qty_in_stock' => $ssp->qty_in_stock - $inv['quantity'],
            ]);
        }
        $invoiceData = [
            'type' => 'Invoice Created',
            'invoice' => $invoice,
            'store' => $store
        ];
        $this->updateNotification($invoiceData);
        foreach ($request->paymentInformation as $paymentInfo) {
            $invoice->paymentModes()->create([
                'amount' => $paymentInfo['amount'],
                'type' => $paymentInfo['type'],
                'due_date' => Arr::exists($paymentInfo, 'due_date') ?? $paymentInfo['due_date'],
            ]);
        }

        return $this->sendMessage('Invoice created!');
    }
    public function toJson(Request $request)
    {
        return $request->all();
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
        return $this->sendMessage(null, ['This invoice is not valid'], false, 404);

    }
    public function filterBetweenDates(Request $request)
    {
        $r = $request->validate([
            'dates' => 'required|array',
            'store_id' => 'integer',
        ]);
        $from = Carbon::parse($request->dates[0]);
        if (array_key_exists(1, $request->dates)) {
            $to = Carbon::parse($request->dates[1]);
        } else {
            $to = Carbon::now();
        }
        // $to = Carbon::parse($request->to);
        if (is_null($request->store_id)) {
            $invoice = Invoice::whereBetween('created_at', [$from, $to])->get();
            return $this->sendMessage($invoice);
        }
        $invoice = Invoice::where('store_id', $request->store_id)->whereBetween('created_at', [$from, $to])->get();
        return $this->sendMessage($invoice);
    }
    public function filterByCode(Request $request)
    {
        $r = $request->validate([
            'code' => 'required',
            'store_id' => 'integer',
        ]);
        if (is_null($request->store_id)) {
            $invoice = Invoice::where(["code", $request->code])->get();
            return $this->sendMessage($invoice);
        }
        $invoice = Invoice::where([['store_id', $request->store_id], ["code", $request->code]])->get();
      if (is_null($invoice)) {
          return $this->sendMessage(null, ['The invoice is not found'], false, 404);

      }
        return $this->sendMessage(InvoiceResource::collection($invoice));

    }
    public function filterToday(Request $request)
    {
        $r = $request->validate([
            'store_id' => 'integer',
        ]);
        $now = Carbon::now();
        $invoice = Invoice::whereBetween('created_at', $now)->get();
        return $this->sendMessage(InvoiceResource::collection($invoice));
    }
    public function chartData($id)
    {

        $start = new Carbon("first day of this month");
        $end = Carbon::now();
        $invoices = Invoice::where('store_id', $id)->whereBetween('created_at', [$start, $end])->get()->groupBy(function ($d) {
            return Carbon::parse($d->created_at)->format('d');
        });
        return $this->sendMessage($invoices);

    }
}
