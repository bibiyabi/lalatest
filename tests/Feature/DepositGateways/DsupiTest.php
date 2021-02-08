<?php

namespace Tests\Feature\WithdrawGateways;

use Tests\TestCase;
use Mockery;
use App\Models\Setting;
use App\Constants\Payments\Status;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Merchant;
use App\Models\Gateway;


class DsupiTest extends TestCase
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
            'name' => 'Dsupi',
            'real_name' => 'Dsupi',
        ])->create();

        $setting = Setting::factory([
            'user_id' => $this->user->id,
            'gateway_id' => $gateway->id,
            'user_pk' => 123,
            'settings' =>  '{"id":1,"user_id":1,"gateway_id":3,"account":"15555551234","merchant_number":"1022239","md5_key":"apHfz0UTH1PzSNvJThlFPvCirKMwV3Ds","note1":"api.fushrshinpay.com"}'

        ])->create();

        $orderId = 'D210121020135606534342';
        $response = $this->post('api/deposit/create', [
            'order_id' => $orderId,
            'pk' => $setting->user_pk,
            'type' => 'bank_card',
            'amount' => 123,
            'transaction_type' => 'BankCardTransferBankCard'
        ]);

        $response->assertJsonFragment(['success'=>true]);
        $this->assertDatabaseHas('orders', ['order_id'=>$orderId]);
    }
}
