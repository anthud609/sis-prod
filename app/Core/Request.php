<?php
// App/Core/Request.php
namespace App\Core;

class Request {
    public function getMethod(): string {
        return $_SERVER['REQUEST_METHOD'];
    }

public function getUri(): string {
    $uri = $_SERVER['REQUEST_URI'];
    $pos = strpos($uri, '?');
    return $pos === false ? $uri : substr($uri, 0, $pos);
}


    public function post(string $key, $default = null) {
        return $_POST[$key] ?? $default;
    }

    public function session(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }
}
