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
Route::post('login', 'UserAuthController@login')->name('login');
Route::post('logout', 'UserAuthController@logout')->name('logout');
Route::get('me', 'UserAuthController@me');

Route::middleware(['auth:sanctum', 'supervisor'])->group(function () {
    Route::post('register', 'UserAuthController@register');

});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('users')->group(function () {
        Route::get('/', 'RoleController@index');
        Route::post('/assign', 'RoleController@assign');

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
        Route::get('/retract/all', 'InvoiceController@retractRequest');
        Route::post('/', 'InvoiceController@store');
        Route::get('/retract/accept/{invoice}', 'InvoiceController@acceptRetract');
        Route::prefix('store')->group(function () {
            Route::get('{id}', 'InvoiceController@storeinvoice');
            Route::post('{id}/retract', 'InvoiceController@retract');
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
        Route::get('/store/{store_id}', 'ProductController@showStoreProducts');
        Route::get('/warehouse/stock', 'ProductController@warehouseStock');
        Route::post('/', 'ProductController@store');
        Route::post('/multiple', 'ProductController@uploadProducts');
        Route::prefix('request')->group(function () {
            Route::post('/', 'TransferController@store');
        });
    });
});
