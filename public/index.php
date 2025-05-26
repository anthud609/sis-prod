<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use Dotenv\Dotenv;
// Before session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Only if using HTTPS
ini_set('session.use_strict_mode', 1);
session_start();

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Handle request
$router = new Router();
$router->dispatch();
