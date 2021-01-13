<?php


namespace App\Contracts\Payments;


use App\Exceptions\GatewayNotFountException;
use App\Services\AbstractWithdrawGateway;
use App\Services\Payments\WithdrawGateways\ApplePay;
use Illuminate\Support\Facades\App;

class WithdrawGatewayFactory
{
    public static function createGateway(string $gatewayName):AbstractWithdrawGateway
    {
        if ($gatewayName == 'ApplePay') {
            $gateway = App::make(ApplePay::class);
        } else {
            throw new GatewayNotFountException("Gateway not found.", 1);
        }

        return $gateway;
    }
}
