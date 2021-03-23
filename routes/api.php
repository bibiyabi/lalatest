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

Route::prefix('test')->group(function () {
    Route::post('bbb', function () {
        return '@@@';
    });

    Route::post('aaa', function () {
        $order = WithdrawOrder::where('order_id', 'aaaaa')->first();
        var_dump($order);
    });
});

# Java設置資料API
Route::post('key', [SettingController::class, 'store']);
Route::delete('key', [SettingController::class, 'destroy']);

# 金流商/交易所下拉選單
Route::get('vendor/list', [GatewayController::class,'index']);

# 提示字
Route::get('placeholder', [GatewayController::class, 'getPlaceholder']);

# 前台提示字
Route::get('requirement', [GatewayController::class, 'getRequireInfo']);

# 代付下單
Route::prefix('withdraw')->group(function () {
    Route::post('create', [WithdrawController::class, 'create']);
    Route::post('reset', [WithdrawController::class, 'reset']);
    #Route::post('testQueue', [WithdrawController::class, 'testQueue']);
});


Route::prefix('deposit')->group(function () {
    Route::post('create', [DepositController::class, 'create']);
    Route::post('reset', [DepositController::class, 'reset']);
});
