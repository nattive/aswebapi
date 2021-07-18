<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Get all of the invoiceItems for the Invoice
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
    /**
     * Get all of the paymentmodes for the Invoice
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function paymentModes()
    {
        return $this->belongsToMany(PaymentMode::class, 'invoice_payment_mode', 'payment_mode_id', 'invoice_id');
    }
    /**
     * Get the customer associated with the Invoice
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }
    public function getGeneratedByUserIdAttribute($value)
    {
       return User::find($value);
    }
    public function getCreatedAtAttribute($value)
    {
       return Carbon::parse($value)->format('d/m/y');
    }
}
