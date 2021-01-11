<?php

namespace App\Jobs\Payment\Withdraw;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Collections\ApplePayCollection;
use App\Collections\BanksCollection;
use App\Exceptions\WithdrawException;
use App\Services\AbstractWithdrawGateway;
use App\Repositories\KeyRepository;
use App\Repositories\GatewayRepository;

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
    public function handle(AbstractWithdrawGateway $gateway )
    {
        echo '=================1';

        echo "handle\r\n";
        # request get gateway
        $name = 'applepay';
        # gateway load database load config
        $config = ['md5' => 'md5test', 'account' => '12345676'];
        # gateway load payment
        $gateway->setRequest($config);
        $res = $gateway->send();
        # set db

        echo 'end';
        # sned request
    }


}
