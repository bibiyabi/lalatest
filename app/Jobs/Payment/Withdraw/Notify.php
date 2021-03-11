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
use App\Services\Payments\Withdraw\WithdrawNotify;
use App\Constants\Payments\Status;
use Illuminate\Support\Facades\Log;
use App\Contracts\LogLine;

class Notify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    //public $timeout = 30;

    private $order;
    private $message;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order, $message = '')
    {
        $this->order = $order;
        $this->message = $message;
    }

    public function handle(WithdrawNotify $WithdrawNotify)
    {
        $this->order = WithdrawOrder::where('order_id', $this->order->order_id)->first();
        try {
            if (in_array($this->order->status, [
                Status::CALLBACK_FAILED,
                Status::ORDER_FAILED
            ]) && !$this->isResetedOrder() ) {
                $WithdrawNotify->setOrder($this->order)->setMessage($this->message)->notifyWithdrawFailed();
            }

            if (in_array($this->order->status, [
                Status::CALLBACK_SUCCESS,
            ]) &&  !$this->isResetedOrder()) {
                $WithdrawNotify->setOrder($this->order)->setMessage($this->message)->notifyWithdrawSuccess();
            }
        } catch (WithdrawException $e) {
            Log::channel('withdraw')->info(new LogLine($e));
        }

    }

    /**
     * 重置訂單 flag = 1
     *
     * @return void
     */
    private function isResetedOrder() {
        if ($this->order->no_notify == 1) {
            return true;
        }
        return false;
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
