<?php

namespace Tests\Feature\WithdrawGateways;

use Tests\TestCase;
use Mockery;
use App\Models\Setting;
use App\Constants\Payments\Status;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Merchant;
use App\Models\Gateway;


class InPayTest extends TestCase
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
        $this->withoutMiddleware();

        $gateway = Gateway::factory([
            'name' => 'InPay',
            'real_name' => 'InPay',
        ])->create();

        $setting = Setting::factory([
            'user_id' => $this->user->id,
            'gateway_id' => $gateway->id,
            'user_pk' => 123,
            'settings' =>  '{"id":1,"user_id":1,"gateway_id":3,"merchant_number":"hotwin","md5_key":"94573e1adef367065fef90edba65d588"}'
        ])->create();

        $orderId = 'unittest'. uniqid();

        $res = $this->post('/api/withdraw/create', [
            'payment_type'     => 'upi',
            'order_id'         =>  $orderId,
            'pk'               =>  $setting->user_pk,
            'amount'           => '101',
            'fund_passwd'      => '1',
            'withdraw_address' => '1',
            'upi_id'           => '1232312',
        ]);

        $res->assertStatus(200);
        $res->assertJsonFragment(['success'=>true]);
        $this->assertDatabaseHas('withdraw_orders', [
            'order_id' => $orderId,
            'status' => Status::PENDING
        ]);

    }
}
