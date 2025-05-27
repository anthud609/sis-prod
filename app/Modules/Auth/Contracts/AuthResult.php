<?php

namespace App\Modules\Auth\Contracts;

class AuthResult
{
    public const SUCCESS = 'success';
    public const INVALID_CREDENTIALS = 'invalid_credentials';
    public const USER_NOT_FOUND = 'user_not_found';
    public const INACTIVE_USER = 'inactive_user';
    public const DELETED_USER = 'deleted_user';
    public const LOCKED_USER = 'locked_user';
    public const RATE_LIMITED = 'rate_limited';
    public const UNKNOWN_STATUS = 'unknown_status';

    private string $status;
    private ?array $user;
    private string $message;

    public function __construct(string $status, ?array $user = null, string $message = '')
    {
        $this->status = $status;
        $this->user = $user;
        $this->message = $message;
    }

    public function isSuccess(): bool
    {
        return $this->status === self::SUCCESS;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getUser(): ?array
    {
        return $this->user;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public static function success(array $user): self
    {
        return new self(self::SUCCESS, $user, 'Authentication successful');
    }

    public static function invalidCredentials(): self
    {
        return new self(self::INVALID_CREDENTIALS, null, 'Invalid email or password');
    }

    public static function userNotFound(): self
    {
        return new self(self::USER_NOT_FOUND, null, 'Invalid email or password');
    }

    public static function inactiveUser(): self
    {
        return new self(self::INACTIVE_USER, null, 'Your account is inactive. Please contact support.');
    }

    public static function deletedUser(): self
    {
        return new self(self::DELETED_USER, null, 'This account has been deleted');
    }

    public static function lockedUser(): self
    {
        return new self(self::LOCKED_USER, null, 'Your account is locked due to too many failed login attempts');
    }

    public static function rateLimited(): self
    {
        return new self(self::RATE_LIMITED, null, 'Too many failed attempts. Please try again later.');
    }

    public static function unknownStatus(string $status): self
    {
        return new self(self::UNKNOWN_STATUS, null, "Your account status ({$status}) is not recognized. Please contact support.");
    }
}