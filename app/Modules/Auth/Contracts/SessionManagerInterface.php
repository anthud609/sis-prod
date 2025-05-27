<?php

namespace App\Modules\Auth\Contracts;

interface SessionManagerInterface
{
    /**
     * Set a session value
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value): void;

    /**
     * Get a session value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Check if a session key exists
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Remove a session key
     *
     * @param string $key
     * @return void
     */
    public function remove(string $key): void;

    /**
     * Clear all session data
     *
     * @return void
     */
    public function clear(): void;

    /**
     * Regenerate session ID
     *
     * @param bool $deleteOldSession
     * @return void
     */
    public function regenerateId(bool $deleteOldSession = true): void;

    /**
     * Destroy the session
     *
     * @return void
     */
    public function destroy(): void;

    /**
     * Get session ID
     *
     * @return string
     */
    public function getId(): string;
}