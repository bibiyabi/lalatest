<?php

namespace Tests\Feature;

use App\Models\Gateway;
use App\Models\GatewayType;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use App\Constants\Payments\Type;

class PlaceholderTest extends TestCase
{
    use DatabaseTransactions;

    private $user;

    public function testGatewayList()
    {
        $this->withoutMiddleware();

        $gateway = Gateway::factory([
            'name' => 'pay',
            'real_name' => 'pay',
        ])->create();

        $gatewayType = GatewayType::factory([
            'gateways_id'           => $gateway->id,
            'types_id'              => Type::type['e_wallet'],
            'is_support_deposit'    => 1,
            'is_support_withdraw'   => 0
        ])->create();

        $response = $this->call('get','api/vendor/list',[
            'is_deposit' => 1,
            'type'=> 'e_wallet'
        ]);

        $response->assertJsonFragment(['success'=> true]);

        $this->assertDatabaseHas('gateway_types',[
            'types_id' => Type::type['e_wallet'],
            'is_support_deposit' => 1
        ]);
    }

    public function testPlaceholder()
    {
        $this->withoutMiddleware();

        $response = $this->call('get','api/placeholder',[
            'is_deposit' => 0,
            'type'=> 'bank_card',
            'gateway_name'=> 'ShineUPay'
        ]);

        $response->assertJsonFragment(['success'=> true]);
    }

    public function testRequirement()
    {
        $this->withoutMiddleware();

        $response = $this->call('get','api/requirement',[
            'is_deposit' => 0,
            'type'=> 'bank_card',
            'gateway_name'=> 'ShineUPay'
        ]);

        $response->assertJsonFragment(['success'=> true]);
    }
}
