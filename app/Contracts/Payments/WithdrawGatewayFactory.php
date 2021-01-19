<?php


namespace App\Contracts\Payments;


use App\Exceptions\GatewayNotFountException;
use App\Services\AbstractWithdrawGateway;
use App\Services\Payments\WithdrawGateways\ApplePay;
use App\Services\Payments\WithdrawGateways\ShineUPay;
use Illuminate\Support\Facades\App;

class WithdrawGatewayFactory
{
    public static function createGateway(string $gatewayName): AbstractWithdrawGateway
    {
        if ($gatewayName == 'ShineUPay') {
            $gateway = App::make(ShineUPay::class);
        } else {
            throw new GatewayNotFountException("Gateway not found.", 1);
        }

        return $gateway;
    }
}
