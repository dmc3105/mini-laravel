<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Container\Container;
use App\Controller\UserController;
use App\Repository\UserRepository;

$container = new Container();
$controller = $container->get(UserController::class);

var_dump($controller);