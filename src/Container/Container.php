<?php

namespace App\Container;

class Container {
    private const MAX_RECURSION_DEPTH = 64;
    private array $bindings = [];    

    public function get(string $id) : object {
        return $this->getInternal($id, 0);
    }

    private function getInternal(string $id, int $recursionDepth) : object {
        if($recursionDepth > self::MAX_RECURSION_DEPTH) {
            throw new ContainerException("The maximum recursion has been reached. A cyclic dependency is possible");
        }

        $id = $this->hasBinding($id) ? $this->getBinding($id) : $id;
        $reflection = new \ReflectionClass($id);

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
                throw new ContainerException("Cannot resolve parameter {parameter->getName()}");
            }

            $dependency = $this->getInternal($type->getName(), $recursionDepth + 1);
            $arguments[] = $dependency;
        }

        return $reflection->newInstanceArgs($arguments);
    }
    
    public function bind(string $abstract, string $concrete) : void {
        $this->bindings[$abstract] = $concrete;
    }

    private function hasBinding(string $id) : bool {
        return isset($this->bindings[$id]);
    }

    private function getBinding(string $id) : string {
        return $this->bindings[$id];
    }
}