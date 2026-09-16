<?php

namespace App\Middleware;

use App\Routing\Middleware\MiddlewareInterface;

use App\Routing\Request;
use App\Routing\Response;
use Closure;

class LoggingMiddleware implements MiddlewareInterface {
    public function handle(
        Request $request,
        Closure $next
    ): Response
    {
        $start = microtime(true);

        error_log(sprintf(
            "[REQUEST] %s %s",
            $request->method(),
            $request->path()
        ));

        $response = $next($request);

        $duration = (microtime(true) - $start) * 1000;

        error_log(sprintf(
            "[RESPONSE] %d %.2fms",
            $response->status(),
            $duration
        ));

        return $response;
    }
}