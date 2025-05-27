<?php

namespace App\Core;

use App\Modules\Auth\Contracts\{
    UserRepositoryInterface,
    AuthServiceInterface,
    ValidatorInterface,
    SessionManagerInterface
};
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Services\{
    AuthService,
    AuthValidator,
    SessionManager
};

class ServiceBootstrap
{
    public static function registerServices(ServiceContainer $container): void
    {
        // Register User Repository
        $container->singleton(UserRepositoryInterface::class, function() {
            return new User();
        });

        // Register Session Manager
        $container->singleton(SessionManagerInterface::class, function() {
            return new SessionManager();
        });

        // Register Validator
        $container->singleton(ValidatorInterface::class, function() {
            return new AuthValidator();
        });

        // Register Auth Service
        $container->singleton(AuthServiceInterface::class, function(ServiceContainer $container) {
            return new AuthService(
                $container->resolve(UserRepositoryInterface::class),
                $container->resolve(SessionManagerInterface::class),
                5, // max attempts
                900 // lockout time (15 minutes)
            );
        });

        // Register Request
        $container->register(Request::class, function() {
            return new Request();
        });
    }
}