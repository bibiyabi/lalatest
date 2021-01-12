<?php

namespace App\Jobs\Payment\Withdraw;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Repositories\Orders\WithdrawRepository;
use App\Services\AbstractWithdrawGateway;
use Illuminate\Support\Facades\Log;
use App\Repositories\KeyRepository;

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
    public function handle(AbstractWithdrawGateway $paymentGateway, WithdrawRepository $withdrawRepository, KeyRepository $keyRepository)
    {
        Log::debug('uuid:' . $this->job->uuid() . ' data:'. json_encode($this->request, true));

        $key = collect($keyRepository->filterId($this->request['key_id'])->first());
        # gateway load database load config

        $gatewayConfigs = json_decode($key->get('keys'), true);

    }

    private function checkSign() {

    }
}
