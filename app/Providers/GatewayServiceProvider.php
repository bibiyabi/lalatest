<?php

namespace App\Providers;

use App\Exceptions\WithdrawException;
use Illuminate\Support\ServiceProvider;
use App\Services\AbstractWithdrawGateway;
use Illuminate\Contracts\Support\DeferrableProvider;
class GatewayServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        // queue.php 為sync 測試再打開
        //$this->createGateway('ShineUPay');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

        if ($this->app->request->segment(3) == 'withdraw' && $this->app->request->segment(4) == 'callback') {
            $gatewayName = $this->app->request->segment(4);

            $this->createGateway($gatewayName);
        }




    }

    public function createGateway($gatewayName) {

        if (empty($gatewayName)) {
            throw new WithdrawException(__LINE__ . 'gateway name not found s', 22);
        }
        $filePath = app_path(). '\Services\Payments\WithdrawGateways\\' . $gatewayName. '.php';

        if (! file_exists($filePath)) {
            throw new WithdrawException($gatewayName . 'gateway not found', 22);
        }

        $this->app->bind(AbstractWithdrawGateway::class, function ($app) use ($gatewayName){
            $className = "App\Services\Payments\WithdrawGateways\\$gatewayName";
            return $app->make($className);
        });



    }

    public function provides()
    {
        return [AbstractWithdrawGateway::class];
    }
}
