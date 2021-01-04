<?php

namespace App\Providers;
use App\Payment\Withdraw\ApplePay;
use Illuminate\Support\ServiceProvider;
use App\Services\AbstractDepositPayment;
use Illuminate\Http\Request;
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
            AbstractDepositPayment::class,
            function() {
                return new ApplePay();
            }
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
