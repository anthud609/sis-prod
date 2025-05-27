<?php

namespace App\Core;

use App\Modules\Auth\Contracts\AuthServiceInterface;

class Middleware
{
    public static function checkAccess(string $uri, AuthServiceInterface $authService): bool
    {
        Logger::debug("Middleware checking access for URI: {$uri}");
        Logger::debug('User authenticated: ' . ($authService->isAuthenticated() ? 'YES' : 'NO'));
        
        $publicRoutes = ['/login', '/'];

        // If user is already logged in, don't allow access to login again
        if (in_array($uri, $publicRoutes) && $authService->isAuthenticated()) {
            Logger::debug('User is logged in, blocking access to public route, redirecting to dashboard');
            header('Location: /dashboard');
            exit;
        }

        // For protected routes
        $protectedRoutes = ['/dashboard'];
        if (in_array($uri, $protectedRoutes) && !$authService->isAuthenticated()) {
            Logger::debug('Protected route accessed without login, denying access');
            return false;
        }

        Logger::debug('Access granted for URI: ' . $uri);
        return true;
    }
}