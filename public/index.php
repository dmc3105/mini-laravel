<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Database\Database;
use App\Container\Container;

$container = new Container();
$database = $container->get(Database::class);

var_dump($database);