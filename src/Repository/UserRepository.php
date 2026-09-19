<?php

namespace App\Repository;

use App\Models\User;
use Override;

class UserRepository implements UserRepositoryInterface {
    #[Override]
    public function getAll(): array
    {
        return User::query()->get();
    }
}