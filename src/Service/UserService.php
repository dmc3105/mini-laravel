<?php

namespace App\Service;

use App\Controller\UserController;
use App\Repository\UserRepositoryInterface;

class UserService {
    private UserRepositoryInterface $repository;
    
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->repository = $userRepository;
    }
}