<?php

namespace App\Http\Middleware;

use App\Constants\Payments\ResponseCode;
use App\Repositories\MerchantRepository;
use App\Services\Signature;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder as RB;

class JavaApiKey
{
    private $repo;

    private $signService;

    public function __construct(MerchantRepository $repo, Signature $signService) {
        $this->repo = $repo;
        $this->signService = $signService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $key = $this->repo->getKey($user);

        $input = $request->input();
        $userSign = $input['sign'] ?? '';
        unset($input['sign']);

        $sign = $this->signService->makeSign($input, $key);

        if ($sign !== $userSign && config('app.env') !== 'local') {
            return RB::error(ResponseCode::ERROR_SIGN);
        }

        return $next($request);
    }
}
