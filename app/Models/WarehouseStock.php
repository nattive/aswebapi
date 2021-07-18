<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseStock extends Model
{
    use HasFactory;
     protected $guarded = ['id'];

     /**
      * Get all of the propducts for the WarehouseStock
      *
      * @return \Illuminate\Database\Eloquent\Relations\HasMany
      */
     public function product()
     {
         return $this->belongsTo(Product::class, 'product_id');
     }
     /**
      * Get the warehouse that owns the WarehouseStock
      *
      * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
      */
     public function warehouse()
     {
         return $this->belongsTo(Warehouse::class);
     }
}
