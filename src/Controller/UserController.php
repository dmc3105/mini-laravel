<?php

namespace App\Controller;

use App\Routing\Response;
use App\Service\UserService;
use App\Routing\Attributes\Route;
use App\Routing\Request;
use App\Service\RandomService;

class UserController
{
    private UserService $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    #[Route("/user")]
    public function index(Request $request, RandomService $randomService) : Response {
        return Response::json($this->service->getAll());
    }

    #[Route("/user", "POST")]
    public function store(object $request)
    {

    }
}
