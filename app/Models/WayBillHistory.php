<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WayBillHistory extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

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
     public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($this->updated_at)->toDateTimeString();
    }
}
