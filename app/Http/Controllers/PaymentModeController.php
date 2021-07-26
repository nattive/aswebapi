<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PaymentMode;
use Illuminate\Http\Request;

class PaymentModeController extends BaseController
{
    public function update(Request $request)
    {
        $v = $request->validate([
            'payment_mode_id' => 'required|integer',
            'amount' => 'required',
            'type' => 'required',
            'due_date' => 'required_if:type,debt',
        ]);
        $pm = PaymentMode::find($request->payment_mode_id);
        $invoice = Invoice::find($request->invoice_id);
        if (!is_null($pm)) {
            /**
             * if the new payment is not up to the debt
             * create a new payment
             * update the previous debt
             */
            if ($pm->amount > $request->amount) {
                $invoice->paymentModes()->create([
                    'amount' => $request->amount,
                    'type' => $request->type,
                ]);
                $pm->update([
                    'amount' => $pm->amount - $request->amount,
                ]);
                return $this->sendMessage('Payment updated');
            } elseif ($pm->amount === $request->amount) {
                $pm->update($request->except('payment_mode_id'));
                return $this->sendMessage('Payment updated');

            }
            return $this->sendMessage('Payment error', ['Payment is more than debt'], false, 422);
        }
    }
}
