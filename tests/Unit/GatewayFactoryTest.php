<?php

namespace Tests\Unit;

use App\Lib\Payments\Deposit\DepositGatewayFactory;
use App\Contracts\Payments\Deposit\DepositGatewayInterface;
use App\Exceptions\GatewayNotFountException;
use Tests\TestCase;

class GatewayFactoryTest extends TestCase
{
    /**
     * @dataProvider get_correct_gateway_name
     *
     * @return void
     */
    public function test_exists_gateway($gatewayName)
    {
        $gateway = DepositGatewayFactory::createGateway($gatewayName);

        $this->assertTrue($gateway instanceof DepositGatewayInterface);
    }

    /**
     * @dataProvider get_error_gateway_name
     *
     * @return void
     */
    public function test_nonexistent_gateway($gatewayName)
    {
        $this->expectException(GatewayNotFountException::class);

        DepositGatewayFactory::createGateway($gatewayName);
    }

    public function get_correct_gateway_name()
    {
        return [
            ['Inrusdt'],
            ['ShineUPay'],
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
