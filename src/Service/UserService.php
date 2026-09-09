<?php

namespace App\Service;

use App\Controller\UserController;
use App\Repository\UserRepositoryInterface;

class UserService {
    private UserRepositoryInterface $repository;
    private UserController $userController;

    public function __construct(UserRepositoryInterface $userRepository, UserController $userController)
    {
        $this->repository = $userRepository;
        $this->userController = $userController;
    }
}