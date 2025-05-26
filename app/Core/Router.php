<?php

namespace App\Core;

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Router
{
    public function dispatch(): void
    {
        Logger::debug('Router dispatch started');
        
        $dispatcher = simpleDispatcher(function (RouteCollector $r) {
            $r->addRoute('GET', '/', ['App\Modules\Auth\Controllers\LoginController', 'redirectToProperPage']);
            $r->addRoute('GET', '/login', ['App\Modules\Auth\Controllers\LoginController', 'showLoginForm']);
            $r->addRoute('POST', '/login', ['App\Modules\Auth\Controllers\LoginController', 'handleLogin']);
            $r->addRoute('GET', '/logout', ['App\Modules\Auth\Controllers\LoginController', 'logout']);
            $r->addRoute('GET', '/dashboard', ['App\Modules\Auth\Controllers\LoginController', 'dashboard']);
        });

        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = $_SERVER['REQUEST_URI'];

        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        Logger::debug("Request: {$httpMethod} {$uri}");
        Logger::debug('Session in router: ' . print_r($_SESSION, true));

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
                Logger::debug('Route not found: ' . $uri);
                http_response_code(404);
                echo '404 Not Found';
                break;

            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                Logger::debug('Method not allowed: ' . $httpMethod . ' ' . $uri);
                http_response_code(405);
                echo '405 Method Not Allowed';
                break;

            case \FastRoute\Dispatcher::FOUND:
                [$class, $method] = $routeInfo[1];
                $vars = $routeInfo[2];

                Logger::debug("Route found: {$class}::{$method}");

                if (!Middleware::checkAccess($uri)) {
                    Logger::debug('Middleware denied access, redirecting to login');
                    (new Response())->redirect('/login')->send();
                    return;
                }

                Logger::debug('Middleware allowed access');

                if (class_exists($class) && method_exists($class, $method)) {
                    try {
                        $request = new \App\Core\Request();
                        $userRepo = new \App\Modules\Auth\Models\User(); // implements UserRepositoryInterface
                        $controller = new $class($userRepo, $request);

                        Logger::debug('Controller instantiated, calling method');
                        $result = call_user_func_array([$controller, $method], $vars);

                        if ($result instanceof \App\Core\Response) {
                            Logger::debug('Sending response');
                            $result->send();
                        } else {
                            Logger::debug('Echoing result');
                            echo $result ?? '';
                        }
                    } catch (\Exception $e) {
                        Logger::error('Controller execution failed: ' . $e->getMessage());
                        Logger::error('Stack trace: ' . $e->getTraceAsString());
                        http_response_code(500);
                        echo "500 Internal Server Error: " . $e->getMessage();
                    }
                } else {
                    Logger::error("Class or method not found: $class::$method");
                    http_response_code(500);
                    echo "500 Internal Server Error: $class::$method not found.";
                }
                break;
        }
    }
}