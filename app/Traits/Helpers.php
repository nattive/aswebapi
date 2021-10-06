<?php

namespace App\Traits;

use App\Mail\InvoiceMail;
use App\Models\Store;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Waybill;
use App\Notifications\GeneralNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

trait Helpers
{
    /**
     * notification methods
     * @param array $notification [
     * $type = Invoice Created
     * $body = [][]
     * table_head= []
     * table_body= []
     * ]
     */

    public function updateNotification(array $notification)
    {
        $toNotify = User::where('role', 'SUPERVISOR')->where('role', 'DIRECTOR')
            ->orWhere('role', 'SUPERVISOR')
            ->get();
        switch ($notification['type']) {
            case 'Invoice Created':
                $other = [
                    'topic' => "Invoice Created",
                    'body' => json_encode([$notification['tablehead'], $notification['tablebody']]),
                ];
                $notifyArray = array_merge($other, $notification);
                foreach ($toNotify as $user) {
                    // Mail::to($user->email)
                    //     ->send(new InvoiceMail($notifyArray));
                    $user->userNotification()->create([
                        'subject' => "Invoice Created",
                        'table_head' => json_encode($notification['tablehead']),
                        'table_body' => json_encode($notification['tablebody']),
                        'body' => "An invoice, with code " . $notification["code"] . " was created",
                    ]);
                }
                break;
            default:
                foreach ($toNotify as $user) {
                    $array = array_merge(['greetings' => "Hi {$user->name}"], $notification);
                    $user->userNotification()->create([
                        'subject' => $notification['type'],
                        'table_head' => json_encode($notification['tablehead']),
                        'table_body' => json_encode($notification['tablebody']),
                        'body' => $notification['body'] . '. See more details',
                    ]);
                    // $user->notify(new GeneralNotification($array));
                }
                break;
        }
    }
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
