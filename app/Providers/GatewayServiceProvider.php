<?php

namespace App\Providers;

use App\Exceptions\WithdrawException;
use Illuminate\Support\ServiceProvider;
use App\Services\Payments\Withdraw\AbstractWithdrawGateway;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

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
    public function boot(Request $request)
    {
        if ($this->isSegmentMatch($request)) {
            $gatewayName = $this->getSegmentN($request, 3);
            $this->createGateway($gatewayName);
        }
    }

    public function getSegmentN(Request $request, $n)
    {
        return $request->segment($n);
    }

    public function isSegmentMatch(Request $request)
    {
        return strtolower($request->segment(2)) == 'withdraw' && strtolower($request->segment(1)) == 'callback';
    }

    public function createGateway($gatewayName)
    {
        if (empty($gatewayName)) {
            throw new WithdrawException(__LINE__ . 'gateway name not found s', 22);
        }
        $filePath = app_path('Services/Payments/WithdrawGateways/' . $gatewayName. '.php');

        if (! file_exists($filePath)) {
            throw new WithdrawException($gatewayName . 'gateway not found', 22);
        }

        App::bind(AbstractWithdrawGateway::class, function ($app) use ($gatewayName) {
            $className = "App\Services\Payments\WithdrawGateways\\$gatewayName";
            return $app->make($className);
        });
    }

    public function provides()
    {
        return [AbstractWithdrawGateway::class];
    }
}
