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
    public function boot(GatewayRepository $gatewayRepository, SettingRepository $settingRepository)
    {

        $this->app->bindMethod([Order::class, 'handle'], function ($job, $app)
        use ($gatewayRepository, $settingRepository) {

            Log::channel('withdraw')->info(__FUNCTION__ . __LINE__, $job->getRequest());
            $request = $job->getRequest();
            $gateway = $gatewayRepository->filterGatewayId($request['gateway_id'])->first();

            $gateway = collect($gateway);
            if (! $gateway->has('name')) {
                throw new Exception('gateway name not found', 22);
            }
            $gatewayName = $gateway->get('name');

            $className = "App\Services\Payments\WithdrawGateways\\$gatewayName";
            $filePath = app_path(). '\Services\Payments\WithdrawGateways\\' . $gatewayName. '.php';

            if (! file_exists($filePath)) {
                throw new Exception(__LINE__ . $gatewayName . 'gateway not found', 22);
            }

            return $job->handle($app->make($className), $settingRepository);
        });
    }
}
