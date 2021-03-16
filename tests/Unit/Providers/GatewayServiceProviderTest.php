<?php

namespace Tests\Unit\Jobs\Payment\Withdraw;

use App\Constants\Payments\Status;
use App\Jobs\Payment\Withdraw\Notify;
use App\Models\Merchant;
use App\Models\WithdrawOrder;
use App\Providers\GatewayServiceProvider;
use App\Services\Payments\Withdraw\WithdrawNotify;
use Database\Factories\WithdrawOrderFactory;
use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerContainer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Mockery\MockInterface;
use Tests\TestCase;

class GatewayServiceProviderTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp():void
    {
        parent::setUp();

    }

    public function test_boot() {

        $request = Request::create('/callback/withdraw/ShineUPay', 'POST');

        $provider = $this->partialMock(GatewayServiceProvider::class, function (MockInterface $mock) {
            $mock->shouldReceive('createGateway')->once()->andReturn('');
            $mock->shouldReceive('getSegmentN')->once()->andReturn('');
            $mock->shouldReceive('isSegmentMatch')->once()->andReturn(true);

        });
        $provider->boot($request);
    }

    public function test_getSegmentN() {


        $request = Request::create('/callback/withdraw/ShineUPay', 'POST');

        $provider = $this->partialMock(GatewayServiceProvider::class);

        $this->assertEquals($provider->getSegmentN($request, 3), 'ShineUPay');


    }






}
