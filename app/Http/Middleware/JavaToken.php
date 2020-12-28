<?php

namespace App\Http\Middleware;

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
            $response = [
              'status' => 2,
              'message'=> 'unauthorized',
            ];

            return response()->json($response);
//            return $response;
            // todo trow Exception
        }

        return $next($request);
    }
}
