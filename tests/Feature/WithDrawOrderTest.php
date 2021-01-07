<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Contracts\Payments\PaymentInterface;
use App\Payment\Withdraw\ApplePay;
use Mockery;

class WithDrawOrderTest extends TestCase
{
    private $payment;
    public function setUp():void
    {
        parent::setUp();

    }

    public function testApplePayReturnRedirectType() {
        $this->payment = $this->instance(ApplePay::class, Mockery::mock(ApplePay::class, function ($mock) {
            $mock->shouldReceive('getRedirectType')->once()->andReturn('curl');
        }));

        $redirectType = $this->payment->getRedirectType();
        $this->assertEquals('curl', $redirectType );

    }




}
