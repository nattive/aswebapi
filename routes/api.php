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

Route::middleware(['auth:sanctum', 'Toplevel'])->group(function () {
    Route::post('register', 'UserAuthController@register');

});

Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('user/{id}')->group(function () {
        Route::post('update', 'UserController@update');
        Route::get('deactivate', 'UserController@deactivate');
        Route::get('activate', 'UserController@activate');
        Route::post('/change-password', 'UserAuthController@changePassword');
    });
    Route::prefix('users')->group(function () {
        Route::get('/', 'UserController@index');
        Route::post('/deactivate', 'UserController@deactivate');
        Route::post('/activate', 'UserController@activate');
        Route::prefix('store')->group(function () {
            Route::post('/assign', 'UserController@changeStore');
        });
        Route::prefix('roles')->group(function () {
            Route::post('/assign', 'RoleController@assign');
        });
    });
    Route::prefix('warehouse')->group(function () {
        Route::get('/', 'WarehouseController@index');
        Route::get('/transfer-history/{warehouse_id}', 'WarehouseController@transferHistory');
        Route::get('/transfer-history/find/{transfer}', 'TransferController@show');
        Route::post('/transfer-history/filter-transfer/{warehouse_id}', 'WarehouseController@filterTransfer');
        Route::post('/', 'WarehouseController@store');
        Route::put('{id}', 'WarehouseController@edit');
        Route::get('{id}', 'WarehouseController@show');
        Route::prefix('request')->group(function () {
            Route::get('{warehouse_id}', 'TransferController@getWarehouse');
            Route::post('/accept', 'TransferController@accept');
            Route::post('/deny', 'TransferController@deny');
        });
        Route::get('{id}', 'WarehouseController@show');
    });
    Route::prefix('store')->group(function () {
        Route::get('/', 'StoreController@index');
        Route::get('deactivated', 'StoreController@deactivated');
        Route::post('/', 'StoreController@store');
        Route::put('{id}', 'StoreController@edit');
        Route::get('{id}', 'StoreController@show');
        Route::get('{id}', 'StoreController@show');
        Route::get('{id}/deactivate', 'StoreController@deactivate');
        Route::get('{id}/activate', 'StoreController@activate');
    });
    Route::prefix('customer')->group(function () {
        Route::get('/', 'CustomerController@index');
        Route::get('/invoice/{id}', 'CustomerController@customerInvoices');
        Route::post('/', 'CustomerController@store');
    });
    Route::prefix('discount')->group(function () {
        Route::get('/', 'DiscountController@index');
        Route::post('/', 'DiscountController@store');
        Route::delete('/', 'DiscountController@destroy');
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
            Route::post('/today', 'InvoiceController@filterToday');
            Route::post('/this_week', 'InvoiceController@filterThisWeek');
            Route::post('/this_month', 'InvoiceController@filterThisMonth');
            Route::post('/this_year', 'InvoiceController@filterThisYear');
            Route::post('/debt', 'InvoiceController@debt');
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
        Route::put('/edit', 'WaybillController@edit');
        Route::post('/filter', 'WaybillController@filter');
        Route::post('/chartData', 'WaybillController@filter');
        Route::get('{id}', 'WaybillController@show');
        Route::post('/add-product/{id}', 'WaybillController@addProduct');
    });
    Route::prefix('products')->group(function () {
        Route::get('/', 'ProductController@index');
        Route::get('{id}', 'ProductController@show');
        Route::delete('{id}', 'ProductController@destroy');
        Route::put('/', 'ProductController@edit');
        Route::get('/store/{store_id}', 'ProductController@showStoreProducts');
        Route::get('/warehouse/stock', 'ProductController@warehouseStock');
        Route::post('/', 'ProductController@store');
        Route::post('/multiple', 'ProductController@uploadProducts');
        Route::prefix('request')->group(function () {
            Route::post('/', 'TransferController@store');
            Route::get('store/{id}', 'TransferController@storeTransfer');
            Route::get('get/{type}/{id}', 'TransferController@getRequest');
            Route::post('/accept', 'TransferController@accept');
            Route::post('/deny', 'TransferController@deny');

        });
    });
    Route::prefix('report')->group(function () {
        Route::get('/stores', 'ReportController@storesReport');
        Route::get('/stores/chart', 'ReportController@storeChat');
    });
    Route::prefix('notification')->group(function () {
        Route::get('/', 'UserNotificationController@index');
        Route::get('/read/{id}', 'UserNotificationController@read');
        Route::get('/show/{id}', 'UserNotificationController@show');
    });
});
