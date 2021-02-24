<?php

namespace App\Jobs\Payment\Withdraw;

use App\Repositories\SettingRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Constants\Payments\ResponseCode;
use App\Exceptions\InputException;
use App\Services\AbstractWithdrawGateway;
use App\Jobs\Payment\Withdraw\Notify;
use App\Models\WithdrawOrder;
use Illuminate\Support\Facades\Log;
use App\Exceptions\WithdrawException;
use Illuminate\Http\Request;
use App\Constants\Payments\Status;
use App\Contracts\LogLine;
use App\Constants\Payments\Type;
use App\Jobs\Payment\Withdraw\CryptoCurrencySearch;
use Throwable;
class Order implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    //public $timeout = 30;
    private $request;
    private $payment;
    private $order;
    private $setting;
    private $post;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($post, WithdrawOrder $order)
    {
        $this->post = $post;
        $this->order = $order;

        if (empty($order)) {
            throw new WithdrawException('order not set' , ResponseCode::EXCEPTION);
        }
    }

    public function getInputOrder() {
        return $this->order;
    }

    /**p
     * Execute the job.
     *
     * @return void
     */
    public function handle(AbstractWithdrawGateway $paymentGateway)
    {
        try {
            $paymentGateway->setRequest($this->post, $this->order);
            $res = $paymentGateway->send();

            if (!isset($res['code'])) {
                throw new WithdrawException('res code not set' , ResponseCode::EXCEPTION);
            }

            WithdrawOrder::where('order_id', $this->post['order_id'])
            ->update(['status' => $res['code']]);

            if ($this->post['type'] == Type::CRYPTO_CURRENCY) {
                $order = WithdrawOrder::where('order_id', $this->post['order_id'])->first();
                CryptoCurrencySearch::dispatch($order, $paymentGateway)->onQueue('cryptocurrency');
                Log::channel('withdraw')->info(new LogLine('數字貨幣訂單 search queue sended: ' . $this->post['order_id']));
            }

        } catch (Throwable $e) {

            Log::channel('withdraw')->info(new LogLine($e));

            if (in_array($e->getCode(), [Status::ORDER_ERROR, Status::ORDER_FAILED, Status::ORDER_SUCCESS])) {
                WithdrawOrder::where('order_id', $this->post['order_id'])
                ->update(['status' => $e->getCode()]);

                if ($e->getCode() == Status::ORDER_FAILED) {
                    Notify::dispatch($this->order, $e->getMessage())->onQueue('notify');
                }
            }
        }
    }

}
