<?php

namespace App\Repository;

use App\ORM\Database;
use App\Models\User;
use Override;

class UserRepository implements UserRepositoryInterface {
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database= $database;
    }

    #[Override]
    public function getAll(): array
    {
        return User::query()->get();
    }
}