<?php

namespace Tests\Feature;

use App\Models\Gateway;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Constants\Payments\Status;
use App\Jobs\Payment\Deposit\Notify;
use Bus;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DepositTest extends TestCase
{
    use DatabaseTransactions;

    private $user;

    public function setUp(): void
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
    }

    public function test_can_callback()
    {
        $this->withoutMiddleware();

        Bus::fake();

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
            'no_notify'=>0,
        ])->create();

        $response = $this->post('callback/deposit/Inrusdt', [
            'orderId' => $order->order_id,
            'status' => 1,
            'amount' => 200,
        ]);

        $response->assertSeeText('success');
        Bus::assertDispatched(Notify::class);
        $this->assertDatabaseHas('orders', ['order_id'=>$order->order_id, 'status'=>Status::CALLBACK_SUCCESS]);
    }
}
