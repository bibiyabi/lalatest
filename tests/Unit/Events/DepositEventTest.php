<?php

namespace Tests\Unit\Events;

use App\Events\DepositCallback;
use App\Listeners\DepositNotify;
use App\Models\Order;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DepositEventTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_trigger_deposit_notify()
    {
        Queue::fake();

        $order = Order::factory(['no_notify'=>false])->make();
        DepositCallback::dispatch($order);

        Queue::assertPushed(CallQueuedListener::class, function ($job) {
            return $job->class == DepositNotify::class;
        });
    }

    public function test_silence_deposit_callback()
    {
        Queue::fake();

        $order = Order::factory(['no_notify'=>true])->make();
        DepositCallback::dispatch($order);

        Queue::assertNothingPushed();
    }

    public function test_failed_handle()
    {
        Log::shouldReceive('warning')->once()->with('Deposit-notify-failed asdf');

        $service = $this->app->make(DepositNotify::class);

        $service->failed(new \Exception("asdf"));
    }
}
