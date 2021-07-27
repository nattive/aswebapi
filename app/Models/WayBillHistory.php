<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WayBillHistory extends Model
{
     protected $guarded = ['id'];
    use HasFactory;

    /**
     * Get the product that owns the WayBillHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the waybill that owns the WayBillHistory
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function waybill()
    {
        return $this->belongsTo(Waybill::class);
    }
}
