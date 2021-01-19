<?php

namespace Tests\Unit;

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

class PaymentTest extends TestCase
{
   // protected $mock;


    public function setUp():void
    {
        parent::setUp();

       // $this->mock = $this->initMock(Payments::class);
    }


    private function initMock($class)
    {
       // $mock = Mockery::mock($class);
       // $this->app->instance($class, $mock);

        //return $mock;
    }



    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testCheckInputData()
    {
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
        $payment=$data['payment'];
        $payment->dispatchOrderQueue();
    }






}
