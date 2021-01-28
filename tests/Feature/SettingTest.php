<?php

namespace Tests\Feature;


use App\Models\Gateway;
use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Models\Merchant;

class SettingTest extends TestCase
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

    public function testCreateSetting()
    {
        $this->withoutMiddleware();

        $gateway = Gateway::factory([
            'name' => 'pay',
            'real_name' => 'pay',
        ])->create();
        // create
        $data = json_encode([
            'info_title'=> 'i am title',
            'account'=> '666',
            'merchant_number'=> '888',
            'md5_key'=> 'i am key',
            'notify_url'=> 'http://google.con'

        ]);
        $response = $this->post('api/key',[
            'data' => urlencode($data),
            'gateway_id'=> $gateway->id,
            'id'=> 555,
        ]);

        $response->assertJsonFragment(['success'=> true]);
        // update
        $dataUpdate = json_encode([
            'info_title'=> 'i am title',
            'account'=> '8888',
            'merchant_number'=> '777',
            'publicKey'=> 'i am key',
            'privateKey'=> 'i am key',
            'notify_url'=> 'http://google.con'

        ]);
        $response = $this->post('api/key',[
            'data' => urlencode($dataUpdate),
            'gateway_id'=> $gateway->id,
            'id'=> 555,
        ]);

        $response->assertJsonFragment(['success'=> true]);

        $this->assertDatabaseHas('settings',[
            'user_pk' => 555,
            'gateway_id' => $gateway->id,
        ]);
    }

    public function testDeleteSetting()
    {
        $this->withoutMiddleware();

        $gateway = Gateway::factory([
            'name' => 'pay',
            'real_name' => 'pay',
        ])->create();

        $setting = Setting::factory([
            'user_id' => $this->user->id,
            'gateway_id' => $gateway->id,
            'user_pk' => 888,
        ])->create();

        $response = $this->delete('api/key',[
           'id' => $setting->user_pk,
        ]);

        $response->assertJsonFragment(['success'=> true]);
        $this->assertDatabaseMissing('settings',[
            'user_pk' => $setting->user_pk,
        ]);
    }
}
