<?php

namespace Tests\Unit\Payments\Withdraw;

use App\Exceptions\InputException;
use App\Payment\Curl;
use Tests\TestCase;
use App\Services\Payments\WithdrawGateways\ShineUPay;
use App\Models\WithdrawOrder;
use Mockery;
use Illuminate\Support\Facades\Log;


class ShineUPayTest extends TestCase
{

    public function setUp():void
    {
        parent::setUp();
    }

    public function test_set_request()
    {
        $shineupay = Mockery::mock(ShineUPay::class)->makePartial();
        $shineupay->shouldReceive('getSettings')->andReturn('');
        $shineupay->shouldReceive('logRequest')->andReturn('');

        $withdrawOrder = Mockery::mock(Withdraworder::class);

        $post = [
            'order_id'         => 'dfd',
            'withdraw_address' => 'dfd',
            'first_name'       => 'fdf',
            'last_name'        => 'ddf',
            'mobile'           => 'dff',
            'bank_address'     => 'efd',
            'ifsc'             => 'fed',
            'amount'           => 'efd',
            'email'            => 'efe',
        ];

        $this->assertNull($shineupay->setRequest($post, $withdrawOrder));
    }

    public function test_set_request_input_exception()
    {
        $this->expectException(InputException::class);

        $shineupay = Mockery::mock(ShineUPay::class)->makePartial();
        $shineupay->shouldReceive('getSettings')->andReturn('');
        $shineupay->shouldReceive('logRequest')->andReturn('');

        $withdrawOrder = Mockery::mock(Withdraworder::class);

        $post = [
            'order_idtest'     => 'dfd',
            'withdraw_address' => 'dfd',
            'first_name'       => 'fdf',
            'last_name'        => 'ddf',
            'mobile'           => 'dff',
            'bank_address'     => 'efd',
            'ifsc'             => 'fed',
            'amount'           => 'efd',
            'email'            => 'efe',
        ];

        $this->assertNull($shineupay->setRequest($post, $withdrawOrder));
    }

    public function test_getSendReturn () {
        $shineupay = new ShineUPay(new Curl());

    }
}
