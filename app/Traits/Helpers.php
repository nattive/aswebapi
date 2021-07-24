<?php

namespace App\Traits;

use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;

/**
 * All tutor helper methods
 */
trait Helpers
{
    public function UserHasAccess($store_id = null, $warehouse_id = null)
    {
        $user =auth("sanctum")->user();
        if (!is_null($store_id)) {
            $store = Store::find($store_id);
            return $user->store_id === $store->supervisor_id ;
        } else if (!is_null($warehouse_id)) {
            $Warehouse = Warehouse::find($warehouse_id);
            return $user->warehouse_id === $Warehouse->supervisor_id;
        }
        return false;
    }

}
