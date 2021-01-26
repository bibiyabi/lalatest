<?php

namespace Tests\Unit\Payments\Withdraw;

use Tests\TestCase;
use Mockery;
use Illuminate\Container\container;
use Illuminate\Support\Facades\Queue;
use App\Payment\Withdraw\Payment;
use App\Models\Setting;
use App\Models\WithdrawOrder;
use Illuminate\Support\Facades\Log;
use TiMacDonald\Log\LogFake;
use App\Repositories\SettingRepository;
use App\Repositories\Orders\WithdrawRepository;
use App\Services\Payments\WithdrawGateways\ShineUPay;
use App\Payment\Curl;
use App\Constants\Payments\Status;
use App\Contracts\Payments\PaymentInterface;
use App\Jobs\Payment\Deposit\Notify;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Merchant;
use App\Models\Gateway;
use Illuminate\Support\Facades\Bus;
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
     * A basic unit test example.
     *
     * @return void
     */
    public function testCheckInputData()
    {
        return;
        $orderId ='aaaaa' . uniqid();

        $settingMock = Mockery::mock(SettingRepository::class);

        $payment = new Payment(new WithdrawRepository, $settingMock);

        $request = Mockery::mock('Illuminate\Http\Request');


        $request->shouldReceive('post')
        ->andReturn([
            'payment_type' => 1,
            'order_id'     => $orderId,
            'pk'      => 1,
            'amount' => 10
        ]);

        $request->shouldReceive('all')
        ->andReturn([
            'payment_type' => 1,
            'order_id'     => $orderId,
            'pk'      => 1,
            'amount' => 10
        ]);

        $o= new \stdClass();
        $o->id = 1;
        $o->user_id = 1;

        $request->shouldReceive('user')
        ->andReturn($o);


        $assertObject = $payment->checkInputData($request);
        $this->assertInstanceOf(Payment::class, $assertObject);

        return ['payment' => $payment, 'orderId' => $orderId, 'settingMock' => $settingMock];
    }

     /**
     * @depends testCheckInputData
     */
    public function test_set_order_to_db($data) {
        return;
        $orderId = $data['orderId'];

        $payment = $data['payment'];

        $setting = Setting::factory()->create([
            'user_id' => 1,
            'gateway_id' => 3,
            'user_pk' => rand(10000,20000),
            'settings' => '{}',
        ]);

        $settingMock = $data['settingMock'];
        $settingMock->shouldReceive('filterCombinePk')
        ->once()
        ->andReturn($settingMock);

        $settingMock->shouldReceive('first')
        ->once()
        ->andReturn($setting);

        $payment->setOrderToDb();

        $setting->delete();

        $this->assertDatabaseHas('WITHDRAW_ORDERS', [
            'order_id' => $orderId
        ]);

        return ['payment' => $payment];

    }

    public function test_callback() {
        return;
        $request = Mockery::mock('Illuminate\Http\Request');
        $request->shouldReceive('post')
        ->andReturn('{"body":{"platformOrderId":"20210115A989GVUBYXA84485","orderId":"123456600131627297f","status":1,"amount":10.0000},"status":0,"merchantId":"A5LB093F045C2322","timestamp":"1610691875552"}');

        $request->shouldReceive('header')->with('HTTP_API_SIGN')
        ->andReturn('5142aade809d9a4038392426c74f859a');

        $key = new \stdClass();
        $key->md5_key = 'fed8b982f9044290af5aba64d156e0d9';
        $o= new \stdClass();
        $o->key = $key;

        $withdrawMock = Mockery::mock(WithdrawRepository::class);

        $withdrawMock->shouldReceive('filterOrderId')
        ->andReturn($withdrawMock);

        $withdrawMock->shouldReceive('first')
        ->andReturn($o);

        $shineUPay = new ShineUPay(new Curl, $withdrawMock);
        $res = $shineUPay->callback($request);
        $this->assertEquals('success', $res->get('msg'));

    }

     /**
     * @depends test_set_order_to_db
     */
    public function test_dispatch_order_queue($data) {

        return;
        $payment=$data['payment'];
        $payment->dispatchOrderQueue();
    }






}
