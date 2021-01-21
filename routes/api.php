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

    Route::post('shineUpay', function (Request $request)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
        // CURLOPT_URL => 'https://testgateway.shineupay.com/withdraw/create',
        CURLOPT_URL => 'http://47.52.40.40:8443/withdraw/create',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POSTFIELDS =>'{
            "body": {
                "AdvPasswordMd5": "PayoutSecretKey",
                "OrderId": "colin3344556677",
                "Flag": 0,
                "BankCode": "IFSC",
                "BankUser": "BankUser",
                "BankUserPhone": "1234567890",
                "BankAddress": "AAA Stree",
                "BankCityName": "City",
                "BankProvinceName": "Province",
                "BankUserEmail": "1234567890@gmail.com",
                "BankUserIFSC": "1234567890",
                "Amount": 20.00,
                "RealAmount": 17.00,
                "Details": "withdraw",
                "NotifyUrl": null
            },
            "merchantId": "9WJW35ERU3GG6371",
            "timestamp": "1605534345977"
        }',
        CURLOPT_HTTPHEADER => array(
            'Api-Sign: 0df488f556acb8707ff3a03a93b24950',
            'Content-Type: application/json',
            'Cookie: __cfduid=d1893bb751bf8bc950de848102fadbdff1610530606',
            "HOST: testgateway.shineupay.com",
        ),
        ));

        $response = curl_exec($curl);


        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            echo  $error_msg;
        }

        curl_close($curl);
        echo $response;

exit;

        $url = "http://47.52.40.40:8443/withdraw/create";
        //$url = "https://testgateway.shineupay.com/withdraw/create";
        //$url = "https://api.shunwpay.com/gateway/query/remit/realtime-remittance";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        //curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        $headers = array(
           "Content-Type: application/json",

           "Accept: application/json",
           "HOST: testgateway.shineupay.com",
           "Cookie: __cfduid=d1893bb751bf8bc950de848102fadbdff1610530606",
          // "User-Agent: PostmanRuntime/7.26.8",
          // "Accept: */*",
          // "Accept-Encoding: gzip, deflate, br",
         //  "Connection: keep-alive",
           //"Api-Sign: 0df488f556acb8707ff3a03a93b24950",
        );

        curl_setopt($curl, CURLOPT_POSTFIELDS, '{\"Id\": 78912,\"Customer\": \"Eric Sweet\",\"Quantity\": 1,\"Price\": 19.00}');
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_ENCODING , "");
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            echo  $error_msg;
        }

        curl_close($curl);
        var_dump($resp);

        exit;

        $url = "http://testgateway.shineupay.com/withdraw/create";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL ,$url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
           "Api-Sign: 5142aade809d9a4038392426c74f859a",
           "Content-Type: application/json",
           "Accept: */*",
           "Accept-Encoding: deflate, gzip",
           "User-Agent: Mozilla/5.0",
           "HOST: ",
           "Content-Length: 0"
        );

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_POSTFIELDS, '{}');
        //for debug only!

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $resp = curl_exec($curl);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            echo  $error_msg;
        }

        curl_close($curl);
        var_dump($resp);


        exit;

    });
});

# Java設置資料API
Route::post('key',[SettingController::class, 'store']);
Route::delete('key',[SettingController::class, 'destroy']);

# 金流商/交易所下拉選單
Route::get('vendor/list',[GatewayController::class,'index']);

# 提示字
Route::get('placeholder',[GatewayController::class, 'getPlaceholder']);

# 前台提示字
Route::get('requirement',[GatewayController::class, 'getRequireInfo']);

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
    Route::post('reset', [WithdrawController::class, 'reset']);
});


Route::prefix('deposit')->group(function ()
{
    Route::post('create', [DepositController::class, 'create']);
    Route::match(['get', 'post'], 'callback/{gatewayName}', [DepositController::class, 'callback']);
    Route::post('reset', [DepositController::class, 'reset']);
});





