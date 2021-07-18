<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    /**
     * Get all of the warehouseStock for the Warehouse
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function warehouseStocks()
    {
        return $this->hasMany(WarehouseStock::class);
    }

    /**
     * Get all of the waybills for the Warehouse
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function waybills()
    {
        return $this->hasMany(Waybill::class);
    }
}
