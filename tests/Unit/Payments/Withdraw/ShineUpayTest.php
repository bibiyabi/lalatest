<?php

namespace Tests\Unit\Payments\Withdraw;

use App\Exceptions\InputException;
use App\Exceptions\NotifyException;
use App\Models\Merchant;
use App\Models\Order;
use App\Repositories\MerchantRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use App\Facades\Curl;
use App\Models\Setting;
use App\Models\WithdrawOrder;
use App\Services\Payments\Withdraw\WithdrawNotify;
use App\Services\Payments\WithdrawGateways\ShineUPay;
use Carbon\Factory;
use Database\Factories\OrderFactory;
use Database\Factories\WithdrawOrderFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ShineUpayTest extends TestCase
{
    use DatabaseTransactions;
    public function setUp():void
    {
        parent::setUp();
    }

    public function test_setRequest()
    {
        Log::shouldReceive('channel')->andReturnSelf();
        Log::shouldReceive('info')->andReturnSelf();

        Validator::shouldReceive('make')->andReturnSelf();
        Validator::shouldReceive('fails')->andReturn(true);
        Validator::shouldReceive('errors')->andReturn('test');


        $key = Setting::create([
            'user_id' => 1,
            'gateway_id' => 1,
            'user_pk' => 777,
            'settings' => '{"id":1,"user_id":1,"gateway_id":3,"merchant_number":"A5LB093F045C2322","md5_key":"fed8b982f9044290af5aba64d156e0d9", "private_key": "A948C01Y9JB47290"}'
        ]);

        $factory = new WithdrawOrderFactory();
        $orderArray = $factory->definition();
        $orderArray['order_id'] = '123456600131627297f';
        $orderArray['key_id'] = $key->id;
        $order = WithdrawOrder::create($orderArray);

        $this->expectException(InputException::class);

        $mock = $this->partialMock(ShineUPay::class);
        $mock->setRequest([], $order);
    }
}
