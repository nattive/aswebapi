<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreStock;
use App\Traits\Helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class InvoiceController extends BaseController
{
    use Helpers;
    public function index()
    {
        $invoices = Invoice::with(["invoiceItems.product", "paymentModes"])->latest()->get();
        return $this->sendMessage(InvoiceResource::collection($invoices));
    }
    public function store(InvoiceRequest $request)
    {
        $customer = '';
        $store = Store::find($request->store_id);
        if (!is_null($request->customerId)) {
            $customer = Customer::find($request->customerId);
        } else {
            $customer = Customer::where('phonenumer', $request->customerPhone)->first();
            if (is_null($customer)) {
                $customer = Customer::create([
                    'phonenumer' => $request->customerPhone,
                    'email' => $request->customerEmail,
                    'name' => $request->customerName,
                    'address' => $request->address,
                ]);

            }

        }
        // return  $customer;
        $invoicesCount = count(Invoice::all()) + 1;
        $code = $store->short_code . ' /INV/' . Carbon::now()->format('m') . '0' . $invoicesCount;
        $invoice = $customer->invoices()->create([
            'code' => $code,
            'generated_by_user_id' => auth('sanctum')->user()->id,
            'customer_id' => $customer->id,
            'total_amount' => $request->totalAmount,
            'store_id' => $request->store_id,
            'discount_id' => $request->discount_id,
        ]);
        foreach ($request->invoiceItem as $inv) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $inv['productId'],
                'qty' => $inv['quantity'],
                'amount' => $inv['total'],
                'ctn_quantity' => $inv['ctn_quantity'],
            ]);
            $ssp = StoreStock::where([["store_id", $request->store_id], ['product_id', $inv['productId']]])->first();
            if (!$ssp) {
                return $this->sendMessage('Product does not exist in store', ['Product does not exist in store'], false, 422);
            }
            if ($ssp->qty_in_stock < $inv['quantity']) {
                return $this->sendMessage('Product out of stock', ['Product out of stock'], false, 422);
                // return $this->sendMessage('Product out of stock', [compact('ssp')], false, 422);
            }
            if ($inv['ctn_quantity']) {
                $product = Product::find($inv['productId']);
                $pcs = $product->pcs_per_ctn * $inv['ctn_quantity'];
            } else {
                $pcs = $inv['quantity'];
            }
            $ssp->update([
                'qty_in_stock' => $ssp->qty_in_stock - $pcs,
            ]);
        }

        $invoiceData = [
            'code' => $code,
            'type' => 'Invoice Created',
            'invoice' => new InvoiceResource($invoice),
            'store' => $store,
            'invoiceItems' => $invoice->invoiceItems()->with('product')->latest()->get(),
            "tablehead" => ["Customer Name", "Invoice Code", "Amount", "Store"],
            "tablebody" => [[$request->customerName, $code, $request->totalAmount, $store->name]],
        ];
        foreach ($request->paymentInformation as $paymentInfo) {
            $invoice->paymentModes()->create([
                'amount' => $paymentInfo['amount'],
                'type' => $paymentInfo['type'],
                'due_date' => Arr::exists($paymentInfo, 'due_date') ? $paymentInfo['due_date'] : null,
            ]);
        }
        $this->updateNotification($invoiceData);

        return $this->sendMessage(new InvoiceResource($invoice));
    }
    public function toJson(Request $request)
    {
        return $request->all();
    }
    public function storeinvoice($id)
    {
        $invoices = Invoice::where('store_id', $id)->with(["invoiceItems.product", "paymentModes"])->latest()->get();
        return $this->sendMessage(InvoiceResource::collection($invoices));
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
        $notification = [
            'type' => 'Invoice Retract Request',
            'body' => "An attendant has requested for an Invoice to be Retracted. Reason: {$request->reason}",
            "tablehead" => ["Customer Name", "Invoice Code", "Amount", "Store"],
            "tablebody" => [[$invoice->customer->name, $invoice->code, $invoice->total_amount, $invoice->store?->name]],
        ];
        try {
            $this->updateNotification($notification);
        } catch (\Throwable$th) {
            return $this->sendMessage("Invoice reversed request sent, with notification error", ["Products uploaded successfully, but mail notification error", json_encode($th)], 500);
        }

        return $this->sendMessage("Invoice reversed request sent");
    }

    public function retractRequest()
    {
        $user = auth("sanctum")->user();
        if ($user->role === 'SUPERVISOR' || $user === 'DIRECTOR') {
            $invoices = Invoice::where('reversed_status', '=', "Pending")->with("store")->get();
            return $this->sendMessage(InvoiceResource::collection($invoices));
        }
        $invoices = Invoice::where('reversed_status', '=', "Pending")
            ->whereHas("store", function ($query) use ($user) {
                return $query->where("supervisor_id", $user->id);
            })->latest()->get();
        return $this->sendMessage(InvoiceResource::collection($invoices));
    }
    public function acceptRetract($invoice_id)
    {
        $invoice = Invoice::find($invoice_id);
        // return $invoice->invoiceItems;
        if (!is_null($invoice)) {
            foreach ($invoice->invoiceItems as $invoiceItem) {
                $ssp = StoreStock::where([["store_id", $invoice->store_id], ["product_id", $invoiceItem->product_id]])->first();
                if (!is_null($ssp)) {
                    $ssp->update([
                        "qty_in_stock" => $ssp->qty_in_stock + number_format($invoiceItem->qty),
                    ]);
                }
            }
            $invoice->update([
                "reversed_approved_id" => auth("sanctum")->user()->id,
                "reversed_status" => "successful",
                "reversed" => true,
            ]);
            $notification = [
                'type' => 'Invoice Retracted',
                'body' => "An invoice has been retracted",
                "tablehead" => ["Customer Name", "Invoice Code", "Amount", "Store"],
                "tablebody" => [[$invoice->customer->name, $invoice->code, $invoice->total_amount, $invoice->store?->name]],
            ];
            try {
                $this->updateNotification($notification);
            } catch (\Throwable$th) {
                return $this->sendMessage("Invoice reversed, with notification error", ["Products uploaded successfully, but mail notification error", json_encode($th)], 500);
            }

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
            $invoice = Invoice::whereBetween('created_at', [$from, $to])->latest()->get();
            return $this->sendMessage(InvoiceResource::collection($invoice));

        }
        $invoice = Invoice::where('store_id', $request->store_id)->whereBetween('created_at', [$from, $to])->latest()->get();
        return $this->sendMessage(InvoiceResource::collection($invoice));
    }
    public function filterByCode(Request $request)
    {
        $r = $request->validate([
            'code' => 'required',
            'store_id' => 'integer|nullable',
        ]);
        if (is_null($request->store_id)) {
            $invoice = Invoice::where("code", $request->code)->latest()->get();
            return $this->sendMessage(InvoiceResource::collection($invoice));
        }
        $invoice = Invoice::where('store_id', $request->store_id)->where("code", $request->code)->latest()->get();
        if (is_null($invoice)) {
            return $this->sendMessage(null, ['The invoice is not found'], false, 404);

        }
        return $this->sendMessage(InvoiceResource::collection($invoice));

    }
    public function filterToday(Request $request)
    {
        $r = $request->validate([
            'store_id' => 'integer|nullable',
        ]);
        if (is_null($request->store_id)) {
            $invoice = Invoice::whereDate('created_at', Carbon::today())->latest()->get();
        } else {
            $invoice = Invoice::where('store_id', $request->store_id)
                ->whereDate('created_at', Carbon::today())->latest()->get();
        }

        return $this->sendMessage(InvoiceResource::collection($invoice));
    }
    public function filterThisWeek(Request $request)
    {
        $r = $request->validate([
            'store_id' => 'integer | nullable',
        ]);
        if (!is_null($request->store_id)) {
            $invoice = Invoice::where('store_id', $request->store_id)
                ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->latest()->get();
            return $this->sendMessage(InvoiceResource::collection($invoice));
        }
        $invoice = Invoice::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->latest()->get();

        return $this->sendMessage(InvoiceResource::collection($invoice));
    }
    public function filterThisMonth(Request $request)
    {
        $r = $request->validate([
            'store_id' => 'integer',
        ]);
        if (is_null($request->store_id)) {
            $invoice = Invoice::whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->latest()->get();
        } else {
            $invoice = Invoice::where('store_id', $request->store_id)
                ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->latest()->get();
        }
        return $this->sendMessage(InvoiceResource::collection($invoice));
    }
    public function filterThisYear(Request $request)
    {
        $r = $request->validate([
            'store_id' => 'integer|nullable',
        ]);
        if (is_null($request->store_id)) {
            $invoice = Invoice::whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])->latest()->get();

        } else {
            $invoice = Invoice::where('store_id', $request->store_id)
                ->whereBetween('created_at', [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()])->latest()->get();
        }

        return $this->sendMessage(InvoiceResource::collection($invoice));
    }
    public function chartData($id)
    {
        $invoices = Invoice::where('store_id', $id)->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->latest()->get()->groupBy(function ($d) {
            return Carbon::parse($d->created_at)->format('D-d');
        });
        return $this->sendMessage($invoices);
    }
    public function debt(Request $request)
    {
        if (is_null($request->store_id)) {
            $invoice = Invoice::whereHas('paymentModes', function ($query) {
                $query->where('type', 'debt');
            })->get();

        } else {
            $invoice = Invoice::where('store_id', $request->store_id)
                ->whereHas('paymentModes', function ($query) {
                    $query->where('type', 'debt');
                })->get();
        }

        return $this->sendMessage(InvoiceResource::collection($invoice));

    }
}
