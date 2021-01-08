<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KeysController;
use App\Http\Controllers\Payment\DepositController;
use App\Http\Controllers\Payment\WithdrawOrderController;
use App\Http\Controllers\Payment\WithdrawConfigController;
use App\Http\Controllers\Payment\WithdrawPaymentController;
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
        return DB::select('select * FROM key');
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
Route::post('key',[KeysController::class, 'store']);
Route::patch('key',[KeysController::class, 'update']);
Route::delete('key',[KeysController::class, 'destroy']);


# JAVA出款傳遞出款參數API

Route::group(['middleware' => 'java.api.key'], function()
{
    //All the routes that belongs to the group goes here

});

# 代付下單
Route::post('/withdraw/order/create', [WithdrawOrderController::class, 'create']);

Route::prefix('deposit')->group(function ()
{
    Route::post('order', [DepositController::class, 'order']);
});





