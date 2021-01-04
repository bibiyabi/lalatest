<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Payment\WithdrawController;
use App\Http\Controllers\Payment\WithdrawConfigController;

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
    Route::get('', function () {
        return '123';
        return DB::select('select SYSDATE FROM DUAL ');
    });

    Route::get('user', function (Request $request)
    {
        return $request->user();
    });
});

# JAVA出款傳遞出款參數API

Route::group(['middleware' => 'java.api.key'], function()
{
    //All the routes that belongs to the group goes here

});

Route::post('/withdraw/config/', [WithdrawConfigController::class, 'store']);
Route::post('/withdraw/order/create', [WithdrawController::class, 'create']);





