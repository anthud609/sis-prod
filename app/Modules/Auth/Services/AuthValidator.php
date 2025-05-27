<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Contracts\ValidatorInterface;
/**
 * Class AuthValidator
 * 
 * This service class implements validation logic for authentication-related data.
 * It ensures that inputs for login and security tokens meet expected criteria.
 * 
 * Implements the ValidatorInterface to guarantee consistency and allow
 * potential multiple implementations or mocking for testing.
 * 
 * TODO: Remove CSRF token validation from this class and move it to a dedicated CSRF service.
 * 
 * @package App\Modules\Auth\Services
 */
class AuthValidator implements ValidatorInterface
{
    /**
     * Validate login input fields: email and password.
     * 
     * This method checks that both email and password are provided and
     * meet minimum validation criteria:
     * - Email must not be empty and must be a valid email format.
     * - Password must not be empty and must have at least 6 characters.
     * 
     * Returns an array of error messages. If empty, validation passed.
     * 
     * @param string $email    The user submitted email address.
     * @param string $password The user submitted password.
     * 
     * @return string[] Array of error messages; empty if no errors.
     */
    public function validateLogin(string $email, string $password): array
    {
        // Initialize an empty array to collect validation error messages
        $errors = [];
        
        // Check if email field is empty
        if (empty($email)) {
            $errors[] = "Email is required.";
        } 
        // If email is provided, verify format is valid
        elseif (!$this->isValidEmail($email)) {
            $errors[] = "Please enter a valid email address.";
        }
        
        // Check if password field is empty
        if (empty($password)) {
            $errors[] = "Password is required.";
        } 
        // If password is provided, verify it meets minimum length requirement
        elseif (!$this->isValidPassword($password)) {
            $errors[] = "Password must be at least 6 characters long.";
        }
        
        // Return all accumulated error messages (empty array means validation passed)
        return $errors;
    }

     /**
     * Validate CSRF token to prevent Cross-Site Request Forgery attacks.
     * 
     * This method securely compares a submitted CSRF token with the one
     * stored in the user's session using timing-attack safe comparison.
     * 
     * @param string $submittedToken The CSRF token received from client (form or header).
     * @param string $sessionToken   The CSRF token stored on the server/session.
     * 
     * @return bool True if tokens match exactly, false otherwise.
     */
    public function validateCsrfToken(string $submittedToken, string $sessionToken): bool
    {
        // Use hash_equals to prevent timing attacks during string comparison
        return hash_equals($sessionToken, $submittedToken);
    }

    /**
     * Check if the provided email is valid.
     * 
     * Uses PHP's built-in filter_var function with FILTER_VALIDATE_EMAIL
     * to validate the email format according to RFC standards.
     * 
     * @param string $email Email string to validate.
     * 
     * @return bool True if email format is valid, false otherwise.
     */
    public function isValidEmail(string $email): bool
    {
        // filter_var returns false on invalid email, otherwise returns filtered email string
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Check if the provided password meets the minimal security requirements.
     * 
     * Currently, this method only enforces a minimum length of 6 characters.
     * This can be extended later to include other rules such as complexity,
     * character sets, or banned password checks.
     * 
     * @param string $password Password string to validate.
     * 
     * @return bool True if password is valid, false otherwise.
     */
    public function isValidPassword(string $password): bool
    {
        // TODO: Consider extending password validation to include:
        // - Minimum length increased (e.g., 8 or more)
        // - Inclusion of uppercase, lowercase, numeric, special characters
        // - Check against commonly used or breached passwords list
        return strlen($password) >= 6;
    }
}