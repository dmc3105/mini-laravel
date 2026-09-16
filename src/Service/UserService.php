<?php

namespace App\Service;

use App\Controller\UserController;
use App\Repository\UserRepositoryInterface;
use App\Models\User;

class UserService {
    private UserRepositoryInterface $repository;
    
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->repository = $userRepository;
    }

    public function getAll(): array
    {
        return $this->repository->getAll();
    }
}