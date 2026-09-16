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
    }

    public function dispatch() : void {
        $request = new Request();
        foreach ($this->mappings as $mapping) {
            if ($this->match($mapping["route"], $request)){
                echo $this->performControllerAction($mapping["class"], $mapping["action"]);
            }
        }
    }

    private function performControllerAction(string $controllerClass, string $action) : mixed
    {
        $reflection = new \ReflectionClass($controllerClass);
        $method = $reflection->getMethod($action);
        $arguments = [];
        foreach ($method->getParameters() as $parameter) {
            if ($parameter->getType()->getName() === Request::class) {
                $arguments[] = new Request();
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
