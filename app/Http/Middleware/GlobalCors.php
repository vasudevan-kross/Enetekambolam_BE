<?php

namespace App\Http\Middleware;

use Closure;

class GlobalCors
{
    public function handle($request, Closure $next)
    {
        $allowedOrigins = ['https://entekambolam.vercel.app', 'http://localhost:3000'];
        $origin = $request->header('Origin');

        $headers = [
            'Access-Control-Allow-Methods'     => 'GET, POST, PUT, DELETE, OPTIONS',
            'Access-Control-Allow-Headers'     => 'Origin, Content-Type, Accept, Authorization, X-Requested-With',
            'Access-Control-Allow-Credentials' => 'true',
        ];

        // Allow if the request has no Origin (e.g., mobile app), or a valid one
        if ($origin && in_array($origin, $allowedOrigins)) {
            $headers['Access-Control-Allow-Origin'] = $origin;
        } elseif (!$origin) {
            // Allow non-browser environments (like Flutter mobile)
            $headers['Access-Control-Allow-Origin'] = '*';
        }

        if ($request->getMethod() === 'OPTIONS') {
            return response()->json(['message' => 'CORS Preflight'], 200, $headers);
        }

        $response = $next($request);
        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }
}
