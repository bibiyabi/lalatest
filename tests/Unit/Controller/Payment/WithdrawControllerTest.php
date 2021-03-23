<?php

namespace Tests\Unit\Controller\Payment;

use App\Constants\Payments\Status;
use App\Exceptions\WithdrawException;
use App\Http\Controllers\Payment\WithdrawController;
use App\Services\Payments\Withdraw\PaymentService;
use App\Services\Payments\Withdraw\AbstractWithdrawGateway;
use App\Services\Payments\DepositGateways\ShineUPay;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Mockery\MockInterface;
use Ramsey\Collection\Map\AbstractMap;
use Tests\TestCase;

class WithdrawControllerTest extends TestCase
{
    use DatabaseTransactions;


    public function setUp():void
    {
        parent::setUp();
    }

    public function test_create_catch_exception_than_1024_or_less_20_code()
    {
        $request = Request::create('/');
        $mockPayment = $this->mock(PaymentService::class, function (MockInterface $mock) {
            $mock->shouldReceive('checkInputSetDbSendOrderToQueue')->andThrow(new WithdrawException('input error', Status::ORDER_FAILED));
        });

        $controller = new WithdrawController();
        $res = $controller->create($request, $mockPayment);

        $this->assertSame($res->original['code'], 1024);
        $this->assertSame($res->original['success'], false);
    }


    public function test_callback_catch_exception()
    {
        $request = Request::create('/');
        $mockPayment = $this->mock(PaymentService::class, function (MockInterface $mock) {
            $mock->shouldReceive('callback')->andThrow(new WithdrawException('input error', Status::ORDER_FAILED));
        });

        $gateway = $this->mock(AbstractWithdrawGateway::class);

        $controller = new WithdrawController();
        $res = $controller->callback($request, $mockPayment, $gateway);

        $this->assertSame($res->original['code'], 1024);
        $this->assertSame($res->original['success'], false);
    }


    public function test_reset_catch_exception()
    {
        $request = Request::create('/');
        $mockPayment = $this->mock(PaymentService::class, function (MockInterface $mock) {
            $mock->shouldReceive('resetOrderStatus')->andThrow(new WithdrawException('input error', Status::ORDER_FAILED));
        });

        $controller = new WithdrawController();
        $res = $controller->reset($request, $mockPayment);

        $this->assertSame($res->original['code'], 1024);
        $this->assertSame($res->original['success'], false);
    }


    public function test_response_other_exception()
    {
        $request = Request::create('/');
        $mockPayment = $this->mock(PaymentService::class, function (MockInterface $mock) {
            $mock->shouldReceive('resetOrderStatus')->andThrow(new Exception('input error', Status::ORDER_FAILED));
        });

        $controller = new WithdrawController();
        $res = $controller->reset($request, $mockPayment);

        $this->assertSame($res->original['code'], 1024);
        $this->assertSame($res->original['success'], false);
    }
}
