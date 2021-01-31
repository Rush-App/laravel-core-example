<?php

namespace App\Http\Middleware;

use Closure;

class ModifyHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle( $request, Closure $next )
    {
        $allowedOrigins = [
            'http://localhost:4200',
            'https://teadmus.org',
            'https://staging.teadmus.org',
            'https://admin.teadmus.org',
            'https://staging.admin.teadmus.org'
        ];
        $origin = array_key_exists('HTTP_ORIGIN', $_SERVER) ? $_SERVER['HTTP_ORIGIN'] : null;

        if (in_array($origin, $allowedOrigins)) {
            $response = $next($request);

            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE', 'OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Language, Authorization, X-Requested-With, Application');

            return $response;
        }

        return $response = $next($request);
    }
}
