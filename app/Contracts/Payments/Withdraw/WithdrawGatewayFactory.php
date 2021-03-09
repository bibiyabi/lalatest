<?php


namespace App\Contracts\Payments\Withdraw;

use App\Exceptions\GatewayNotFountException;
use App\Lib\Curl\Curl;
use App\Services\AbstractWithdrawGateway;
use Illuminate\Support\Facades\Log;

class WithdrawGatewayFactory
{
    private static $namespace = '\App\Services\Payments\WithdrawGateways\\';

    public static function createGateway(string $gatewayName): AbstractWithdrawGateway
    {
        $class = self::$namespace.$gatewayName;
        try {
            $gateway = new $class(new Curl());
        }catch(\Error $e){
            Log::info(__NAMESPACE__.'     '. $e->getMessage());
            throw new GatewayNotFountException($gatewayName.' Withdraw Gateway Not Found.');
        }

        return $gateway;
    }
}
