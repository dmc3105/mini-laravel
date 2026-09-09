<?php

namespace App\Container;

class Container {
    private array $bindings = [];    

    public function get(string $id) : object {
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

            $dependency = $this->get($type->getName());
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