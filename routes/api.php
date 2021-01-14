<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Payment\DepositController;
use App\Http\Controllers\Payment\WithdrawOrderController;
use App\Http\Controllers\GatewayController;
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
    Route::get('aaa', function () {
        echo '@@@';
        return DB::select('select * FROM keys');
    });

    Route::get('bbb', function (){
        return 123;
    });

    Route::get('user', function (Request $request)
    {
        return $request->user();
    });
});

# Java設置資料API
Route::post('key',[SettingController::class, 'store']);
Route::patch('key',[SettingController::class, 'update']);
Route::delete('key',[SettingController::class, 'destroy']);

# 金流商/交易所下拉選單
Route::get('vendor/list',[GatewayController::class,'index']);

# 提示字
Route::get('placeholder',[GatewayController::class, 'getPlaceholder']);

# JAVA出款傳遞出款參數API

Route::group(['middleware' => 'java.api.key'], function()
{
    //All the routes that belongs to the group goes here

});

# 代付下單
Route::prefix('withdraw')->group(function ()
{
    Route::post('create', [WithdrawOrderController::class, 'create']);
    Route::post('callback/{gatewayName}', [WithdrawOrderController::class, 'callback']);
});


Route::prefix('deposit')->group(function ()
{
    Route::post('order', [DepositController::class, 'order']);
    Route::post('callback/{gatewayName}', [DepositController::class, 'callback']);
});





