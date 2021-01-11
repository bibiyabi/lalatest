<?php

namespace Tests\Unit\Payments\Withdraw;

use Tests\TestCase;
use App\Services\Payments\WithdrawGateways\ApplePay;
use Illuminate\Container\container;
use App\Services\Curl;

class ApplePayTest extends TestCase
{

    public function setUp():void
    {
        parent::setUp();
       // $this->mock = $this->initMock(Payments::class);
    }
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testSetRequestAndSend()
    {
        $container = Container::getInstance();

        $payment = $container->make(ApplePay::class);

        $data = [
            'user_pk' => 1,
            'sign' => 1,
        ];


        $payment->setRequest($data);
        $assertObject = $payment->send();

        $this->assertInstanceOf(Curl::class, $assertObject);
    }



}
