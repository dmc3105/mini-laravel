<?php

namespace App\Controller;

use App\Service\UserService;

class UserController {
    public string $test;
    private UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }
}