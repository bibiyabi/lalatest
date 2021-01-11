<?php

namespace Tests\Unit;

use Tests\TestCase;
use Mockery;
use Illuminate\Support\Facades\Queue;
use App\Payment\Withdraw\Payment;
use Illuminate\Support\Facades\Log;
use TiMacDonald\Log\LogFake;

class PaymentTest extends TestCase
{
   // protected $mock;

    public function setUp():void
    {
        parent::setUp();
       // $this->mock = $this->initMock(Payments::class);
    }


    private function initMock($class)
    {
       // $mock = Mockery::mock($class);
       // $this->app->instance($class, $mock);

        //return $mock;
    }



    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testCheckInputData()
    {
        $payment = new Payment;

        $data = [
            'user_pk' => 1,
        ];

        $assertObject = $payment->checkInputData($data);
        $this->assertInstanceOf(Payment::class, $assertObject);


    }
}
