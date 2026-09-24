<?php

namespace Minilaravel\Routing;

use Minilaravel\Container\Container;
use Minilaravel\Routing\Response;
use Minilaravel\Routing\Attributes\Route;
use Minilaravel\Routing\Middleware\Pipeline;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;

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
            $attributes = $method->getAttributes(Route::class);

            if ($attributes === []) {
                continue;
            }

            foreach ($attributes as $attribute) {
                $route = $attribute->newInstance();

                $this->mappings[] = [
                    "class" => $reflection->getName(),
                    "action" => $method->getName(),
                    "route" => $route,
                ];
            }
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
            $params = $this->match($mapping["route"], $request);
            if ($params !== null){
                $response = $this->runPipeline(
                    $request,
                    $mapping["class"],
                    $mapping["action"],
                    $params
                );
                $response->send();
            }
        }
    }

    private function runPipeline(
        Request $request,
        string $controllerClass,
        string $action,
        array $params
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
                $request,
                $params
            )
        );
    }

    private function performControllerAction(
        string $controllerClass,
        string $action,
        Request $request,
        array $params
    ) : Response
    {
        $reflection = new \ReflectionClass($controllerClass);
        $method = $reflection->getMethod($action);
        $arguments = [];
        foreach ($method->getParameters() as $parameter) {
            $name = $parameter->getName();
            $type = $parameter->getType();

            if (array_key_exists($name, $params)) {
                $arguments[] = $this->cast($params[$name], $type);
                continue;
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin() && $type->getName() === Request::class) {
                $arguments[] = $request;
                continue;
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                $arguments[] = $this->container->get($type->getName());
                continue;
            }

            if ($parameter->isDefaultValueAvailable()) {
                $arguments[] = $parameter->getDefaultValue();
                continue;
            }

            $arguments[] = null;
        }

        $controller = $this->container->get($controllerClass);
        return $method->invokeArgs($controller, $arguments);
    }

    private function cast(mixed $value, ?ReflectionType $type) : mixed {
        if (!$type instanceof ReflectionNamedType || !$type->isBuiltin()) {
            return $value;
        }

        return match ($type->getName()) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOL),
            'string' => (string) $value,
            default => $value,
        };
    }

    private function match(Route $route, Request $request) : ?array {
        if (strtoupper($route->method) !== strtoupper($request->method())) {
            return null;
        }

        $pattern = preg_replace_callback(
            '/\{(\w+)\}/',
            fn ($m) => '(?P<' . $m[1] . '>[^/]+)',
            $route->path
        );
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $request->path(), $matches)) {
            return null;
        }

        $params = [];
        foreach ($matches as $key => $value) {
            if (is_string($key)) {
                $params[$key] = $value;
            }
        }

        return $params;
    }
}
