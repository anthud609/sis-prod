<?php

namespace App\Core;

use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Router
{
    public function dispatch(): void
    {
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

                if (!Middleware::checkAccess($uri)) {
                    (new Response())->redirect('/login')->send();
                    return;
                }

                if (class_exists($class) && method_exists($class, $method)) {
                    $request = new \App\Core\Request();
                    $userRepo = new \App\Modules\Auth\Models\User(); // implements UserRepositoryInterface
                    $controller = new $class($userRepo, $request);

                    $result = call_user_func_array([$controller, $method], $vars);

                    if ($result instanceof \App\Core\Response) {
                        $result->send();
                    } else {
                        echo $result ?? '';
                    }
                } else {
                    http_response_code(500);
                    echo "500 Internal Server Error: $class::$method not found.";
                }
                break;
        }
    }
}
