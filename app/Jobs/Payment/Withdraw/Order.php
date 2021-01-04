<?php

namespace App\Jobs\Payment\Withdraw;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\AbstractDepositPayment;

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

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle( AbstractDepositPayment $depositPayment)
    {
        # request get gateway
        $gateway = 'applepay';
        # gateway load database load config
        $config = ['md5' => 'md5test', 'account' => '12345676'];
        # gateway load payment
        $depositPayment->setRequest($this->request)->send();
        # sned request
    }
}
