<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\WithdrawOrder;
use Artisan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ArtisanCommandTest extends TestCase
{
    use DatabaseTransactions;

    public function test_deposit_order_clean()
    {
        $order = Order::factory([
            'created_at' => Carbon::now()->subDays(70)
        ])->create();

        Artisan::call('order:clean');

        $this->assertDatabaseMissing('orders', [
            'order_id' => $order->order_id
        ]);
    }

    public function test_withdraw_order_clean()
    {
        $order = WithdrawOrder::factory([
            'created_at' => Carbon::now()->subDays(70)
        ])->create();

        Artisan::call('order:clean');

        $this->assertDatabaseMissing('withdraw_orders', [
            'order_id' => $order->order_id
        ]);
    }
}
