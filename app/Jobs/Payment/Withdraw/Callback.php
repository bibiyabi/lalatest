<?php

namespace App\Jobs\Payment\Withdraw;

use App\Repositories\SettingRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Orders\WithdrawRepository;
use App\Services\Payments\Withdraw\AbstractWithdrawGateway;
use Illuminate\Support\Facades\Log;


class Callback implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $timeout = 30;
    private $request;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(AbstractWithdrawGateway $paymentGateway, WithdrawRepository $withdrawRepository, SettingRepository $settingRepository)
    {
        Log::debug('uuid:' . $this->job->uuid() . ' data:'. json_encode($this->request, true));

        $setting = collect($settingRepository->filterId($this->request['key_id'])->first());
        # gateway load database load config

        $gatewayConfigs = json_decode($setting->get('settings'), true);

    }

    private function checkSign() {

    }
}
