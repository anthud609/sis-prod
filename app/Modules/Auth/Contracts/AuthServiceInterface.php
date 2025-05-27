<?php

namespace App\Modules\Auth\Contracts;

interface AuthServiceInterface
{
    /**
     * Attempt to authenticate a user with email and password
     *
     * @param string $email
     * @param string $password
     * @return AuthResult
     */
    public function authenticate(string $email, string $password): AuthResult;

    /**
     * Log in a user (set session data)
     *
     * @param array $user
     * @return void
     */
    public function login(array $user): void;

    /**
     * Log out the current user
     *
     * @return void
     */
    public function logout(): void;

    /**
     * Check if a user is currently authenticated
     *
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * Get the current authenticated user
     *
     * @return array|null
     */
    public function getCurrentUser(): ?array;

    /**
     * Check if an email is rate limited
     *
     * @param string $email
     * @return bool
     */
    public function isRateLimited(string $email): bool;

    /**
     * Record a failed login attempt
     *
     * @param string $email
     * @return void
     */
    public function recordFailedAttempt(string $email): void;

    /**
     * Clear failed login attempts
     *
     * @param string $email
     * @return void
     */
    public function clearFailedAttempts(string $email): void;
}