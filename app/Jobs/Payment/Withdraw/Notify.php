<?php

namespace App\Jobs\Payment\Withdraw;

use App\Exceptions\WithdrawException;
use App\Models\WithdrawOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
use App\Repositories\Orders\WithdrawRepository;
use App\Services\Payments\PlatformNotify;
use App\Constants\Payments\Status;
use Illuminate\Support\Facades\Log;

class Notify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 30;

    private $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    public function handle(PlatformNotify $platformNotify)
    {
        if (in_array($this->order->status, [
            Status::CALLBACK_SUCCESS,
            Status::ORDER_FAILED
        ])) {
            $platformNotify->setOrder($this->order)->notifyWithdrawFailed();
        }

        if (in_array($this->order->status, [
            Status::CALLBACK_SUCCESS,
        ])) {
            $platformNotify->setOrder($this->order)->notifyWithdrawSuccess();
        }

    }

    /**
     * Handle a job failure.
     *
     * @param  \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        // Send user notification of failure, etc...
        echo $exception->getMessage();
    }


}
