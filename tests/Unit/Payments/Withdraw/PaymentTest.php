<?php

namespace Tests\Unit\Payments\Withdraw;

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
use App\Http\Controllers\Payment\WithdrawController;
use App\Payment\Withdraw\Payment;
use App\Providers\GatewayServiceProvider;
use App\Services\AbstractWithdrawGateway;
use Database\Factories\WithdrawOrderFactory;

class PaymentTest extends TestCase
{
   // protected $mock;

    use DatabaseTransactions;
    public function setUp():void
    {
        parent::setUp();



       $user = Merchant::factory([
           'name' => 'java',
       ])->create();

       $this->user = $user;

       $this->actingAs($user);


    }
    private function initMock($class)
    {
        $mock = Mockery::mock($class);
        $container = Container::getInstance();
        $container->instance($class, $mock);

        return $mock;
    }

    private function initMockPartial($class)
    {
        $mock = Mockery::mock($class)->makePartial();
        $container = Container::getInstance();
        $container->instance($class, $mock);

        return $mock;
    }


    public function test_create_order() {
        $this->withoutMiddleware();
        Bus::fake();

        $gateway = Gateway::factory([
            'name' => 'ShineUPay',
            'real_name' => 'ShineUPay',
        ])->create();

        $setting = Setting::factory([
            'user_id' => $this->user->id,
            'gateway_id' => $gateway->id,
            'user_pk' => 123,
            'settings' =>  '{"id":1,"user_id":1,"gateway_id":3,"merchantId":"A5LB093F045C2322","md5_key":"fed8b982f9044290af5aba64d156e0d9", "private_key": "673835da9a3458e88e8d483bdae9c9f1"}'
        ])->create();

        $orderId = 'unittest'. uniqid();

        $res = $this->post('/api/withdraw/create', [
            'payment_type'     => 'bank_card',
            'order_id'         =>  $orderId,
            'pk'               =>  $setting->user_pk,
            'amount'           => '1',
            'fund_passwd'      => '1',
            'email'            => '1',
            'user_country'     => '1',
            'user_state'       => '1',
            'user_city'        => '1',
            'user_address'     => '1',
            'bank_province'    => '1',
            'bank_city'        => '1',
            'bank_address'     => 'r',
            'last_name'        => '1',
            'first_name'       => '1',
            'mobile'           => '1',
            'telegram'         => '1',
            'withdraw_address' => '1',
            'gateway_code'     => '1',
            'ifsc'             => '1'
        ]);

        $res->assertStatus(200);
        $res->assertJsonFragment(['success'=>true]);
        $this->assertDatabaseHas('withdraw_orders', [
            'order_id' => $orderId,
            'status' => Status::PENDING
        ]);
        Bus::assertNotDispatched(Order::class);
        Bus::assertNotDispatched(Notify::class);
    }

    /**
     * 這隻有幾個重點 ,gateway service provider $this->app->request->segment(2) 要先有值, Request::create可以
     * php::input模擬 ,只能先把getCallBackInput 設public用makePartial複寫
     *
     * @return void
     */
    public function test_shineUpay_callback() {

        $key = Setting::create([
            'user_id' => 1,
            'gateway_id' => 1,
            'user_pk' => 777,
            'settings' => '{"id":1,"user_id":1,"gateway_id":3,"merchantId":"A5LB093F045C2322","md5_key":"fed8b982f9044290af5aba64d156e0d9", "private_key": "A948C01Y9JB47290"}'
        ]);

        $factory = new WithdrawOrderFactory();
        $orderArray = $factory->definition();
        $orderArray['order_id'] = '123456600131627297f';
        $orderArray['key_id'] = $key->id;
        WithdrawOrder::create($orderArray);

        $payload = '{"body":{"platformOrderId":"20210115A989GVUBYXA84485","orderId":"123456600131627297f","status":1,"amount":10.0000},"status":0,"merchantId":"A5LB093F045C2322","timestamp":"1610691875552"}';

        $request = Request::create('/callback/withdraw/ShineUPay', 'POST', json_decode($payload, true), [], [],  [
            'HTTP_Api-Sign' => '5142aade809d9a4038392426c74f859a',
            'HTTP_CONTENT_LENGTH' => strlen($payload),
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], $payload);

        $shineUpay = Mockery::mock(ShineUPay::class)->makePartial();
        $shineUpay->shouldReceive('getCallBackInput')
        ->andReturn($payload);

        $res = $shineUpay->callback($request);

        $this->assertEquals('success', $res->getMsg());
    }


