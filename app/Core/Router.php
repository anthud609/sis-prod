<?php

namespace App\Core;

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Router
{
    public function dispatch(): void
    {
        $dispatcher = simpleDispatcher(function (RouteCollector $r) {
    // Default route — root path
    $r->addRoute('GET', '/', ['App\Modules\Auth\Controllers\LoginController', 'redirectToProperPage']);

    // Auth routes
    $r->addRoute('GET', '/login', ['App\Modules\Auth\Controllers\LoginController', 'showLoginForm']);
    $r->addRoute('POST', '/login', ['App\Modules\Auth\Controllers\LoginController', 'handleLogin']);
    $r->addRoute('GET', '/logout', ['App\Modules\Auth\Controllers\LoginController', 'logout']);

    // Protected route
    $r->addRoute('GET', '/dashboard', ['App\Modules\Auth\Controllers\LoginController', 'dashboard']);
});

        

        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);
        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                http_response_code(404);
                echo '404 Not Found';
                break;

            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                http_response_code(405);
                echo '405 Method Not Allowed';
                break;

            case \FastRoute\Dispatcher::FOUND:
                [$class, $method] = $routeInfo[1];
                $vars = $routeInfo[2];

                // Login check middleware
                if (!Middleware::checkAccess($uri)) {
                    header('Location: /login');
                    exit;
                }

                // Execute controller action
                if (class_exists($class) && method_exists($class, $method)) {
                    call_user_func_array([new $class, $method], $vars);
                } else {
                    http_response_code(500);
                    echo "500 Internal Server Error: $class::$method not found.";
                }
                break;
        }
    }
}
