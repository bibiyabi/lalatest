<?php

namespace Tests\Feature;

use App\Exceptions\WithdrawException;
use App\Models\Gateway;
use App\Repositories\Orders\WithdrawRepository;
use Tests\TestCase;
use Mockery;
use App\Models\WithdrawOrder;
use App\Providers\GatewayServiceProvider;
use App\Services\Payments\WithdrawGateways\ShineUPay;

use Illuminate\Container\container;
use App\Services\Payments\Withdraw\AbstractWithdrawGateway;
use Illuminate\Database\Eloquent\Factories\Factory;

class GatewayTest extends TestCase
{
    public function setUp():void
    {
        parent::setUp();
        $this->mock = $this->initMock(WithdrawRepository::class);
    }

    private function initMock($class)
    {
        $mock = Mockery::mock($class);
        $container = Container::getInstance();
        $container->instance($class, $mock);

        return $mock;
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_create_gateway()
    {
        $gateway = Gateway::factory()->create([
            'name' => 'ShineUPay',
            'real_name' => 'ShineUPay',
        ]);

        $gatewayName = $gateway->name;
        $container = Container::getInstance();
        $className = "App\Services\Payments\WithdrawGateways\\$gatewayName";
        $this->assertInstanceOf(ShineUPay::class, $container->make($className));
        $gateway->delete();
    }

    public function test_service_provider_create_gateway_instance()
    {
        $container = Container::getInstance();
        $this->service_provider = new GatewayServiceProvider($container);
        $this->assertInstanceOf(GatewayServiceProvider::class, $this->service_provider);

        $this->service_provider->createGateway('ShineUPay');

        $gateway = app(AbstractWithdrawGateway::class);
        $this->assertInstanceOf(ShineUPay::class, $gateway);
    }

    public function test_gateway_not_found_return_withdraw_exception()
    {
        $this->expectException(WithdrawException::class);

        $container = Container::getInstance();
        $this->service_provider = new GatewayServiceProvider($container);
        $this->assertInstanceOf(GatewayServiceProvider::class, $this->service_provider);

        $unusedGateway = 'ABCDEFG';
        $this->service_provider->createGateway($unusedGateway);
    }
}
