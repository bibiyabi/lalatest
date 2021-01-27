<?php

namespace App\Contracts\Payments\Deposit;

use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Exceptions\GatewayNotFountException;
use Illuminate\Support\Facades\Log;

class DepositGatewayFactory
{
    private static $namespace = '\App\Services\Payments\DepositGateways\\';

    public static function createGateway(string $gatewayName): DepositGatewayInterface
    {
        $class = self::$namespace.$gatewayName;
        try {
            $gateway = new $class();
        }catch(\Exception $e){
            Log::info($e->getMessage());
            throw new GatewayNotFountException();
        }

        return $gateway;
    }


}
