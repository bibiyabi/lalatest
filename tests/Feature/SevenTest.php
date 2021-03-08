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
use App\Services\Payments\WithdrawGateways\Seven;
use Database\Factories\WithdrawOrderFactory;
use Illuminate\Http\Request;

class SevenTest extends TestCase
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
            'name' => 'Seven',
            'real_name' => 'Seven',
        ])->create();

        $setting = Setting::factory([
            'user_id' => $this->user->id,
            'gateway_id' => $gateway->id,
            'user_pk' => 123,
            'settings' =>  '{"id":1,"user_id":1,"gateway_id":3,"account":"","merchant_number":"fz2146","md5_key":"715f34ed-fe1b-4d17-bef7-c58a64076a9a"}'
        ])->create();

        $orderId = 'unittest'. uniqid();

        $res = $this->post('/api/withdraw/create', [
            'type'     => 'bank_card',
            'order_id'         =>  $orderId,
            'pk'               =>  $setting->user_pk,
            'amount'           => 100,
            'fund_passwd'      => '1',
            'withdraw_address' => '1',
            'first_name'       => 'efefe',
            'last_name'       => 'efefe',
            'ifsc'           => '1232312',
            'bank_name' => 'aaa'
        ]);

        $res->assertStatus(200);
        $res->assertJsonFragment(['success'=>true]);
        $this->assertDatabaseHas('withdraw_orders', [
            'order_id' => $orderId,
            'status' => Status::PENDING
        ]);

    }


    public function test_callback() {
        #$this->markTestSkipped('skip');
        $key = Setting::create([
            'user_id' => 1,
            'gateway_id' => 1,
            'user_pk' => 777,
            'settings' => '{"id":1,"user_id":1,"gateway_id":3,"account":"","merchant_number":"fz2146","md5_key":"715f34ed-fe1b-4d17-bef7-c58a64076a9a"}'
        ]);

        $factory = new WithdrawOrderFactory();
        $orderArray = $factory->definition();
        $orderArray['order_id'] = 'unittest6041d7a23c1c8';
        $orderArray['key_id'] = $key->id;
        WithdrawOrder::create($orderArray);

        $payload = '{"payamount":0.0,"mark":"IFSC代码错误","ordertype":2,"iscancel":1,"ticket":"a48f0aeb-6546-4a06-9d2a-b6c54e03e913","userid":"fz2146","orderid":"unittest6041d7a23c1c8","type":"bank","sign":"c5e47cbf9d73d399320c8620d633d685","pageurl":"https://form.zf77777.org/api/paypage?ticket=a48f0aeb-6546-4a06-9d2a-b6c54e03e913","amount":100,"bmount":"100.00","serialno":null,"upi":null,"qrcode":null,"note":null,"success":1,"message":null}';

        $request = Request::create('/callback/withdraw/Seven', 'POST', json_decode($payload, true), [], [],  [
            'HTTP_CONTENT_LENGTH' => strlen($payload),
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], $payload);

        $pay = Mockery::mock(Seven::class)->makePartial();
        $pay->shouldReceive('getCallBackInput')
        ->andReturn($payload);

        $res = $pay->callback($request);
        $this->assertEquals('success', $res->getMsg());
    }

}
