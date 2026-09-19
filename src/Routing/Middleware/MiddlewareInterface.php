<?php

namespace Minilaravel\Routing\Middleware;

use Minilaravel\Routing\Request;
use Minilaravel\Routing\Response;
use Closure;

interface MiddlewareInterface
{
    public function handle(
        Request $request,
        Closure $next
    ): Response;
}
