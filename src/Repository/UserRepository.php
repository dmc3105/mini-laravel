<?php

namespace App\Repository;

use App\Database\Database;

class UserRepository implements UserRepositoryInterface {
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database= $database;
    }
}