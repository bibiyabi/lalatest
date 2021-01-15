<?php

namespace App\Jobs\Payment\Withdraw;

use App\Repositories\SettingRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\AbstractWithdrawGateway;

use App\Models\WithdrawOrder;
use Illuminate\Support\Facades\Log;
use App\Exceptions\WithdrawException;

use Throwable;
class Order implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 30;
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
    public function handle(AbstractWithdrawGateway $paymentGateway, SettingRepository $settingRepository)
    {
        Log::debug('uuid:' . $this->job->uuid() . ' data:'. json_encode($this->request, true));

        echo "handle\r\n";
        print_r($this->request);
        echo "key_id: ".$this->request['key_id']." \r\n";
        $setting = collect($settingRepository->filterId($this->request['key_id'])->first());
        # gateway load database load config

        $gatewayConfigs = json_decode($setting->get('keys'), true);

        # gateway load payment
        try {
            $paymentGateway->setRequest($this->request, $setting);
            $res = $paymentGateway->send();

            if (!isset($res['code'])) {
                throw new WithdrawException('dw');
            }

        } catch (\Exception $e) {
            echo __LINE__. $e->getMessage();
            Log::channel('withdraw')->error(__LINE__ . 'uuid:' . $this->job->uuid() . $e->getMessage() , $gatewayConfigs);
            return;
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
