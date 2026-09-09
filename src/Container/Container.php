<?php

namespace App\Container;

class Container {
    
    public function get(string $id) : object {
        $reflection = new \ReflectionClass($id);

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
}