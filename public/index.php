<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Container\Container;
use App\Controller\UserController;

$container = new Container();
$database = $container->get(UserController::class);

var_dump($database);