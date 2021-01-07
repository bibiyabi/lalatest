<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AbstractDepositPayment;
use Illuminate\Http\Request;
use App\Repositories\KeysRepository;
use App\Contracts\Payments\PaymentInterface;
use App\Payment\Withdraw\Payment;
use App\Models\key;
use App\Services\AbstractWithdrawGateway;
use App\Payment\Withdraw\ApplePay;
class PaymentServiceProvider extends ServiceProvider
{


    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->bind(
            PaymentInterface::class,
            Payment::class
        );






    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {



    }
}
