<?php

namespace Tests\Unit\Payments\Deposit;

use App\Exceptions\NotifyException;
use App\Models\Merchant;
use App\Models\Order;
use App\Repositories\MerchantRepository;
use App\Services\Payments\Deposit\DepositNotify;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DepositNotifyTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test deposit order notify success.
     *
     * @return void
     */
    public function test_success_flow()
    {
        Http::fake([
            '*' => Http::response([
                'message' => 'success',
                'status' => '200',
            ])
        ]);

        $merchant = Merchant::factory()->create();
        $service = new DepositNotify(new MerchantRepository());
        $result = $service->notify(Order::factory(['user_id'=>$merchant->id])->create());

        $this->assertTrue($result);
    }

    public function test_failed_flow()
    {
        Http::fake([
            '*' => Http::response([
                'message' => 'failed',
                'status' => '404',
            ])
        ]);

        $this->expectException(NotifyException::class);

        $merchant = Merchant::factory()->create();
        $service = new DepositNotify(new MerchantRepository());
        $service->notify(Order::factory(['user_id'=>$merchant->id])->create());
    }
}
