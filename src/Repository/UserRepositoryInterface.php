<?php

namespace App\Repository;

use App\Models\User;

interface UserRepositoryInterface {
    public function getAll() : array;
}