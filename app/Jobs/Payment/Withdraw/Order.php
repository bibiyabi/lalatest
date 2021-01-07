<?php

namespace App\Jobs\Payment\Withdraw;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Services\AbstractWithdrawGateway;

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
        echo " job order construct \r\n";
        $this->request = $request;

        print_r($request, true);

        # fack
        $this->request = [];
        $this->request['user_pk'] = 1;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(AbstractWithdrawGateway $gateway)
    {
        echo "handle\r\n";
        # request get gateway
        $name = 'applepay';
        # gateway load database load config
        $config = ['md5' => 'md5test', 'account' => '12345676'];
        # gateway load payment
        $gateway->setRequest('')->send();
        # sned request
    }


}
