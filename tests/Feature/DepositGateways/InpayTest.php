<?php

namespace Tests\Feature\WithdrawGateways;

use Tests\TestCase;
use Mockery;
use App\Models\Setting;
use App\Constants\Payments\Status;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Merchant;
use App\Models\Gateway;


class InpayTest extends TestCase
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
        $this->withoutMiddleware();

        $gateway = Gateway::factory([
            'name' => 'InPay',
            'real_name' => 'InPay',
        ])->create();

        $setting = Setting::factory([
            'user_id' => $this->user->id,
            'gateway_id' => $gateway->id,
            'user_pk' => 123,
            'settings' =>  '{"transaction_type":"upi","id":1,"user_id":1,"gateway_id":3,"merchant_number":"hotwin","md5_key":"94573e1adef367065fef90edba65d588"}'

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
}
