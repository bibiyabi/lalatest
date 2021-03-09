<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Setting;
use App\Constants\Payments\Status;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Merchant;
use Database\Factories\WithdrawOrderFactory;
use App\Models\WithdrawOrder;
use App\Services\Payments\WithdrawGateways\Binance;
use Illuminate\Container\container;
use App\Jobs\Payment\Withdraw\CryptoCurrencySearch;
use App\Constants\Payments\CryptoCurrencyStatus;
use App\Services\Payments\Withdraw\CryptCallbackResult;

class CryptoCurrencySearchQueueTest extends TestCase
{
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

    public function test_handle_order_success_db_check()
    {

        $setting = Setting::create([
            'user_id' => 111,
            'gateway_id' => 2222,
            'user_pk' => 999988776 ,
            'settings' =>  '{"coin":"USDT","blockchain_contract":"TRC20","id":1,"user_id":1,"gateway_id":3,"api_key":"76jJmqVMkD9waAgAzgp5YDSaaRLJYSJO65Aumyv5JFemOvb9jVcYULUht767TAzW","md5_key":"7nx5jaYLAYrukclpvXzc9IF5IYasfJwY12hOJqE4ZPrUEw0CaCqsZKgI0rmMZs24"}'
        ]);


        $orderId = 'unittest'. uniqid();

        $factory = new WithdrawOrderFactory();
        $orderArray = $factory->definition();
        $orderArray['order_id'] = $orderId;
        $orderArray['key_id'] = $setting->id;
        WithdrawOrder::create($orderArray);


        $order = WithdrawOrder::where('order_id', $orderId)->first();

        $container = Container::getInstance();
        $binance = $container->make(Binance::class);

        $cryptResult = new CryptCallbackResult(CryptoCurrencyStatus::ORDER_FAIL, '');

        $mock = $this->partialMock(CryptoCurrencySearch::class, function ($mock) use ($cryptResult) {
            $mock->shouldReceive('getGatewayResult')->andReturn($cryptResult);
        });

        $mock->__construct($order, $binance);
        $mock->handle();

        $this->assertDatabaseHas('withdraw_orders', [
            'order_id'    => $orderId,
            'status'      => Status::ORDER_FAILED,
        ]);

    }

    public function test_handle_order_failed_db_check()
    {

        $setting = Setting::create([
            'user_id' => 111,
            'gateway_id' => 2222,
            'user_pk' => 999988776 ,
            'settings' =>  '{"coin":"USDT","blockchain_contract":"TRC20","id":1,"user_id":1,"gateway_id":3,"api_key":"76jJmqVMkD9waAgAzgp5YDSaaRLJYSJO65Aumyv5JFemOvb9jVcYULUht767TAzW","md5_key":"7nx5jaYLAYrukclpvXzc9IF5IYasfJwY12hOJqE4ZPrUEw0CaCqsZKgI0rmMZs24"}'
        ]);

        $orderId = 'unittest'. uniqid();
        $factory = new WithdrawOrderFactory();
        $orderArray = $factory->definition();
        $orderArray['order_id'] = $orderId;
        $orderArray['key_id'] = $setting->id;
        WithdrawOrder::create($orderArray);

        $order = WithdrawOrder::where('order_id', $orderId)->first();

        $container = Container::getInstance();
        $binance = $container->make(Binance::class);

        $cryptResult = new CryptCallbackResult(CryptoCurrencyStatus::ORDER_SUCCESS, '');

        $mock = $this->partialMock(CryptoCurrencySearch::class, function ($mock) use ($cryptResult) {
            $mock->shouldReceive('getGatewayResult')->andReturn($cryptResult);
        });

        $mock->__construct($order, $binance);
        $mock->handle();

        $this->assertDatabaseHas('withdraw_orders', [
            'order_id'    => $orderId,
            'status'      => Status::CALLBACK_SUCCESS,
        ]);

    }




}
