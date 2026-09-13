<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Container\Container;
use App\Container\ContainerException;
use App\Controller\UserController;
use App\ORM\Database;
use App\ORM\Model;
use App\Repository\UserRepository;
use App\Repository\UserRepositoryInterface;
use App\Routing\Attributes\Route;
use App\Routing\Router;

try {
    $database = "mysql:hots=localhost;dbname=users_db;charset=utf8mb4";
    $user = "root";
    $password = "";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ];
    $database = new Database($database, $user, $password, $options);
    Model::setDatabase($database);

    $container = new Container();
    $container->bind(UserRepositoryInterface::class, UserRepository::class);
    $container->singleton(Database::class, Database::class);
    $container->instance(Database::class, $database);

    
    $router = new Router($container);
    $router->registerController(UserController::class);
    $router->dispatch();
} catch (ContainerException $ex) {
    echo "Error: " . $ex->getMessage();
}

