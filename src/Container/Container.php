<?php

namespace App\Container;

class Container {
    
    public function get(string $id) : object {
        $reflection = new \ReflectionClass($id);

        return $reflection->newInstance();
    }
}