<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use App\Models\Setting;
use App\Constants\Payments\Status;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Merchant;
use App\Models\Gateway;
use App\Models\WithdrawOrder;
use App\Services\Payments\WithdrawGateways\GlobalPay;
use Database\Factories\WithdrawOrderFactory;
use Illuminate\Http\Request;

class GlobalPayTest extends TestCase
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


    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_create()
    {
        #$this->markTestSkipped('skip');

        $this->withoutMiddleware();

        $gateway = Gateway::factory([
            'name' => 'GlobalPay',
            'real_name' => 'GlobalPay',
        ])->create();

        $setting = Setting::factory([
            'user_id' => $this->user->id,
            'gateway_id' => $gateway->id,
            'user_pk' => 123,
            'settings' =>  '{"id":1,"user_id":1,"gateway_id":3,"account":"","merchant_number":"gm761100000067975","md5_key":"0936D7E86164C2D53C8FF8AD06ED6D09"}'
        ])->create();

        $orderId = 'unittest'. uniqid();

        $res = $this->post('/api/withdraw/create', [
            'type'     => 'bank_card',
            'order_id'         =>  $orderId,
            'pk'               =>  $setting->user_pk,
            'first_name'           => 'cc',
            'last_name'           => 'bb',
            'withdraw_address' => 'dfdf',
            'amount'           => 100,
            'ifsc' => '000003423423',
            'transaction_type' => 'IDPT0001'

        ]);

        $res->assertStatus(200);
        $res->assertJsonFragment(['success'=>true]);
        $this->assertDatabaseHas('withdraw_orders', [
            'order_id' => $orderId,
            'status' => Status::PENDING
        ]);
    }


    public function test_callback()
    {
        //$this->markTestSkipped('skip');
        $key = Setting::create([
            'user_id' => 1,
            'gateway_id' => 1,
            'user_pk' => 777,
            'settings' => '{"id":1,"user_id":1,"gateway_id":3,"account":"","merchant_number":"gm761100000067975","md5_key":"0936D7E86164C2D53C8FF8AD06ED6D09"}'
        ]);

        $factory = new WithdrawOrderFactory();
        $orderArray = $factory->definition();
        $orderArray['order_id'] = '202103160000000426661123130942';
        $orderArray['key_id'] = $key->id;
        WithdrawOrder::create($orderArray);

        $payload = '{"order_no":"202103160000000426661123130942","mer_no":"gm761100000067975","create_time":"2021-03-16 12:31:30","err_msg":null,"order_amount":"111.00","sign":"fb3773b6d1d8fd67f463b2444c0728c0","err_code":null,"ccy_no":"INR","mer_order_no":"202103160000000426661123130942","pay_time":null,"status":"SUCCESS"}';

        $request = Request::create('/callback/withdraw/GlobalPay', 'POST', json_decode($payload, true), [], [], [
            'CONTENT_TYPE' => 'application/x-www-form-urlencoded; charset=UTF-8'
        ]);

        $pay = Mockery::mock(GlobalPay::class)->makePartial();
        $pay->shouldReceive('getCallBackInput')
        ->andReturn($payload);

        $res = $pay->callback($request);
        $this->assertEquals('SUCCESS', $res->getMsg());
    }
}
