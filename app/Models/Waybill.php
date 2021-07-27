<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waybill extends Model
{
    use HasFactory;
     protected $guarded = ['id'];

    /**
     * Get the warehouse that owns the Waybill
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
    /**
     * Get all of the products for the Waybill
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'waybill_id');
    }
    public function getCreatedAtAttribute($value)
    {
        return  Carbon::parse($value)->format('d/m/Y');
    }
    /**
     * Get the waybillHistory associated with the Waybill
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function waybillHistory()
    {
        return $this->hasMany(waybillHistory::class);
    }
}
