<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
/**
 * Get the invoice that owns the InvoiceItem
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    /**
     * Get the product that owns the InvoiceItem
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
