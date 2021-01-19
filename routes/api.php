<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Payment\DepositController;
use App\Http\Controllers\Payment\WithdrawController;
use App\Http\Controllers\GatewayController;
use App\Models\Order;
use App\Models\WithdrawOrder;

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

Route::prefix('test')->group(function() {
    Route::post('bbb', function (){

        DB::enableQueryLog();
        $order = WithdrawOrder::first();
        dd(DB::getQueryLog());

        dd($order);

    });

    Route::post('user', function (Request $request)
    {
       print_r($request->json()->all());

    });
});

# Java設置資料API
Route::post('key',[SettingController::class, 'store']);
Route::delete('key',[SettingController::class, 'destroy']);

# 金流商/交易所下拉選單
Route::get('vendor/list',[GatewayController::class,'index']);

# 提示字
Route::get('placeholder',[GatewayController::class, 'getPlaceholder']);

# JAVA出款傳遞出款參數API
Route::post('aaa',function (Request $request)
{
    echo '@@@';
});


# 代付下單
Route::prefix('withdraw')->group(function ()
{
    Route::post('create', [WithdrawController::class, 'create']);
    Route::post('callback/{gatewayName}', [WithdrawController::class, 'callback']);
});


Route::prefix('deposit')->group(function ()
{
    Route::post('create', [DepositController::class, 'create']);
    Route::match(['get', 'post'], 'callback/{gatewayName}', [DepositController::class, 'callback']);
});





