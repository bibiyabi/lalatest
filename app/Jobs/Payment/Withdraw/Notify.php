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

    private $request;
    private $withdrawRepository;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    public function handle(WithdrawRepository $withdrawRepository, PlatformNotify $platformNotify)
    {
        Log::debug('uuid:' . $this->job->uuid() . ' data:'. json_encode($this->request, true));

        $orderId  = $this->request['order_id'];
        $order = $withdrawRepository->filterOrderId($orderId)->first();

        if (!isset($order['order_id'])) {
            throw new WithdrawException('aaa');
        }

        if (in_array($order['status'], [
            Status::CALLBACK_SUCCESS,
            Status::ORDER_FAILED
        ])) {
            #notify java
            $platformNotify->notifyWithdrawFailed();
        }

        if (in_array($order['status'], [
            Status::CALLBACK_SUCCESS,
        ])) {
            #notify java
            $platformNotify->notifyWithdrawSuccess();
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
