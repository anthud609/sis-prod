<?php

namespace App\Modules\Auth\Contracts;

interface ValidatorInterface
{
    /**
     * Validate login input
     *
     * @param string $email
     * @param string $password
     * @return array Array of validation errors (empty if valid)
     */
    public function validateLogin(string $email, string $password): array;

    /**
     * Validate CSRF token
     *
     * @param string $submittedToken
     * @param string $sessionToken
     * @return bool
     */
    public function validateCsrfToken(string $submittedToken, string $sessionToken): bool;

    /**
     * Validate email format
     *
     * @param string $email
     * @return bool
     */
    public function isValidEmail(string $email): bool;

    /**
     * Check if password meets requirements
     *
     * @param string $password
     * @return bool
     */
    public function isValidPassword(string $password): bool;
}