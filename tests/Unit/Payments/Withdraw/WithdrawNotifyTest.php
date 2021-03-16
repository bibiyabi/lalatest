<?php

namespace Tests\Unit\Payments\Withdraw;

use App\Exceptions\NotifyException;
use App\Models\Merchant;
use App\Models\Order;
use App\Repositories\MerchantRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Facades\Curl;
use App\Models\WithdrawOrder;
use App\Services\Payments\Withdraw\WithdrawNotify;
use Carbon\Factory;
use Database\Factories\OrderFactory;
use Database\Factories\WithdrawOrderFactory;

class WithdrawNotifyTest extends TestCase
{

    public function setUp():void
    {
        parent::setUp();
    }


    /**
     *
     * @return void
     */
    public function test_notify()
    {

        $this->markTestSkipped('skip test');
        Curl::shouldReceive('setUrl')->andReturnSelf();
        Curl::shouldReceive('setPost')->andReturnSelf();
        Curl::shouldReceive('exec')->andReturn(['data' => json_encode(['status' => '200'])]);

        $notify = new WithdrawNotify();
        $factory = new WithdrawOrderFactory();
        $order = (object)$factory->definition();
        $notify->setOrder($order);
        $res = $notify->notify(1);
        $this->assertTrue($res);



    }

}
