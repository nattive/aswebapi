<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Transfer extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
/**
 * Get all of the transferProducts for the Transfer
 *
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
    public function transferProducts()
    {
        return $this->hasMany(TransferProduct::class);
    }

    public function getApprovedByIdAttribute($value)
    {
        return User::find($value)?->name;
    }
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->toDateString();
    }
}
