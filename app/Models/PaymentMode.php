<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMode extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * The invoices that belong to the PaymentMode
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'invoice_payment_mode', 'invoice_id', 'payment_mode_id');
    }

    public function getDueDateAttribute($value)
    {
        return Carbon::parse($value)->format('d/m/Y');
    }
}
