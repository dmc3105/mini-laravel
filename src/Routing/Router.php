<?php

namespace App\Routing;

use App\Container\Container;
use App\Routing\Attributes\Route;
use ReflectionMethod;

class Router
{
    private array $mappings = [];
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function registerController(string $controllerClass) {
        $reflection =  new \ReflectionClass($controllerClass);

        $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $route = $method->getAttributes(Route::class)[0];
            
            if ($route === null) {
                continue;
            }

            $this->mappings[] = [
                "class" => $reflection->getName(),
                "action" => $method->getName(),
                "route" => $route,
            ];
        }
    }

    public function dispatch() : void {
        $request = new Request();
        foreach ($this->mappings as $mapping) {
            if ($this->match($mapping["route"], $request)){
                $this->performControllerAction($mapping["class"], $mapping["action"]);
            }
        }
    }

    private function performControllerAction(string $controllerClass, string $action)
    {
        $reflection = new \ReflectionClass($controllerClass);
        $method = $reflection->getMethod($action);

        $controller = $this->container->get($controllerClass);
        $method->invoke($controller);
    }

    private function match(Route $route, Request $request) : bool {
        return $route->path == $request->path() && $route->method == $request->method();
    }
}
