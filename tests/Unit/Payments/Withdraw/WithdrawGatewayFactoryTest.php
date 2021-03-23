<?php

namespace Tests\Unit\Payments\Withdraw;

use Tests\TestCase;
use App\Lib\Payments\Withdraw\WithdrawGatewayFactory;
use App\Services\Payments\Withdraw\AbstractWithdrawGateway;
use App\Exceptions\GatewayNotFountException;

class WithdrawGatewayFactoryTest extends TestCase
{
    /**
     * @dataProvider get_correct_gateway_name
     *
     * @return void
     */
    public function test_exists_gateway($gatewayName)
    {
        $gateway = WithdrawGatewayFactory::createGateway($gatewayName);

        $this->assertTrue($gateway instanceof AbstractWithdrawGateway);
    }

    /**
     * @dataProvider get_error_gateway_name
     *
     * @return void
     */
    public function test_nonexistent_gateway($gatewayName)
    {
        $this->expectException(GatewayNotFountException::class);

        WithdrawGatewayFactory::createGateway($gatewayName);
    }

    public function get_correct_gateway_name()
    {
        return [
            ['Binance'],
            ['Pay777'],
        ];
    }

    public function get_error_gateway_name()
    {
        return [
            ['asdfasdf'],
            ['ShinczvhjeUPay'],
        ];
    }
}
