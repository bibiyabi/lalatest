<?php

namespace App\Jobs\Payment\Withdraw;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Constants\Payments\CryptoCurrencyStatus;
use App\Constants\Payments\Status;
use App\Models\WithdrawOrder;
use App\Jobs\Payment\Withdraw\Notify;
use Illuminate\Support\Facades\Log;

/**
 * 數字貨幣查詢訂單
 */
class CryptoCurrencySearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 60*24;
    private $gateway;
    private $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($order, $gateway)
    {
        $this->gateway = $gateway;
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->gateway->setCurl();
        $result = $this->gateway->search($this->order);

        Log::info('CryptoCurrencySearch orderId:' . $this->order['order_id']. ' ,result code: ' . $result->getCode());

        switch($result->getCode()) {
            case CryptoCurrencyStatus::ORDER_NOT_FOUND:
            case CryptoCurrencyStatus::API_FAIL:
                $this->release(60);
                break;
            case CryptoCurrencyStatus::ORDER_FAIL:
                WithdrawOrder::where('order_id', $this->order['order_id'])
                ->update(['status' => STATUS::ORDER_FAILED]);
                Notify::dispatch($this->order, $result->getMsg());
                break;

            case CryptoCurrencyStatus::ORDER_SUCCESS:
                WithdrawOrder::where('order_id', $this->order['order_id'])
                ->update(['status' => STATUS::CALLBACK_SUCCESS]);
                Notify::dispatch($this->order, $result->getMsg());
                break;

            default:
                throw new \Exception($result->getCode() . ' code not found in CurrencySearch');
        }
    }
}
