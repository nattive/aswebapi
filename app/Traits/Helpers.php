<?php

namespace App\Traits;

use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Waybill;
use Carbon\Carbon;

/**
 * All tutor helper methods
 */
trait Helpers
{
    public function UserHasAccess($store_id = null, $warehouse_id = null)
    {
        $user = auth("sanctum")->user();
        if (!is_null($store_id)) {
            $store = Store::find($store_id);
            return $user->store_id === $store->supervisor_id;
        } else if (!is_null($warehouse_id)) {
            $Warehouse = Warehouse::find($warehouse_id);
            return $user->warehouse_id === $Warehouse->supervisor_id;
        }
        return false;
    }
    /**
     * filter waybills between dates
     * @param string  $fromDate
     * @param string $toDate
     * @return Waybill
     */
    public function filterWaybillBetweenDates($fromDate, $toDate)
    {

        $from = Carbon::parse($fromDate);
        $to = Carbon::parse($toDate);

        $invoice = Waybill::whereBetween('created_at', [$from, $to])->with(['warehouse', 'products.storeStocks.store'])->get();
        return $invoice;
    }
}
