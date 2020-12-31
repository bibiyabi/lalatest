<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Vendor\Remit\Config as RemitConfig;
use App\Http\Controllers\Vendor\Remit\Order as RemitOrder;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('test', function ()
{
    return '123';
    return DB::select('select SYSDATE FROM DUAL ');
});

# JAVA出款傳遞出款參數API
Route::get('/remit/config/add', [RemitConfig::class, 'add']);
Route::get('/remit/order/add', [RemitOrder::class, 'setOrderToProcessing']);


