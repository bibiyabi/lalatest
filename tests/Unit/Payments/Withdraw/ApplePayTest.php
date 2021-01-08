<?php

namespace Tests\Unit\Payments\Withdraw;

use Tests\TestCase;
use App\Payment\Withdraw\ApplePay;
use App\Collections\ApplePayPostCollection;
use App\Collections\ApplePayBanksCollection;
use App\Collections\BanksCollection;
use Illuminate\Support\Facades\Config;


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
    public function testRequestValidate()
    {

        $payment = new ApplePay(['aa'=>1]);

        $data = [
            'user_pk' => 1,
            'sign' => 1,
        ];




        $assertObject = $payment->setRequest($data);

        $this->assertInstanceOf(ApplePay::class, $assertObject);
    }



}
