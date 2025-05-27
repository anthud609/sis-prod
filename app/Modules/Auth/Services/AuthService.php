<?php

namespace App\Modules\Auth\Services;

use App\Modules\Auth\Contracts\{
    AuthServiceInterface,
    AuthResult,
    UserRepositoryInterface,
    SessionManagerInterface
};

/**
 * Class AuthService
 * 
 * This service handles the core authentication logic, including user login,
 * logout, rate limiting for failed login attempts, and session management.
 * 
 * It relies on injected dependencies for user data retrieval and session management,
 * allowing for clean separation of concerns and easier testing.
 * 
 * Implements AuthServiceInterface to standardize authentication methods.
 * 
 * @package App\Modules\Auth\Services
 */
class AuthService implements AuthServiceInterface
{
    /**
     * @var UserRepositoryInterface Interface to retrieve user data from data source.
     */
    private UserRepositoryInterface $userRepository;

    /**
     * @var SessionManagerInterface Interface to manage session data (e.g., PHP sessions).
     */
    private SessionManagerInterface $sessionManager;

    /**
     * @var int Maximum allowed failed login attempts before lockout.
     */
    private int $maxAttempts;

    /**
     * @var int Lockout duration in seconds (default 900 seconds = 15 minutes).
     */
    private int $lockoutTime;

    /**
     * Constructor to initialize dependencies and settings.
     * 
     * @param UserRepositoryInterface $userRepository User data source abstraction.
     * @param SessionManagerInterface $sessionManager Session management abstraction.
     * @param int $maxAttempts Maximum allowed failed login attempts (default 5).
     * @param int $lockoutTime Lockout time in seconds after max attempts exceeded (default 900s).
     */
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

    /**
     * Authenticate a user by email and password.
     * 
     * This method enforces rate limiting for security, verifies user existence,
     * checks password validity, and inspects the user's account status.
     * It returns an AuthResult object describing the outcome.
     * 
     * @param string $email User's email address attempting login.
     * @param string $password User's submitted password.
     * 
     * @return AuthResult Object representing the result of authentication attempt.
     */
    public function authenticate(string $email, string $password): AuthResult
    {
        // Check if user is currently rate limited due to too many failed attempts
        if ($this->isRateLimited($email)) {
            return AuthResult::rateLimited();
        }

        // Attempt to retrieve the user record by email
        $user = $this->userRepository->findByEmail($email);

        // If user not found, record failed login attempt and return failure result
        if (!$user) {
            $this->recordFailedAttempt($email);
            return AuthResult::userNotFound();
        }

        // Verify the provided password against the stored hashed password
        if (!password_verify($password, $user['password'])) {
            $this->recordFailedAttempt($email);
            return AuthResult::invalidCredentials();
        }

        // On successful authentication, reset failed login attempts
        $this->clearFailedAttempts($email);

        // Check user's status and return the appropriate AuthResult
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
                // Handles unexpected status values gracefully
                return AuthResult::unknownStatus($user['status']);
        }
    }

    /**
     * Log in a user by storing their details in the session.
     * 
     * Regenerates the session ID for security (prevents session fixation),
     * then stores user ID and email in session storage.
     * 
     * @param array $user User data array, expected to contain 'id' and 'email' keys.
     */
    public function login(array $user): void
    {
        $this->sessionManager->regenerateId(true);
        $this->sessionManager->set('user_id', $user['id']);
        $this->sessionManager->set('email', $user['email']);
    }

    /**
     * Log out the current user by clearing and destroying the session.
     * 
     * After destroying, a new session is started and session ID regenerated
     * to allow for new sessions such as flash messages.
     */
    public function logout(): void
    {
        $this->sessionManager->clear();
        $this->sessionManager->destroy();

        // Start a fresh session after logout
        session_start();
        $this->sessionManager->regenerateId(true);
    }

    /**
     * Check if a user is currently authenticated.
     * 
     * This checks if the session contains a 'user_id' key.
     * 
     * @return bool True if user is logged in, false otherwise.
     */
    public function isAuthenticated(): bool
    {
        return $this->sessionManager->has('user_id');
    }

    /**
     * Retrieve the currently authenticated user's full data.
     * 
     * Returns null if no user is logged in.
     * 
     * @return array|null User data array or null if unauthenticated.
     */
    public function getCurrentUser(): ?array
    {
        if (!$this->isAuthenticated()) {
            return null;
        }

        $userId = $this->sessionManager->get('user_id');
        return $this->userRepository->findById($userId);
    }

    /**
     * Determine if login attempts for an email are currently rate limited.
     * 
     * Rate limiting is based on the number of failed attempts and a lockout duration.
     * If the user exceeds the max allowed attempts, they are locked out for $lockoutTime seconds.
     * 
     * @param string $email Email address to check rate limit status for.
     * 
     * @return bool True if rate limited (locked out), false otherwise.
     */
    public function isRateLimited(string $email): bool
    {
        // Generate a unique session key for tracking attempts per email
        $key = "login_attempts_" . md5($email);

        // Get current number of failed attempts (default 0)
        $attempts = $this->sessionManager->get($key, 0);

        // Get lockout expiration timestamp (default 0)
        $lockoutTime = $this->sessionManager->get($key . '_lockout', 0);

        // If current time is before lockout expiry, user is still locked out
        if ($lockoutTime > time()) {
            return true;
        }

        // If max attempts exceeded, set lockout timestamp and return true
        if ($attempts >= $this->maxAttempts) {
            $this->sessionManager->set($key . '_lockout', time() + $this->lockoutTime);
            return true;
        }

        // Otherwise, user is not rate limited
        return false;
    }

    /**
     * Record a failed login attempt for an email address.
     * 
     * Increments the count of failed attempts stored in the session.
     * 
     * @param string $email Email address for which to record the failed attempt.
     */
    public function recordFailedAttempt(string $email): void
    {
        $key = "login_attempts_" . md5($email);
        $attempts = $this->sessionManager->get($key, 0) + 1;
        $this->sessionManager->set($key, $attempts);
    }

    /**
     * Clear failed login attempts and lockout state for an email.
     * 
     * This should be called after a successful login to reset counters.
     * 
     * @param string $email Email address for which to clear failed attempt data.
     */
    public function clearFailedAttempts(string $email): void
    {
        $key = "login_attempts_" . md5($email);
        $this->sessionManager->remove($key);
        $this->sessionManager->remove($key . '_lockout');
    }
}