    public function test_contoller_callback_payment_always_success() {

        $orderId = 'unittest'. uniqid();

        $order = WithdrawOrder::factory([
            'order_id'    => $orderId,
        ])->create();

        $container = Container::getInstance();
        $provider = new GatewayServiceProvider($container);
        $provider->createGateway('ShineUPay');
        $container->instance(GatewayServiceProvider::class, $provider);

        $callbackResult = Mockery::mock(CallbackResult::class);
        $callbackResult->shouldReceive('getSuccess')->andReturn(true);
        $callbackResult->shouldReceive('getOrder')->andReturn($order);
        $callbackResult->shouldReceive('getAmount')->andReturn(10);
        $callbackResult->shouldReceive('getNotifyMessage')->andReturn('unit test msg');
        $callbackResult->shouldReceive('getMsg')->andReturn('success');

        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->shouldReceive('callbackNotifyToQueue')
        ->andReturn('');
        $payment->shouldReceive('callback')
        ->andReturn($callbackResult);

        $container->instance(Payment::class, $payment);

        $res = $this->post('/callback/withdraw/ShineUPay', []);

        $res->assertStatus(200);
        $this->assertDatabaseHas('withdraw_orders', [
            'order_id'    => $orderId,
            'status'      => Status::CALLBACK_SUCCESS,
            'real_amount' => 10
        ]);

    }


    public function test_contoller_callback_payment_always_failed() {

        $orderId = 'unittest'. uniqid();

        $order = WithdrawOrder::factory([
            'order_id'    => $orderId,
        ])->create();

        $container = Container::getInstance();
        $provider = new GatewayServiceProvider($container);
        $provider->createGateway('ShineUPay');
        $container->instance(GatewayServiceProvider::class, $provider);

        $callbackResult = Mockery::mock(CallbackResult::class);
        $callbackResult->shouldReceive('getSuccess')->andReturn(false);
        $callbackResult->shouldReceive('getOrder')->andReturn($order);
        $callbackResult->shouldReceive('getAmount')->andReturn(10);
        $callbackResult->shouldReceive('getNotifyMessage')->andReturn('unit test msg');
        $callbackResult->shouldReceive('getMsg')->andReturn('success');

        $payment = Mockery::mock(Payment::class)->makePartial();
        $payment->shouldReceive('callbackNotifyToQueue')
        ->andReturn('');
        $payment->shouldReceive('callback')
        ->andReturn($callbackResult);

        $container->instance(Payment::class, $payment);

        $res = $this->post('/callback/withdraw/ShineUPay', []);

        $res->assertStatus(200);
        $this->assertDatabaseHas('withdraw_orders', [
            'order_id'    => $orderId,
            'status'      => Status::CALLBACK_FAILED,
        ]);

    }


    public function test_reset_order()
    {
        $this->withoutMiddleware();

        $order = WithdrawOrder::factory([
            'user_id'=>$this->user->id,
            'key_id'=>1,
            'gateway_id'=>1,
            'no_notify'=>0,
        ])->create();

        $response = $this->post('api/withdraw/reset', [
            'order_id' => $order->order_id,
        ]);
        $this->assertDatabaseHas('withdraw_orders', [
            'order_id'    => $order->order_id,
            'no_notify'      => 1
        ]);
        $response->assertJsonFragment(['success'=>true]);
    }








}
