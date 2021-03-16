<?php

namespace App\Listeners;

use App\Events\DepositCallback;
use App\Services\Payments\Deposit\DepositNotifyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class DepositNotify implements ShouldQueue
{
    public $service;

    public $tries = 10;

    public $backoff = 180;

    public function __construct(DepositNotifyService $service)
    {
        $this->service = $service;
    }

    public function handle(DepositCallback $event)
    {
        Log::info('Deposit-callback-notify ' . $event->order->order_id);
        $this->service->notify($event->order);
    }

    public function shouldQueue(DepositCallback $event)
    {
        return $event->order->no_notify == false;
    }

    public function failed(\Throwable $e)
    {
        Log::warning('Deposit-notify-failed ' . $e->getMessage());
    }
}
