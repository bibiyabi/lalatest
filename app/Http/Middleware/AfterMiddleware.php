<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
class AfterMiddleware
{
    public function handle($request, Closure $next)
    {
        // Perform action
        $response = $next($request);
        Log::channel('access')->debug('response', ['response' => $response]);

        return $response;
    }
}
