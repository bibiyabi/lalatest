<?php

namespace App\Jobs\Payment\Deposit;

use App\Models\Order;
use App\Services\Payments\Deposit\DepositNotify;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Notify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 10;

    public $backoff = 180;

    private $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order->withoutRelations();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(DepositNotify $service)
    {
        $service->notify($this->order);
    }
}
