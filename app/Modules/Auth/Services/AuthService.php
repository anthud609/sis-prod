<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Contracts\{
    AuthServiceInterface,
    AuthResult,
    UserRepositoryInterface,
    SessionManagerInterface
};

class AuthService implements AuthServiceInterface
{
    private UserRepositoryInterface $userRepository;
    private SessionManagerInterface $sessionManager;
    private int $maxAttempts;
    private int $lockoutTime;

    public function __construct(
        UserRepositoryInterface $userRepository,
        SessionManagerInterface $sessionManager,
        int $maxAttempts = 5,
        int $lockoutTime = 900 // 15 minutes
    ) {
        $this->userRepository = $userRepository;
        $this->sessionManager = $sessionManager;
        $this->maxAttempts = $maxAttempts;
        $this->lockoutTime = $lockoutTime;
    }

    public function authenticate(string $email, string $password): AuthResult
    {
        // Check rate limiting first
        if ($this->isRateLimited($email)) {
            return AuthResult::rateLimited();
        }

        // Find user
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            $this->recordFailedAttempt($email);
            return AuthResult::userNotFound();
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            $this->recordFailedAttempt($email);
            return AuthResult::invalidCredentials();
        }

        // Clear failed attempts on successful authentication
        $this->clearFailedAttempts($email);

        // Check user status
        switch ($user['status']) {
            case 'active':
                return AuthResult::success($user);
            case 'inactive':
                return AuthResult::inactiveUser();
            case 'deleted':
                return AuthResult::deletedUser();
            case 'locked':
                return AuthResult::lockedUser();
            default:
                return AuthResult::unknownStatus($user['status']);
        }
    }

    public function login(array $user): void
    {
        $this->sessionManager->regenerateId(true);
        $this->sessionManager->set('user_id', $user['id']);
        $this->sessionManager->set('email', $user['email']);
    }

    public function logout(): void
    {
        $this->sessionManager->clear();
        $this->sessionManager->destroy();
        
        // Start a new session for potential flash messages
        session_start();
        $this->sessionManager->regenerateId(true);
    }

    public function isAuthenticated(): bool
    {
        return $this->sessionManager->has('user_id');
    }

    public function getCurrentUser(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        $userId = $this->sessionManager->get('user_id');
        return $this->userRepository->findById($userId);
    }

    public function isRateLimited(string $email): bool
    {
        $key = "login_attempts_" . md5($email);
        $attempts = $this->sessionManager->get($key, 0);
        $lockoutTime = $this->sessionManager->get($key . '_lockout', 0);
        
        if ($lockoutTime > time()) {
            return true; // Still locked out
        }
        
        if ($attempts >= $this->maxAttempts) {
            $this->sessionManager->set($key . '_lockout', time() + $this->lockoutTime);
            return true;
        }
        
        return false;
    }

    public function recordFailedAttempt(string $email): void
    {
        $key = "login_attempts_" . md5($email);
        $attempts = $this->sessionManager->get($key, 0) + 1;
        $this->sessionManager->set($key, $attempts);
    }

    public function clearFailedAttempts(string $email): void
    {
        $key = "login_attempts_" . md5($email);
        $this->sessionManager->remove($key);
        $this->sessionManager->remove($key . '_lockout');
    }
}