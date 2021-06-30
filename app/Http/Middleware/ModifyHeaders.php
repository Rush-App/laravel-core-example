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
            /** for localhost **/
            'http://localhost:4201',
            'https://localhost:4201',

            /** for staging **/
//            'https://staging.teadmus.org',
//            'https://staging.admin.teadmus.org',

            /** for test checking callback payment **/
//            'https://414pshn44m.api.quickmocker.com',
//            'https://quickmocker.com'

            /** for production **/
            'https://teadmus.org',
            'https://admin.teadmus.org',
        ];

        $origin = array_key_exists('HTTP_ORIGIN', $_SERVER) ? $_SERVER['HTTP_ORIGIN'] : null;

        if (in_array($origin, $allowedOrigins)) {
            $response = $next($request);

            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE', 'OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Language, Authorization, X-Requested-With, Application, Stripe-Signature');
            $response->headers->set('Access-Control-Expose-Headers', 'Checkout_Session_Id, Response-Status, Main-Id');

            return $response;
        }

        return $response = $next($request);
    }
}
