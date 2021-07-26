<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */
Route::post('login', 'UserAuthController@login');
Route::post('logout', 'UserAuthController@logout');
Route::get('me', 'UserAuthController@me');

Route::middleware(['auth:sanctum', 'supervisor'])->group(function () {
    Route::post('register', 'UserAuthController@register');

});

Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('user/{id}')->group(function () {
        Route::post('update', 'UserController@update');
        Route::post('deactivate', 'UserController@deactivate');
        Route::post('activate', 'UserController@activate');
        Route::post('/change-password', 'UserAuthController@changePassword');
    });
    Route::prefix('users')->group(function () {
        Route::get('/', 'UserController@index');
        Route::post('/assign', 'UserController@deactivate');
        Route::post('/assign', 'UserController@activate');
        Route::prefix('store')->group(function () {
            Route::post('/assign', 'UserController@changeStore');
        });
        Route::prefix('roles')->group(function () { Route::post('/assign', 'RoleController@assign');
        });
    });
    Route::prefix('warehouse')->group(function () {
        Route::get('/', 'WarehouseController@index');
        Route::post('/', 'WarehouseController@store');
        Route::put('{id}', 'WarehouseController@edit');
        Route::get('{id}', 'WarehouseController@show');
        Route::prefix('request')->group(function () {
            Route::get('{warehouse_id}', 'TransferController@getWarehouse');
            Route::post('/accept', 'TransferController@accept');
        });
        Route::get('{id}', 'WarehouseController@show');
    });
    Route::prefix('store')->group(function () {
        Route::get('/', 'StoreController@index');
        Route::post('/', 'StoreController@store');
        Route::put('{id}', 'StoreController@edit');
        Route::get('{id}', 'StoreController@show');
        Route::delete('{id}', 'StoreController@destroy');
    });
    Route::prefix('customer')->group(function () {
        Route::get('/', 'CustomerController@index');
        Route::post('/', 'CustomerController@store');
    });
    Route::prefix('invoice')->group(function () {
        Route::get('/', 'InvoiceController@index');
        Route::post('/draft', 'InvoiceController@toJson');
        Route::get('/retract/all', 'InvoiceController@retractRequest');
        Route::post('/', 'InvoiceController@store');
        Route::get('/retract/accept/{invoice}', 'InvoiceController@acceptRetract');
        Route::prefix('payment')->group(function () {
            Route::put('/', 'PaymentModeController@update');
        });
        Route::prefix('filter')->group(function () {
            Route::post('/dates', 'InvoiceController@filterBetweenDates');
            Route::post('/code', 'InvoiceController@filterByCode');
            Route::get('/today', 'InvoiceController@filterToday');
            Route::get('/this_week', 'InvoiceController@filterBetweenDates');
            Route::get('/this_month', 'InvoiceController@filterBetweenDates');
            Route::get('/this_year', 'InvoiceController@filterBetweenDates');
        });
        Route::prefix('store/{id}')->group(function () {
            Route::get('/', 'InvoiceController@storeinvoice');
            Route::post('/retract', 'InvoiceController@retract');
            Route::get('/chartData', 'InvoiceController@chartData');
        });
    });
    Route::prefix('waybill')->group(function () {
        Route::get('/', 'WaybillController@index');
        Route::post('/', 'WaybillController@store');
        Route::get('{id}', 'WaybillController@show');
        Route::post('/add-product/{id}', 'WaybillController@addProduct');
    });
    Route::prefix('products')->group(function () {
        Route::get('/', 'ProductController@index');
        Route::put('/', 'ProductController@edit');
        Route::get('/store/{store_id}', 'ProductController@showStoreProducts');
        Route::get('/warehouse/stock', 'ProductController@warehouseStock');
        Route::post('/', 'ProductController@store');
        Route::post('/multiple', 'ProductController@uploadProducts');
        Route::prefix('request')->group(function () {
            Route::post('/', 'TransferController@store');
        });
    });
    Route::prefix('report')->group(function () {
        Route::get('/stores', 'ReportController@storesReport');
        Route::get('/stores/chart', 'ReportController@storeChat');

    });
});
