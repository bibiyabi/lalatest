<?php

namespace App\Http\Middleware;

use App\Common\ExceptionHandler;
use Closure;
use Illuminate\Http\Request;

class JavaToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->input('token') != 'iamtoken'){
            ExceptionHandler::exceptionThrow(config('testcode.E01'), 'token is incorrect.');
        }

        return $next($request);
    }
}
