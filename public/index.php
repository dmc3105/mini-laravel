<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Container\Container;
use App\Controller\UserController;
use App\Repository\UserRepository;
use App\Repository\UserRepositoryInterface;

$container = new Container();
$container->bind(UserRepositoryInterface::class, UserRepository::class);
$controller = $container->get(UserController::class);

var_dump($controller);