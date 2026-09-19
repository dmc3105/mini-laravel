<?php

namespace Minilaravel\Routing\Middleware;

use Minilaravel\Container\Container;
use Minilaravel\Routing\Request;
use Minilaravel\Routing\Response;
use Closure;

class Pipeline {
    /**
     * @var class-string<MiddlewareInterface>[]
     */
    private array $middlewares = [];

    public function __construct(
        private readonly Container $container
    )
    {
    }

    public function pipe(string $middleware) : self {
        $this->middlewares[] = $middleware;
    
        return $this;
    }

    public function then(
        Request $request,
        Closure $destination
    ): Response {
        $next = $destination;

        foreach(array_reverse($this->middlewares) as $middlewareClass) {
            $current = $next;

            $next = function (Request $request) 
            use ($middlewareClass, $current)
            : Response
            {
                $middleware = $this->container->get($middlewareClass);

                return $middleware->handle($request, $current);
            };
        }

        return $next($request);
    }
}