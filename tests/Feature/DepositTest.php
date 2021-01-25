<?php

namespace Tests\Feature;

use App\Models\Gateway;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\Setting;
use Auth;
use Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DepositTest extends TestCase
{
    use RefreshDatabase;

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
        ])->create();

        $response = $this->post('api/deposit/create', [
            'order_id' => 'D210121020135606534342',
            'pk' => $setting->user_pk,
            'type' => 'e_wallet',
            'amount' => 123,
        ]);

        $response->assertJsonFragment(['success'=>true]);
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

    public function test_can_callback_()
    {

    }
}
