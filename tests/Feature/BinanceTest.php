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
use App\Constants\Payments\CryptoCurrencyStatus;

/**
 * API Key
76jJmqVMkD9waAgAzgp5YDSaaRLJYSJO65Aumyv5JFemOvb9jVcYULUht767TAzW
Secret Key
7nx5jaYLAYrukclpvXzc9IF5IYasfJwY12hOJqE4ZPrUEw0CaCqsZKgI0rmMZs24

    */

class BinanceTest extends TestCase
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


    public function test_can_create_order()
    {
        $this->markTestSkipped('要浪費錢不要測 扣一次手續費0.2');
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
            'settings' =>  '{"coin":"USDT","blockchain_contract":"TRC20","id":1,"user_id":1,"gateway_id":3,"api_key":"76jJmqVMkD9waAgAzgp5YDSaaRLJYSJO65Aumyv5JFemOvb9jVcYULUht767TAzW","md5_key":"7nx5jaYLAYrukclpvXzc9IF5IYasfJwY12hOJqE4ZPrUEw0CaCqsZKgI0rmMZs24"}'

        ])->create();

        $orderId = 'unittest'. uniqid();

        # 這是dennis的不要亂用
        #$address = 'TBvrFcquLk5zBzinutkDvrs3vrLk4qncDj';
        $address = '';

        $res = $this->post('/api/withdraw/create', [
            'type'     => 'cryptocurrency',
            'order_id'         =>  $orderId,
            'pk'               =>  $setting->user_pk,
            'amount'           => '10',
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
            'withdraw_address' => $address,
            'gateway_code'     => '1',
            'ifsc'             => '1'
        ]);



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
       // $this->markTestSkipped('skip.');
        # test api  fail
        $this->test_search_status(false, 3, CryptoCurrencyStatus::API_FAIL);
        # test order status is fail
        $this->test_search_status(true, 3, CryptoCurrencyStatus::ORDER_FAIL);
        # test order status success
        $this->test_search_status(true, 6, CryptoCurrencyStatus::ORDER_SUCCESS);
    }

    private function  test_search_status($resSuccess, $status, $assertStatus) {
        $setting = Setting::create([
            'user_id' => rand(),
            'gateway_id' => rand(),
            'user_pk' => rand(),
            'settings' =>  '{"coin":"USDT","blockchain_contract":"TRC20","id":1,"user_id":1,"gateway_id":3,"api_key":"76jJmqVMkD9waAgAzgp5YDSaaRLJYSJO65Aumyv5JFemOvb9jVcYULUht767TAzW","private_key":"7nx5jaYLAYrukclpvXzc9IF5IYasfJwY12hOJqE4ZPrUEw0CaCqsZKgI0rmMZs24"}'
        ]);

        $orderId = 'unittest'. uniqid();
        $factory = new WithdrawOrderFactory();
        $orderArray = $factory->definition();
        $orderArray['order_id'] = $orderId;
        $orderArray['key_id'] = $setting->id;
        $order = WithdrawOrder::create($orderArray);

        $shineUpay = Mockery::mock(Binance::class)->makePartial();
        $shineUpay->setCurl();

        $shineUpay->shouldReceive('isHttps')
        ->andReturn(true);
        $shineUpay->shouldReceive('getCallBackInput')
        ->andReturn([]);

        //$binanceResSample = '{"withdrawList":[{"amount":9.8,"transactionFee":0.2,"address":"TBvrFcquLk5zBzinutkDvrs3vrLk4qncDj","withdrawOrderId":"unittest603320540cf11","txId":"bd877cd0280aa2e6a7d5d38f5a426d183147d101008b92176ece4759043af997","id":"f92c958671f6424384658a157281aa5d","asset":"USDT","applyTime":1613963381000,"status":6,"network":"TRC20"}],"success":true}';

        $result = [];
        $orderArray = [];
        $orderArray['withdrawOrderId'] = $orderId;
        $orderArray['status'] = $status;
        $orderArray['amount'] = 10;

        $result['withdrawList'][] = $orderArray;
        $result['success'] = $resSuccess;

        $shineUpay->shouldReceive('getCrypSearchResult')
        ->andReturn($result);

        $res = $shineUpay->search($order, $setting);

        $this->assertEquals($assertStatus, $res->getCode());
    }





}
