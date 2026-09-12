<?php

namespace App\ORM;

class Database {
    private \PDO $pdo;

    public function __construct(
        string $database,
        string $user,
        string $password,
        array $options
        )
    {
        try {
            $this->pdo = new \PDO($database, $user, $password, $options);
        } catch (\PDOException $e) {
            die("Ошибка подключения к бд: " . $e->getMessage());
        }
    }

    public function getPdo(): \PDO {
        return $this->pdo;
    }
}