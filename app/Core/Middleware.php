<?php

namespace App\Core;

class Middleware
{
    public static function checkAccess(string $uri): bool
    {
        $publicRoutes = ['/login'];

        // If user is already logged in, don't allow access to login again
        if (in_array($uri, $publicRoutes) && isset($_SESSION['user_id'])) {
            header('Location: /dashboard');
            exit;
        }

        // For protected routes
        $protectedRoutes = ['/dashboard'];
        if (in_array($uri, $protectedRoutes) && !isset($_SESSION['user_id'])) {
            return false;
        }

        return true;
    }
}
