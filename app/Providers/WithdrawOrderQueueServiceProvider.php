<?php

namespace App\Providers;

use App\Repositories\GatewayRepository;
use App\Repositories\SettingRepository;
use Illuminate\Support\ServiceProvider;
use Exception;
use Illuminate\Support\Facades\Log;
use App\Jobs\Payment\Withdraw\Order;

class WithdrawOrderQueueServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bindMethod([Order::class, 'handle'], function ($job, $app) {
            $order = $job->getInputOrder();
            $gateway = $order->key->gateway;

            if (empty($gateway)) {
                throw new Exception('gateway name not found in ' . __CLASS__);
            }
            $gatewayName = $gateway->name;

            $className = "App\Services\Payments\WithdrawGateways\\$gatewayName";
            $filePath =  app_path('Services/Payments/WithdrawGateways/' . $gatewayName. '.php');

            if (! file_exists($filePath)) {
                throw new Exception(__LINE__ . $gatewayName . 'gateway file not found in ' . __CLASS__);
            }

            return $job->handle($app->make($className));
        });
    }
}
