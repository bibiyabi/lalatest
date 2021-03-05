<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use App\Models\Setting;
use App\Constants\Payments\Status;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\Merchant;
use App\Models\Gateway;


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
        //$this->markTestSkipped('還不用測');
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
}
