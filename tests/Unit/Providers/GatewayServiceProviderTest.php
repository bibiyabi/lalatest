<?php

namespace Tests\Unit\Jobs\Payment\Withdraw;

use App\Providers\GatewayServiceProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
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
