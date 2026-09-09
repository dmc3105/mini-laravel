<?php

namespace App\Controller;

use App\Service\UserService;

class UserController {
    private UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }
}