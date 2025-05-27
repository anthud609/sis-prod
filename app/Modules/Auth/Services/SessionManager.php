<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Contracts\SessionManagerInterface;

class SessionManager implements SessionManagerInterface
{
    public function set(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function clear(): void
    {
        session_unset();
    }

    public function regenerateId(bool $deleteOldSession = true): void
    {
        session_regenerate_id($deleteOldSession);
    }

    public function destroy(): void
    {
        session_destroy();
    }

    public function getId(): string
    {
        return session_id();
    }
}