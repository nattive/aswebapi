<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
/**
 * Get all of the storeStocks for the Store
 *
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
    public function storeStocks()
    {
        return $this->hasMany(StoreStock::class);
    }

    /**
     * Get all of the invoices for the Store
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function getSupervisorIdAttribute($value)
    {

        $user = User::where('id', $value)->first();
        if(\is_null($user)){
            return User::where('role', 'DIRECTOR')->first();
        }

        return   $user;
    }
}
