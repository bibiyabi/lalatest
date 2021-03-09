<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use Illuminate\Container\container;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Services\Payments\WithdrawGateways\ShineUPay;
use App\Payment\Curl;
use App\Constants\Payments\Status;
use App\Jobs\Payment\Deposit\Notify;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Merchant;
use App\Models\Gateway;
use Illuminate\Support\Facades\Bus;
use App\Models\WithdrawOrder;
use App\Contracts\Payments\CallbackResult;
use App\Exceptions\DecodeException;
use App\Http\Controllers\Payment\WithdrawController;
use App\Services\Payments\Withdraw\PaymentService;
use App\Providers\GatewayServiceProvider;
use App\Services\AbstractWithdrawGateway;
use Database\Factories\WithdrawOrderFactory;
use Illuminate\Support\Facades\Queue;
use Mockery\MockInterface;
use App\Exceptions\WithdrawException;
use App\Exceptions\InputException;


use App\Jobs\Payment\Withdraw\Order as OrderQueue;

class OrderQueueTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp():void
    {
        parent::setUp();
    }


    public function test_order_success_when_gateway_success() {

        $key = Setting::create([
            'user_id' => 1,
            'gateway_id' => 1,
            'user_pk' => 777,
            'settings' => '{"id":1,"user_id":1,"gateway_id":3,"merchant_number":"A5LB093F045C2322","md5_key":"fed8b982f9044290af5aba64d156e0d9", "private_key": "A948C01Y9JB47290"}'
        ]);

        $orderId = 'unittest'. uniqid();
        $factory = new WithdrawOrderFactory();
        $orderArray = $factory->definition();
        $orderArray['order_id'] = $orderId;
        $orderArray['key_id'] = $key->id;
        $order = WithdrawOrder::create($orderArray);

        $shineUpay = $this->partialMock(ShineUPay::class, function (MockInterface $mock) use($order) {
            $mock->shouldReceive('setRequest')->andReturn([]);
            $mock->shouldReceive('send')->andReturn(['code'=> Status::ORDER_SUCCESS, 'msg'=> 'msg test', 'data' => ['datatest']]);
        });

        $queue = new OrderQueue(['order_id' => $orderId,'type' => 'bank_card'], $order);

        $queue->handle($shineUpay);

        $this->assertDatabaseHas('withdraw_orders', [
            'order_id' => $orderId,
            'status' => Status::ORDER_SUCCESS
        ]);
    }


    public function test_order_fail_when_gateway_input_exception() {

        $key = Setting::create([
            'user_id' => 1,
            'gateway_id' => 1,
            'user_pk' => 777,
            'settings' => '{"id":1,"user_id":1,"gateway_id":3,"merchant_number":"A5LB093F045C2322","md5_key":"fed8b982f9044290af5aba64d156e0d9", "private_key": "A948C01Y9JB47290"}'
        ]);

        $orderId = 'unittest'. uniqid();
        $factory = new WithdrawOrderFactory();
        $orderArray = $factory->definition();
        $orderArray['order_id'] = $orderId;
        $orderArray['key_id'] = $key->id;
        $order = WithdrawOrder::create($orderArray);

        $shineUpay = $this->partialMock(ShineUPay::class, function (MockInterface $mock) {
            $e = new InputException('input error', Status::ORDER_FAILED);
            $mock->shouldReceive('setRequest')->andThrow($e);
        });

        $queue = new OrderQueue(['order_id' => $orderId, 'type'     => 'bank_card'], $order);

        $queue->handle($shineUpay);

        $this->assertDatabaseHas('withdraw_orders', [
            'order_id' => $orderId,
            'status' => Status::ORDER_FAILED
        ]);
    }


    public function test_order_fail_when_gateway_decode_exception() {

        $key = Setting::create([
            'user_id' => 1,
            'gateway_id' => 1,
            'user_pk' => 777,
            'settings' => '{"id":1,"user_id":1,"gateway_id":3,"merchant_number":"A5LB093F045C2322","md5_key":"fed8b982f9044290af5aba64d156e0d9", "private_key": "A948C01Y9JB47290"}'
        ]);

        $orderId = 'unittest'. uniqid();
        $factory = new WithdrawOrderFactory();
        $orderArray = $factory->definition();
        $orderArray['order_id'] = $orderId;
        $orderArray['key_id'] = $key->id;
        $order = WithdrawOrder::create($orderArray);

        $shineUpay = $this->partialMock(ShineUPay::class, function (MockInterface $mock) {
            $e = new DecodeException('input error', STATUS::ORDER_ERROR);
            $mock->shouldReceive('setRequest')->andThrow($e);
        });

        $queue = new OrderQueue(['order_id' => $orderId, 'type'     => 'bank_card'], $order);

        $queue->handle($shineUpay);

        $this->assertDatabaseHas('withdraw_orders', [
            'order_id' => $orderId,
            'status' => Status::ORDER_ERROR
        ]);
    }

    public function test_order_fail_when_gateway_withdraw_exception() {

        $key = Setting::create([
            'user_id' => 1,
            'gateway_id' => 1,
            'user_pk' => 777,
            'settings' => '{"id":1,"user_id":1,"gateway_id":3,"merchant_number":"A5LB093F045C2322","md5_key":"fed8b982f9044290af5aba64d156e0d9", "private_key": "A948C01Y9JB47290"}'
        ]);

        $orderId = 'unittest'. uniqid();
        $factory = new WithdrawOrderFactory();
        $orderArray = $factory->definition();
        $orderArray['order_id'] = $orderId;
        $orderArray['key_id'] = $key->id;
        $order = WithdrawOrder::create($orderArray);

        $shineUpay = $this->partialMock(ShineUPay::class, function (MockInterface $mock) {
            $e = new WithdrawException('unknown error', STATUS::ORDER_FAILED);
            $mock->shouldReceive('setRequest')->andThrow($e);
        });

        $queue = new OrderQueue(['order_id' => $orderId, 'type'     => 'bank_card'], $order);

        $queue->handle($shineUpay);

        $this->assertDatabaseHas('withdraw_orders', [
            'order_id' => $orderId,
            'status' => Status::ORDER_FAILED
        ]);
    }



    public function test_order_fail_when_gateway_unknown_exception() {

        $key = Setting::create([
            'user_id' => 1,
            'gateway_id' => 1,
            'user_pk' => 777,
            'settings' => '{"id":1,"user_id":1,"gateway_id":3,"merchant_number":"A5LB093F045C2322","md5_key":"fed8b982f9044290af5aba64d156e0d9", "private_key": "A948C01Y9JB47290"}'
        ]);

        $orderId = 'unittest'. uniqid();
        $factory = new WithdrawOrderFactory();
        $orderArray = $factory->definition();
        $orderArray['order_id'] = $orderId;
        $orderArray['key_id'] = $key->id;
        $order = WithdrawOrder::create($orderArray);

        $shineUpay = $this->partialMock(ShineUPay::class, function (MockInterface $mock) {
            $e = new \Exception('input error');
            $mock->shouldReceive('setRequest')->andThrow($e);
        });

        $queue = new OrderQueue(['order_id' => $orderId, 'type'     => 'bank_card'], $order);

        $queue->handle($shineUpay);

        $this->assertDatabaseHas('withdraw_orders', [
            'order_id' => $orderId,
            'status' => Status::ORDER_ERROR
        ]);
    }


}
