<?php

namespace App\Contracts\Payments\Deposit;

use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Exceptions\GatewayNotFountException;

class DepositGatewayFactory
{
    public static function createGateway(string $gatewayName): DepositGatewayInterface
    {
        if ($gatewayName == 'Inrusdt') {
            $gateway = new \App\Services\Payments\Gateways\Inrusdt();
        } else {
            throw new GatewayNotFountException("Gateway not found.", 1);
        }

        return $gateway;
    }
}
