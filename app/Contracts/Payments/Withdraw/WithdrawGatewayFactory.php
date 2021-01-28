<?php


namespace App\Contracts\Payments\Withdraw;

use App\Exceptions\GatewayNotFountException;
use App\Payment\Curl;
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
        }catch(\Exception $e){
            Log::info($e->getMessage());
            throw new GatewayNotFountException();
        }

        return $gateway;
    }
}
