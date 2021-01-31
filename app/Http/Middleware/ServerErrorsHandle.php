<?php

namespace App\Http\Middleware;

use Closure;
use Monolog\Logger;
use RushApp\Core\Services\LoggingService;

class ServerErrorsHandle
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (env('APP_ENV') === 'testing') {
            return $response;
        }

        $regexError500 = preg_match('/5[0-9][0-9]/', $response->getStatusCode());
        if ($regexError500 !== 0) {
            LoggingService::criticalServerErrorsLogging(
                $response,
                Logger::CRITICAL
            );

            return response()->json(['error' => __('response_messages.error_500')], 500);
        } else {
            return $response;
        }
    }
}
