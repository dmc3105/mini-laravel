<?php

namespace App\Routing\Middleware;

use App\Routing\Request;
use App\Routing\Response;
use Closure;

interface MiddlewareInterface
{
    public function handle(
        Request $request,
        Closure $next
    ): Response;
}
