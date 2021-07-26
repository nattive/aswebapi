<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Store;
use Carbon\Carbon;

class ReportController extends BaseController
{
    public function storesReport()
    {
        $stores = Store::all();
        $report = [];
        foreach ($stores as $store) {
            $stockWorth = 0;
            foreach ($store->storeStocks as $ss) {
                $tt = $ss->qty_in_stock * $ss->product()->first()->price;
                $stockWorth += $tt;
            }
            $totalDebt = 0;
            $totalDebtDay = 0;
            $debtInvoiceCount = 0;

            $si_today = $store->invoices()->where('created_at', Carbon::now())
                ->whereHas('paymentModes', function ($query) {
                    return $query->where('type', 'debt');
                })->with('invoiceItems')->get();
            // Query invoices that has attribute type === debt on payment mode
            $storeInvoices = $store->invoices()->whereHas('paymentModes', function ($query) {
                return $query->where('type', 'debt');
            })->with('invoiceItems', 'paymentModes')->get();

            $overdue = $store->invoices()->whereHas('paymentModes', function ($query) {
                return $query->where([['type', 'debt'], ['due_date', '<', Carbon::now()]]);
            })->with('invoiceItems', 'paymentModes')->get();

            foreach ($storeInvoices as $invoice) {
                $debtInvoiceCount += count($invoice->paymentModes);
                foreach ($invoice->paymentModes as $pm) {
                    $totalDebt += $pm->amount;
                }
            }

            array_push($report, [
                'store_name' => $store->name,
                'last_sale' => $store->invoices()->with('invoiceItems.product', 'paymentModes')->orderBy('created_at', 'desc')->first(),
                'today_sale' => $store->invoices()->where('created_at', Carbon::now())->orderBy('created_at', 'desc')->get(),
                'today_debt' => $si_today,
                'total_unpaid_debt' => $debtInvoiceCount,
                'total_sale_this_month' => $store->invoices()->whereBetween('created_at', [new Carbon("first day of this month"), Carbon::now()])->orderBy('created_at', 'desc')->get(),
                'total_debt' => $totalDebt,
                'stock_worth' => $stockWorth,
                'over_due_debt' => $overdue,
            ]);
        }
        return $this->sendMessage($report);
    }
    public function storeChat()
    {
        $start = new Carbon("first day of this month");
        $end = Carbon::now();
        $invoices = Invoice::whereBetween('created_at', [$start, $end])
            ->get()->groupBy(function ($d) {
            return Carbon::parse($d->created_at)->format('d');
        });
        return $this->sendMessage($invoices);

    }
}
