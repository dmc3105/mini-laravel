<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Container\Container;
use App\Container\ContainerException;
use App\Controller\UserController;
use App\Repository\UserRepository;
use App\Repository\UserRepositoryInterface;

try {
    $container = new Container();
    $container->bind(UserRepositoryInterface::class, UserRepository::class);
    $container->singleton(UserController::class, UserController::class);
    $controller = $container->get(UserController::class);
    $controller->test = "1234";
    $controller2 = $container->get(UserController::class);
    echo $controller2->test;
} catch (ContainerException $ex) {
    echo "Error: " . $ex->getMessage();
}

