<?php

namespace Tests\Unit\Modules\Auth\Services;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Modules\Auth\Services\AuthValidator;

/**
 * Class AuthValidatorTest
 *
 * Unit tests for the AuthValidator class covering:
 *  - Email validation (valid/invalid format)
 *  - Password validation (length/empty check)
 *  - CSRF token validation
 *  - Full login validation including error messaging
 *
 * @package Tests\Unit\Modules\Auth\Services
 */
class AuthValidatorTest extends TestCase
{
    /**
     * Instance of the class under test.
     *
     * @var AuthValidator
     */
    protected AuthValidator $validator;

    /**
     * Setup method run before each test.
     * Instantiates the AuthValidator.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new AuthValidator();
    }

    // ────────────────────────────────────────────────────────
    // EMAIL VALIDATION TESTS
    // ────────────────────────────────────────────────────────

    /**
     * Tests isValidEmail() returns true for a variety of valid email formats.
     */
    #[DataProvider('validEmailProvider')]
    public function testIsValidEmailReturnsTrueForValidEmails(string $email): void
    {
        $this->assertTrue($this->validator->isValidEmail($email));
    }

    /**
     * Provides a list of valid email addresses for testing.
     */
    public static function validEmailProvider(): array
    {
        return [
            ['test@example.com'],
            ['user.name+tag+sorting@example.com'],
            ['email@subdomain.example.com'],
            ['user@mail.co'],
            ['firstname.lastname@domain.com'],
            ['1234567890@domain.com'],
            ['email@domain-one.com'],
            ['_______@domain.com'],
            ['email@domain.name'],
            ['email@domain.superlongtld'],
        ];
    }

    /**
     * Tests isValidEmail() returns false for invalid email formats.
     */
    #[DataProvider('invalidEmailProvider')]
    public function testIsValidEmailReturnsFalseForInvalidEmails(string $email): void
    {
        $this->assertFalse($this->validator->isValidEmail($email));
    }

    /**
     * Provides a list of invalid email addresses for testing.
     */
    public static function invalidEmailProvider(): array
    {
        return [
            ['invalid-email'],
            [''],
            ['plainaddress'],
            ['@missingusername.com'],
            ['email.domain.com'],
            ['email@domain@domain.com'],
            ['email@.domain.com'],
            ['email@domain..com'],
        ];
    }

    // ────────────────────────────────────────────────────────
    // LOGIN VALIDATION TESTS
    // ────────────────────────────────────────────────────────

    /**
     * Tests validateLogin() returns an empty array for valid inputs.
     */
    public function testValidateLoginReturnsNoErrorsForValidInput(): void
    {
        $errors = $this->validator->validateLogin('user@example.com', 'securePass');
        $this->assertEmpty($errors);
    }

    /**
     * Tests error message when email is missing.
     */
    public function testValidateLoginReturnsErrorWhenEmailIsEmpty(): void
    {
        $errors = $this->validator->validateLogin('', 'securePass');
        $this->assertContains('Email is required.', $errors);
    }

    /**
     * Tests error message for an invalid email format.
     */
    public function testValidateLoginReturnsErrorWhenEmailIsInvalid(): void
    {
        $errors = $this->validator->validateLogin('invalid-email', 'securePass');
        $this->assertContains('Please enter a valid email address.', $errors);
    }

    /**
     * Tests error message when password is missing.
     */
    public function testValidateLoginReturnsErrorWhenPasswordIsEmpty(): void
    {
        $errors = $this->validator->validateLogin('user@example.com', '');
        $this->assertContains('Password is required.', $errors);
    }

    /**
     * Tests error for password that is too short.
     */
    public function testValidateLoginReturnsErrorWhenPasswordIsTooShort(): void
    {
        $errors = $this->validator->validateLogin('user@example.com', '123');
        $this->assertContains('Password must be at least 6 characters long.', $errors);
    }

    /**
     * Tests that multiple errors are returned when both inputs are invalid.
     */
    public function testValidateLoginReturnsMultipleErrors(): void
    {
        $errors = $this->validator->validateLogin('', '');
        $this->assertCount(2, $errors);
        $this->assertContains('Email is required.', $errors);
        $this->assertContains('Password is required.', $errors);
    }

    // ────────────────────────────────────────────────────────
    // CSRF TOKEN VALIDATION TESTS
    // ────────────────────────────────────────────────────────

    /**
     * Tests CSRF token validation passes when tokens match.
     */
    public function testValidateCsrfTokenReturnsTrueForMatchingTokens(): void
    {
        $this->assertTrue($this->validator->validateCsrfToken('abc123', 'abc123'));
    }

    /**
     * Tests CSRF token validation fails when tokens do not match.
     */
    public function testValidateCsrfTokenReturnsFalseForNonMatchingTokens(): void
    {
        $this->assertFalse($this->validator->validateCsrfToken('abc123', 'xyz789'));
    }

    // ────────────────────────────────────────────────────────
    // BASIC isValidEmail() & isValidPassword() TESTS
    // ────────────────────────────────────────────────────────

    /**
     * Tests valid email case directly.
     */
    public function testIsValidEmailReturnsTrueForValidEmail(): void
    {
        $this->assertTrue($this->validator->isValidEmail('test@example.com'));
    }

    /**
     * Tests invalid email case directly.
     */
    public function testIsValidEmailReturnsFalseForInvalidEmail(): void
    {
        $this->assertFalse($this->validator->isValidEmail('invalid-email'));
    }

    /**
     * Tests empty email string is invalid.
     */
    public function testIsValidEmailReturnsFalseForEmptyString(): void
    {
        $this->assertFalse($this->validator->isValidEmail(''));
    }

    /**
     * Tests password validation for sufficient length.
     */
    public function testIsValidPasswordReturnsTrueForSufficientLength(): void
    {
        $this->assertTrue($this->validator->isValidPassword('123456'));
    }

    /**
     * Tests password validation fails for too short input.
     */
    public function testIsValidPasswordReturnsFalseForShortPassword(): void
    {
        $this->assertFalse($this->validator->isValidPassword('123'));
    }

    /**
     * Tests password validation fails for empty string.
     */
    public function testIsValidPasswordReturnsFalseForEmptyPassword(): void
    {
        $this->assertFalse($this->validator->isValidPassword(''));
    }

    // TODO: Add edge case tests for maximum email length, password complexity rules, and internationalized email support
}