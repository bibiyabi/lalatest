<?php

namespace Tests\Unit\Payments\Deposit;

use App\Exceptions\NotifyException;
use App\Models\Merchant;
use App\Models\Order;
use App\Repositories\MerchantRepository;
use App\Services\Payments\Deposit\DepositNotifyService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DepositNotifyTest extends TestCase
{
    use DatabaseTransactions;

    public $service;

    public function setup(): void
    {
        parent::setUp();

        $this->service = $this->app->make(DepositNotifyService::class);
    }

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

        $result = $this->service->notify(Order::factory()->make());

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

        $this->service->notify(Order::factory()->make());
    }
}
