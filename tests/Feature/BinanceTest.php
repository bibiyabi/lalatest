<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use App\Models\Setting;
use App\Constants\Payments\Status;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Merchant;
use App\Models\Gateway;
use Database\Factories\WithdrawOrderFactory;
use App\Models\WithdrawOrder;
use App\Services\Payments\WithdrawGateways\Binance;
use Illuminate\Container\container;
use App\Payment\Curl;
use App\Jobs\Payment\Withdraw\Notify;

/**
 * API Key
76jJmqVMkD9waAgAzgp5YDSaaRLJYSJO65Aumyv5JFemOvb9jVcYULUht767TAzW
Secret Key
7nx5jaYLAYrukclpvXzc9IF5IYasfJwY12hOJqE4ZPrUEw0CaCqsZKgI0rmMZs24

    */

class BinanceTest extends TestCase
{
    //use DatabaseTransactions;

    public function setUp():void
    {
        parent::setUp();

       $user = Merchant::factory([
           'name' => 'java',
       ])->create();

       $this->user = $user;
       $this->actingAs($user);
    }


    public function test_can_create_order()
    {

        $this->withoutMiddleware();
        //Queue::fake();

        $gateway = Gateway::factory([
            'name' => 'Binance',
            'real_name' => 'Binance',
        ])->create();

        $setting = Setting::factory([
            'user_id' => $this->user->id,
            'gateway_id' => $gateway->id,
            'user_pk' => 123,
            'settings' =>  '{"coin":"USDT","blockchain_contract":"TRX","id":1,"user_id":1,"gateway_id":3,"private_key":"7nx5jaYLAYrukclpvXzc9IF5IYasfJwY12hOJqE4ZPrUEw0CaCqsZKgI0rmMZs24","md5_key":"76jJmqVMkD9waAgAzgp5YDSaaRLJYSJO65Aumyv5JFemOvb9jVcYULUht767TAzW"}'

        ])->create();

        $orderId = 'unittest'. uniqid();

        $res = $this->post('/api/withdraw/create', [
            'type'     => 'bank_card',
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
        //Queue::assertNotPushed(Order::class);
       // Queue::assertNotPushed(Notify::class);
    }


    public function test_search()
    {
        $this->markTestSkipped('skip.');

        $setting = Setting::create([
            'user_id' => 1,
            'gateway_id' => 1,
            'user_pk' => 777,
            'settings' =>  '{"coin":"USDT","blockchain_contract":"TRX","id":1,"user_id":1,"gateway_id":3,"private_key":"7nx5jaYLAYrukclpvXzc9IF5IYasfJwY12hOJqE4ZPrUEw0CaCqsZKgI0rmMZs24","md5_key":"76jJmqVMkD9waAgAzgp5YDSaaRLJYSJO65Aumyv5JFemOvb9jVcYULUht767TAzW"}'
        ]);

        $factory = new WithdrawOrderFactory();
        $orderArray = $factory->definition();
        $orderArray['order_id'] = 'unittest'. uniqid();
        $orderArray['key_id'] = $setting->id;
        $order = WithdrawOrder::create($orderArray);

        $shineUpay = Mockery::mock(Binance::class)->makePartial();
        $shineUpay->setCurl();

        $shineUpay->shouldReceive('isHttps')
        ->andReturn(true);
        $shineUpay->shouldReceive('getCallBackInput')
        ->andReturn([]);

        $res = $shineUpay->search($order, $setting);

        var_dump($res);
    }





}
