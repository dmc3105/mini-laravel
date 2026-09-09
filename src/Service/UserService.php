<?php

namespace App\Service;

use App\Repository\UserRepository;

class UserService {
    private UserRepository $repository;

    public function __construct(UserRepository $userRepository)
    {
        $this->repository = $userRepository;
    }
}