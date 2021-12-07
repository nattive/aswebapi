<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    /**
     * The warehouseStock that belong to the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function warehouseStock()
    {
        return $this->hasMany(WarehouseStock::class);
    }
    public function waybills()
    {
        return $this->belongsToMany(Waybill::class);
    }
    /**
     * Get all of the StoreStock for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function storeStocks()
    {
        return $this->hasMany(StoreStock::class);
    }
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->toDateString();
    }
    /**
     * Get all of the TransferProduct for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transferProducts()
    {
        return $this->hasMany(TransferProduct::class);
    }
}
