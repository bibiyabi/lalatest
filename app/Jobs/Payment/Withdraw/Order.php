<?php

namespace App\Jobs\Payment\Withdraw;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\AbstractWithdrawGateway;

use App\Repositories\GatewayRepository;
use App\Repositories\KeyRepository;
use App\Models\WithdrawOrder;
use App\Constants\WithDrawOrderStatus;
use App\Exceptions\WithdrawException;
use App\Services\Payments\PlatformNotify;

class Order implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $request;
    private $payment;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    public function getRequest() {
        return $this->request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(AbstractWithdrawGateway $paymentGateway, KeyRepository $keyRepository, PlatformNotify $platformNotify)
    {
        echo "handle\r\n";
        print_r($this->request);
        echo "key_id: ".$this->request['key_id']." \r\n";
        $key = collect($keyRepository->filterId($this->request['key_id'])->first());
        # gateway load database load config

        $gatewayConfigs = json_decode($key->get('keys'));

        # gateway load payment
        $paymentGateway->setRequest($gatewayConfigs);
        $res = $paymentGateway->send();

        if (!isset($res['code'])) {
            throw new WithdrawException('dw');
        }
        if ($res['code'] == WithDrawOrderStatus::FAIL) {
            #notify java
            $platformNotify->notifyWithdrawSuccess();
        }

        # set db
        WithdrawOrder::where('order_id', $this->request['order_id'])
        ->update(['status' => $res['code']]);

        echo 'end';
        # sned request
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
