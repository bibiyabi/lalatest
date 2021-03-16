<?php

namespace Tests\Unit\Jobs\Payment\Withdraw;

use App\Constants\Payments\Status;
use App\Jobs\Payment\Withdraw\Notify;
use App\Models\WithdrawOrder;
use App\Services\Payments\Withdraw\WithdrawNotify;
use Database\Factories\WithdrawOrderFactory;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use Mockery\MockInterface;
use Tests\TestCase;

class NotifyTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp():void
    {
        parent::setUp();
    }

    public function test_handle_if_order_failed() {


        $withdrawNotify = $this->mock(WithdrawNotify::class, function (MockInterface $mock) {
            $mock->shouldReceive('setOrder')->once()->andReturnSelf();
            $mock->shouldReceive('setMessage')->once()->andReturnSelf();
            $mock->shouldReceive('notifyWithdrawFailed')->once()->andReturn('');

        });

        $notify = $this->partialMock(Notify::class, function (MockInterface $mock) {

        });

        $factory = new WithdrawOrderFactory();
        $orderArray = $factory->definition();
        $orderArray['order_id'] = '123456600131627297f';
        $orderArray['key_id'] = 777;
        $orderArray['status'] = Status::ORDER_FAILED;
        $orderArray['no_notify'] = false;
        $order = WithdrawOrder::create($orderArray);

        $notify->__construct($order, 'msgtest');

        $notify->handle($withdrawNotify);
    }


    public function test_handle_if_order_success() {


        $withdrawNotify = $this->mock(WithdrawNotify::class, function (MockInterface $mock) {
            $mock->shouldReceive('setOrder')->once()->andReturnSelf();
            $mock->shouldReceive('setMessage')->once()->andReturnSelf();
            $mock->shouldReceive('notifyWithdrawSuccess')->once()->andReturn('');

        });

        $notify = $this->partialMock(Notify::class, function (MockInterface $mock) {

        });

        $factory = new WithdrawOrderFactory();
        $orderArray = $factory->definition();
        $orderArray['order_id'] = '123456600131627297f';
        $orderArray['key_id'] = 777;
        $orderArray['status'] = Status::CALLBACK_SUCCESS;
        $orderArray['no_notify'] = false;
        $order = WithdrawOrder::create($orderArray);

        $notify->__construct($order, 'msgtest');

        $notify->handle($withdrawNotify);
    }


    public function test_no_notify_when_order_no_notify_is_true() {

        $withdrawNotify = $this->mock(WithdrawNotify::class, function (MockInterface $mock) {
            $mock->shouldNotHaveReceived('setOrder');
        });

        $notify = $this->partialMock(Notify::class, function (MockInterface $mock) {

        });

        $factory = new WithdrawOrderFactory();
        $orderArray = $factory->definition();
        $orderArray['order_id'] = '123456600131627297f';
        $orderArray['key_id'] = 777;
        $orderArray['no_notify'] = true;
        $orderArray['status'] = Status::CALLBACK_SUCCESS;
        $order = WithdrawOrder::create($orderArray);

        $notify->__construct($order, 'msgtest');

        $notify->handle($withdrawNotify);
    }

    public function test_log_exception(){

        $notify = $this->partialMock(Notify::class);
        Log::shouldReceive('channel')->andReturnSelf();
        Log::shouldReceive('info')->andReturn('');
        $notify->failed(new Exception('aaa'));
    }

}
