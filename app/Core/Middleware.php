<?php

namespace App\Core;

class Middleware
{
    public static function checkAccess(string $uri): bool
    {
        Logger::debug("Middleware checking access for URI: {$uri}");
        Logger::debug('Session user_id in middleware: ' . ($_SESSION['user_id'] ?? 'NOT SET'));
        
        $publicRoutes = ['/login', '/'];

        // If user is already logged in, don't allow access to login again
        if (in_array($uri, $publicRoutes) && isset($_SESSION['user_id'])) {
            Logger::debug('User is logged in, blocking access to public route, redirecting to dashboard');
            header('Location: /dashboard');
            exit;
        }

        // For protected routes
        $protectedRoutes = ['/dashboard'];
        if (in_array($uri, $protectedRoutes) && !isset($_SESSION['user_id'])) {
            Logger::debug('Protected route accessed without login, denying access');
            return false;
        }

        Logger::debug('Access granted for URI: ' . $uri);
        return true;
    }
}