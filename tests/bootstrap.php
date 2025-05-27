<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Initialize session for testing
if (!session_id()) {
    session_start();
}

// Load test environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
try {
    $dotenv->load();
} catch (Exception $e) {
    // Environment file might not exist in test environment
}

// Set test database configuration if needed
$_ENV['DB_NAME'] = $_ENV['TEST_DB_NAME'] ?? 'test_database';

// Initialize logger for tests
App\Core\Logger::init();