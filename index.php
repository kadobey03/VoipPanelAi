<?php
require_once __DIR__.'/config/bootstrap.php';
use App\Core\Router;

$router = new Router();
$router->dispatch();
