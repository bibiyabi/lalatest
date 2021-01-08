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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('test')->group(function() {
    Route::get('aaa', function () {
        echo '@@@';
        return DB::select('select * FROM key');
    });

    Route::get('user', function (Request $request)
    {
        return $request->user(); //{"id":1,"name":"java","created_at":null,"updated_at":null}
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
# 代付設定
#Route::post('/withdraw/config/', [WithdrawConfigController::class, 'store']);

# 代付下單
Route::post('/withdraw/order/create', [WithdrawOrderController::class, 'create']);
# 代付下拉
Route::get('/withdraw/payments/bankcards', [WithdrawPaymentController::class, 'getSupportBankCards']);
Route::get('/withdraw/payments/wallets', [WithdrawPaymentController::class, 'getSupportWallet']);
Route::get('/withdraw/payments/digital_currencys', [WithdrawPaymentController::class, 'getSupportDigitalCurrency']);

Route::prefix('deposit')->group(function ()
{
    Route::post('order', [DepositController::class, 'order']);
});





