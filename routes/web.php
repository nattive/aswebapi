<?php

use App\Http\Resources\InvoiceResource;
use App\Models\Store;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */
// Route::get('/', 'ProductController@index');

Route::get('/mailable', function () {
    $user = User::find(1);
    $store = Store::find(1);
   $invoiceData = [
    'greetings' => "Hi {$user->name}",
    'type' => 'store Created',
    'body' => 'A store has been created successfully',
    'line1' => "A store named  has been successfully created by  ",
    "tablehead" => ["store Name", "Supervisor", "short code"],
    "tablebody" => [$store -> name, $store ->supervisor_id, $store -> short_code]
];
return (new  GeneralNotification($invoiceData))
    ->toMail($user);
});
