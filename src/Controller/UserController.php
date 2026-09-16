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

    #[Route("/index")]
    public function index(Request $request, RandomService $randomService) : Response {
        $query = $request->query("q");
        $number = $randomService->generateRandomNumber(1, 20);
        return Response::json(["query" => $query, "number" => $number]);
    }

    #[Route("/user", "POST")]
    public function store(object $request)
    {

    }
}
