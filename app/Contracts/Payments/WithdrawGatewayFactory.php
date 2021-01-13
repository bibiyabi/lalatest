<?php


namespace App\Contracts\Payments;


use App\Exceptions\GatewayNotFountException;
use App\Services\AbstractWithdrawGateway;

class WithdrawGatewayFactory
{
    public static function createGateway(string $gatewayName):AbstractWithdrawGateway
    {
        if ($gatewayName == 'ApplePay') {
            $gateway = new \App\Services\Payments\WithdrawGatewyas\ApplePay();
        } else {
            throw new GatewayNotFountException("Gateway not found.", 1);
        }

        return $gateway;
    }
}
