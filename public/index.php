<?php

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Logger;
use Dotenv\Dotenv;

// Initialize logger first
Logger::init();
Logger::debug('Application starting...');

// Before session_start() - FIXED: Only set secure cookie if using HTTPS
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

// FIXED: Only set secure cookies if we're using HTTPS
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
    Logger::debug('HTTPS detected - secure cookies enabled');
} else {
    Logger::debug('HTTP detected - secure cookies disabled');
}

Logger::debug('Starting session...');
session_start();

Logger::debug('Session started. Session ID: ' . session_id());
Logger::debug('Session data: ' . print_r($_SESSION, true));

// Load environment
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    Logger::debug('Environment loaded successfully');
} catch (Exception $e) {
    Logger::error('Failed to load environment: ' . $e->getMessage());
}

// Handle request
try {
    Logger::debug('Dispatching router...');
    $router = new Router();
    $router->dispatch();
} catch (Exception $e) {
    Logger::error('Router dispatch failed: ' . $e->getMessage());
    http_response_code(500);
    echo "500 Internal Server Error";
}