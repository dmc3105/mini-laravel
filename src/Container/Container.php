<?php

namespace Minilaravel\Container;

use Exception;

class Container
{
    private const MAX_RECURSION_DEPTH = 64;
    private array $bindings = [];
    private array $singletones = [];
    private array $instances = [];

    public function get(string $id): object
    {
        return $this->getInternal($id, 0);
    }

    private function getInternal(string $id, int $recursionDepth): object
    {
        if ($recursionDepth > self::MAX_RECURSION_DEPTH) {
            throw new ContainerException("The maximum recursion has been reached. A cyclic dependency is possible");
        }

        $resolvedId = 0;

        if ($this->hasBinding($id)) {
            $resolvedId = $this->getBinding($id);
        } elseif ($this->isSingleton($id)) {
            $resolvedId = $this->getSingleton($id);
            if (isset($this->instances[$resolvedId])){
                return $this->instances[$resolvedId];
            }
        } else {
            $resolvedId = $id;
        }

        $reflection = new \ReflectionClass($resolvedId);

        if ($reflection->isAbstract() or $reflection->isInterface()) {
            throw new ContainerException("Need to bind the abstract {$reflection->getName()} to a concrete class");
        }

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return $reflection->newInstance();
        }

        $arguments = [];

        foreach ($constructor->getParameters() as $parameter) {
            $type = $parameter->getType();

            if ($type === null) {
                throw new ContainerException("Cannot resolve parameter {$parameter->getName()}");
            }

            $dependency = $this->getInternal($type->getName(), $recursionDepth + 1);
            $arguments[] = $dependency;
        }

        $instance = $reflection->newInstanceArgs($arguments);

        if ($this->isSingleton($id) && !isset($this->instances[$id]))
        {
            $this->instances[$resolvedId] = $instance;
        }

        return $instance;
    }

    public function bind(string $abstract, string $concrete): void
    {
        if (isset($this->singletones[$abstract]))
            throw new ContainerException("Cannot register a class as a prototype and a singleton simultaneously");
        $this->bindings[$abstract] = $concrete;
    }

    public function singleton(string $abstract, string $concrete): void
    {
        if (isset($this->bindings[$abstract]))
            throw new ContainerException("Cannot register a class as a prototype and a singleton simultaneously");
        $this->singletones[$abstract] = $concrete;
    }

    public function instance(string $abstract, object $instance) : void {
        if (!isset($this->singletones[$abstract]))
        {
            throw new Exception("Cannot to register an instance if it is not a singleton");
        }
        $concrete = $this->singletones[$abstract];
        $this->instances[$concrete] = $instance;
    }

    private function hasBinding(string $id): bool
    {
        return isset($this->bindings[$id]);
    }

    private function isSingleton(string $id): bool
    {
        return isset($this->singletones[$id]);
    }

    private function getBinding(string $id): string
    {
        return $this->bindings[$id];
    }

    private function getSingleton(string $id): string
    {
        return $this->singletones[$id];
    }
}
