<?php

namespace App\Middleware;

use App\Routing\Middleware\MiddlewareInterface;

use App\Routing\Request;
use App\Routing\Response;
use Closure;

class RandomErrorMiddleware implements MiddlewareInterface {
    public function handle(
        Request $request,
        Closure $next
    ): Response
    {
        if (rand(0,1) == 1)
        {
            return Response::text("some error happend", 500);   
        }
        
        return $next($request);
    }
}