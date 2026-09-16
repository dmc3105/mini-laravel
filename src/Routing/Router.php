<?php

namespace App\Routing;

use App\Container\Container;
use App\Routing\Response;
use App\Routing\Attributes\Route;
use App\Routing\Middleware\Pipeline;
use ReflectionMethod;

class Router
{
    private array $mappings = [];
    private Container $container;
    private array $middlewares = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function controller(string $controllerClass) : self {
        $reflection =  new \ReflectionClass($controllerClass);

        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $atrributes = $method->getAttributes(Route::class);
            
            if ($atrributes === []) {
                continue;
            }

            $route = $atrributes[0]->newInstance();
                
            $this->mappings[] = [
                "class" => $reflection->getName(),
                "action" => $method->getName(),
                "route" => $route,
            ];
        }
        return $this;
    }

    public function middleware(string $middleware) : self
    {
        $this->middlewares[] = $middleware;

        return $this;
    }

    public function dispatch() : void {
        $request = new Request();
        foreach ($this->mappings as $mapping) {
            if ($this->match($mapping["route"], $request)){
                $response = $this->runPipeline(
                    $request,
                    $mapping["class"],
                    $mapping["action"]
                );
                $response->send();
            }
        }
    }

    private function runPipeline(
        Request $request,
        string $controllerClass,
        string $action

    ) : Response {
        $pipeline = new Pipeline($this->container);

        foreach ($this->middlewares as $middleware) {
            $pipeline->pipe($middleware);
        }

        return $pipeline->then($request, 
        fn (Request $request) : Response => 
            $this->performControllerAction(
                $controllerClass,
                $action,
                $request
            )
        );
    }

    private function performControllerAction(
        string $controllerClass,
        string $action,
        Request $request
    ) : Response
    {
        $reflection = new \ReflectionClass($controllerClass);
        $method = $reflection->getMethod($action);
        $arguments = [];
        foreach ($method->getParameters() as $parameter) {
            if ($parameter->getType()->getName() === Request::class) {
                $arguments[] = $request;
            } else {
                $arguments[] = $this->container->get($parameter->getType()->getName());
            }
        }

        $controller = $this->container->get($controllerClass);
        return $method->invokeArgs($controller, $arguments);
    }

    private function match(Route $route, Request $request) : bool {
        return $route->path == $request->path() && $route->method == $request->method();
    }
}
