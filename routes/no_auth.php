<?php

use App\Http\Controllers\FakeDepositController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payment\DepositController;
use App\Http\Controllers\Payment\WithdrawController;

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

Route::match(['get', 'post'], 'withdraw/{gatewayName}', [WithdrawController::class, 'callback']);
Route::match(['get', 'post'], 'deposit/{gatewayName}', [DepositController::class, 'callback']);

Route::prefix('fake_deposit')->group(function () {
    Route::post('orders/{order}', [FakeDepositController::class, 'sendNotify'])->middleware('web');
});
