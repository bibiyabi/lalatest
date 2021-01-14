<?php

namespace App\Providers;

use App\Jobs\Payment\Withdraw\Order;

use App\Repositories\KeyRepository;
use App\Repositories\GatewayRepository;
use App\Exceptions\WithdrawException;
use App\Services\Payments\PlatformNotify;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Orders\WithdrawRepository;
use App\Services\AbstractWithdrawGateway;



class GatewayServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(GatewayRepository $gatewayRepository, KeyRepository $keyRepository, PlatformNotify $platformNotify, WithdrawRepository $withdrawRepository)
    {
        $this->app->bindMethod([Order::class, 'handle'], function ($job, $app)
            use ($gatewayRepository, $keyRepository) {

            $request = $job->getRequest();
            $request['gateway_id'] = 3;
            $gateway = $gatewayRepository->filterGatewayId($request['gateway_id'])->first();

            $gateway = collect($gateway);
            if (! $gateway->has('name')) {
                throw new WithdrawException('aaa', 0);
            }

            $gatewayName = $gateway->get('name');
            $className = "App\Services\Payments\WithdrawGateways\\$gatewayName";
            return $job->handle($app->make($className), $keyRepository);
        });


        $this->app->bindMethod([Notify::class, 'handle'], function ($job, $app)
            use ($withdrawRepository, $platformNotify) {
            return $job->handle($app->make(Notify::class), $withdrawRepository, $platformNotify);
        });


        $this->app->bind(AbstractWithdrawGateway::class, function ($app) {
            $gatewayName =  $app->request->segment(4);
            if (empty($gatewayName)) {
                throw new WithdrawException('aaa', 0);
            }
            $className = "App\Services\Payments\WithdrawGateways\\$gatewayName";
            return $app->make($className);
        });






    }
}
