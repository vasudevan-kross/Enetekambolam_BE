<?php

namespace App\Http\Middleware;

use Closure;

class GlobalCors
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (! $response->headers->has('Access-Control-Allow-Origin')) {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, Authorization, X-Requested-With');
        }

        return $response;
    }
}
