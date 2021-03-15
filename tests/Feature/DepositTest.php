<?php

namespace Tests\Feature;

use App\Constants\Payments\ResponseCode;
use App\Models\Gateway;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\Setting;
use Tests\TestCase;
use App\Constants\Payments\Status;
use App\Events\DepositCallback;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;

class DepositTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    private $user;

    public function setUp(): void
    {
        parent::setUp();

        $user = Merchant::where(['name' => 'java'])->firstOr(function () {
            return Merchant::factory(['name'=>'java'])->create();
        });

        $this->user = $user;

        $this->actingAs($user);
    }

    public function test_can_create_form_order()
    {
        $this->withoutMiddleware();

        $gateway = Gateway::factory([
            'name' => 'Inrusdt',
            'real_name' => '印發',
        ])->create();

        $setting = Setting::factory([
            'user_id' => $this->user->id,
            'gateway_id' => $gateway->id,
            'user_pk' => 123,
            'settings' => '{"public_key":"brianHalfBank","info_title":"brianHalfBank","return_url":"http://商戶後台/recharge/notify","private_key":"brianHalfBank","notify_url":"brianHalfBank","merchant_number":"brianHalfBank","md5_key":"請填上md5密鑰","account":"brianHalfBank"}',

        ])->create();

        $orderId = 'D210121020135606534342';
        $response = $this->post('api/deposit/create', [
            'order_id' => $orderId,
            'pk' => $setting->user_pk,
            'type' => 'e_wallet',
            'amount' => 123,
        ]);

        $response->assertJsonFragment(['success'=>true]);
        $this->assertDatabaseHas('orders', ['order_id'=>$orderId]);
    }

    public function test_can_create_url_order()
    {
        $this->withoutMiddleware();

        Http::fake([
            '*' => Http::response([
                'data' => [
                    'qrcode_url' => 'google.com'
                ],
            ])
        ]);

        $gateway = Gateway::factory([
            'name' => 'Jinfaguoji',
            'real_name' => '金發',
        ])->create();

        $setting = Setting::factory([
            'user_id' => $this->user->id,
            'gateway_id' => $gateway->id,
            'user_pk' => 123,
            'settings' => '{"public_key":"brianHalfBank","info_title":"brianHalfBank","return_url":"http://商戶後台/recharge/notify","private_key":"brianHalfBank","notify_url":"brianHalfBank","merchant_number":"brianHalfBank","md5_key":"請填上md5密鑰","account":"brianHalfBank"}',

        ])->create();

        $orderId = 'D210121020135606534342';
        $response = $this->post('api/deposit/create', [
            'order_id' => $orderId,
            'pk' => $setting->user_pk,
            'type' => 'e_wallet',
            'amount' => 123,
        ]);

        $response->assertJsonFragment(['success'=>true, 'content'=>'google.com']);
        $this->assertDatabaseHas('orders', ['order_id'=>$orderId]);
    }

    public function test_create_order_when_have_no_setting()
    {
        $this->withoutMiddleware();

        $orderId = 'D210121020135606534342';
        $response = $this->post('api/deposit/create', [
            'order_id' => $orderId,
            'pk' => $this->faker()->numberBetween(999999, 999999999),
            'type' => 'e_wallet',
            'amount' => 123,
        ]);

        $response->assertJsonFragment(['code'=>ResponseCode::RESOURCE_NOT_FOUND]);
        $this->assertDatabaseMissing('orders', ['order_id'=>$orderId]);
    }

    public function test_create_order_when_have_no_gateway()
    {
        $this->withoutMiddleware();

        $setting = Setting::factory([
            'user_id' => $this->user->id,
            'gateway_id' => $this->faker()->numberBetween(999999, 999999999),
            'user_pk' => 123,
            'settings' => '{"public_key":"brianHalfBank","info_title":"brianHalfBank","return_url":"http://商戶後台/recharge/notify","private_key":"brianHalfBank","notify_url":"brianHalfBank","merchant_number":"brianHalfBank","md5_key":"請填上md5密鑰","account":"brianHalfBank"}',
        ])->create();

        $orderId = 'D210121020135606534342';
        $response = $this->post('api/deposit/create', [
            'order_id' => $orderId,
            'pk' => $setting->user_pk,
            'type' => 'e_wallet',
            'amount' => 123,
        ]);

        $response->assertJsonFragment(['code'=>ResponseCode::RESOURCE_NOT_FOUND]);
        $this->assertDatabaseMissing('orders', ['order_id'=>$orderId]);
    }

    public function test_can_reset_order()
    {
        $this->withoutMiddleware();

        $order = Order::factory([
            'user_id'=>$this->user->id,
            'key_id'=>1,
            'gateway_id'=>1,
            'no_notify'=>0,
        ])->create();

        $response = $this->post('api/deposit/reset', [
            'order_id' => $order->order_id,
        ]);

        $response->assertJsonFragment(['success'=>true]);
        $this->assertDatabaseHas('orders', [
            'order_id' => $order->order_id,
            'no_notify' => true,
        ]);
    }

    public function test_can_callback()
    {
        $this->withoutMiddleware();

        Event::fake();

        $gateway = Gateway::factory([
            'name' => 'Inrusdt',
            'real_name' => '印發',
        ])->create();

        $setting = Setting::factory([
            'user_id' => $this->user->id,
            'gateway_id' => $gateway->id,
            'user_pk' => 123,
        ])->create();

        $order = Order::factory([
            'user_id'=>$this->user->id,
            'key_id'=>$setting->id,
            'status'=>Status::ORDER_SUCCESS,
            'gateway_id'=>$gateway->id,
        ])->create();

        $response = $this->post('callback/deposit/Inrusdt', [
            'orderId' => $order->order_id,
            'status' => 1,
            'amount' => 200,
        ]);

        $response->assertSeeText('success');
        $this->assertDatabaseHas('orders', ['order_id'=>$order->order_id, 'status'=>Status::CALLBACK_SUCCESS]);
        Event::assertDispatched(DepositCallback::class);
    }
}
