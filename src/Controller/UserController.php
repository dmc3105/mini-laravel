<?php

namespace App\Controller;

use App\Service\UserService;
use App\Routing\Attributes\Route;

class UserController
{
    private UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    #[Route("/user")]
    public function index() {
        return "hello";
    }

    #[Route("/user", "POST")]
    public function store(object $request)
    {

    }
}
