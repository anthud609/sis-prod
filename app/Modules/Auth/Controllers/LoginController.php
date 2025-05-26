<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Auth\Contracts\UserRepositoryInterface;
use App\Core\Request;
use App\Core\Response;
use App\Core\Logger;

class LoginController
{
    private UserRepositoryInterface $users;
    private Request $request;

    public function __construct(UserRepositoryInterface $users, Request $request) 
    {
        $this->users = $users;
        $this->request = $request;
        Logger::debug('LoginController instantiated');
    }

    public function redirectToProperPage(): Response 
    {
        Logger::debug('redirectToProperPage called');
        Logger::debug('Session user_id: ' . ($_SESSION['user_id'] ?? 'NOT SET'));
        
        $response = new Response();

        if (isset($_SESSION['user_id'])) {
            Logger::debug('User is logged in, redirecting to dashboard');
            return $response->redirect('/dashboard');
        }

        Logger::debug('User not logged in, redirecting to login');
        return $response->redirect('/login');
    }

    public function showLoginForm()
    {
        Logger::debug('showLoginForm called');
        
        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            Logger::debug('Generated new CSRF token');
        }
        $csrfToken = $_SESSION['csrf_token'];
        
        // Get any login errors from session
        $errors = [];
        if (isset($_SESSION['login_error'])) {
            $errors[] = $_SESSION['login_error'];
            Logger::debug('Login error found: ' . $_SESSION['login_error']);
            unset($_SESSION['login_error']); // Clear the error after displaying
        }

        Logger::debug('Rendering login form');
        require __DIR__ . '/../Views/login.php';
    }

    public function handleLogin(): Response 
    {
        Logger::debug('handleLogin called');
        Logger::debug('POST data: ' . print_r($_POST, true));
        Logger::debug('Session before login: ' . print_r($_SESSION, true));
        
        $response = new Response();
        
        // Get input values
        $email = trim($this->request->post('email', ''));
        $password = trim($this->request->post('password', ''));
        
        Logger::debug('Login attempt for email: ' . $email);

        // Validate CSRF token first
        $submittedToken = $this->request->post('csrf_token', '');
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        
        Logger::debug('CSRF check - Submitted: ' . substr($submittedToken, 0, 10) . '...');
        Logger::debug('CSRF check - Session: ' . substr($sessionToken, 0, 10) . '...');
        
        if (!hash_equals($sessionToken, $submittedToken)) {
            Logger::error('CSRF token mismatch');
            $_SESSION['login_error'] = "Invalid request. Please try again.";
            return $response->redirect('/login');
        }

        // Validate input
        $validationErrors = $this->validateLoginInput($email, $password);
        if (!empty($validationErrors)) {
            Logger::debug('Validation errors: ' . implode(', ', $validationErrors));
            $_SESSION['login_error'] = implode(' ', $validationErrors);
            return $response->redirect('/login');
        }

        // Check rate limiting
        if (!$this->checkRateLimit($email)) {
            Logger::debug('Rate limit exceeded for: ' . $email);
            $_SESSION['login_error'] = "Too many failed attempts. Please try again later.";
            return $response->redirect('/login');
        }

        // Find user and verify credentials
        Logger::debug('Looking up user in database...');
        $user = $this->users->findByEmail($email);
        
        if (!$user) {
            Logger::debug('User not found in database');
            $this->recordFailedAttempt($email);
            $_SESSION['login_error'] = "Invalid email or password.";
            return $response->redirect('/login');
        }
        
        Logger::debug('User found: ' . print_r($user, true));
        
        if (!password_verify($password, $user['password'])) {
            Logger::debug('Password verification failed');
            $this->recordFailedAttempt($email);
            $_SESSION['login_error'] = "Invalid email or password.";
            return $response->redirect('/login');
        }
        
        Logger::debug('Password verification successful');

        // Clear failed attempts on successful authentication
        $this->clearFailedAttempts($email);

        // Check user status
        Logger::debug('User status: ' . $user['status']);
        
        switch ($user['status']) {
            case 'active':
                Logger::debug('User is active, proceeding with login');
                
                // Regenerate session ID for security
                $oldSessionId = session_id();
                session_regenerate_id(true);
                $newSessionId = session_id();
                
                Logger::debug('Session regenerated from ' . $oldSessionId . ' to ' . $newSessionId);
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                
                Logger::debug('Session variables set:');
                Logger::debug('user_id: ' . $_SESSION['user_id']);
                Logger::debug('email: ' . $_SESSION['email']);
                Logger::debug('Full session after login: ' . print_r($_SESSION, true));
                
                Logger::debug('Redirecting to dashboard');
                return $response->redirect('/dashboard');

            case 'inactive':
                Logger::debug('User account is inactive');
                $_SESSION['login_error'] = "Your account is inactive. Please contact support.";
                break;

            case 'deleted':
                Logger::debug('User account is deleted');
                $_SESSION['login_error'] = "This account has been deleted.";
                break;

            case 'locked':
                Logger::debug('User account is locked');
                $_SESSION['login_error'] = "Your account is locked due to too many failed login attempts.";
                break;

            default:
                Logger::debug('Unknown user status: ' . $user['status']);
                $_SESSION['login_error'] = "Your account status is not recognized. Please contact support.";
        }

        return $response->redirect('/login');
    }

    public function dashboard(): Response 
    {
        Logger::debug('dashboard called');
        Logger::debug('Session at dashboard: ' . print_r($_SESSION, true));
        
        $response = new Response();

        if (!isset($_SESSION['user_id'])) {
            Logger::debug('No user_id in session, redirecting to login');
            $_SESSION['login_error'] = "Please log in to access the dashboard.";
            return $response->redirect('/login');
        }

        Logger::debug('Looking up user by ID: ' . $_SESSION['user_id']);
        $currentUser = $this->users->findById($_SESSION['user_id']);
        
        if (!$currentUser) {
            Logger::error('User not found by ID: ' . $_SESSION['user_id']);
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['login_error'] = "User not found. Please log in again.";
            return $response->redirect('/login');
        }

        Logger::debug('User found for dashboard: ' . print_r($currentUser, true));
        Logger::debug('Rendering dashboard');

        ob_start();
        require __DIR__ . '/../Views/dashboard.php';
        $response->body = ob_get_clean();
        return $response;
    }

    public function logout(): Response 
    {
        Logger::debug('logout called');
        Logger::debug('Session before logout: ' . print_r($_SESSION, true));
        
        session_unset();
        session_destroy();
        
        // Start a new session for potential flash messages
        session_start();
        session_regenerate_id(true);
        
        Logger::debug('Session after logout: ' . print_r($_SESSION, true));
        
        $response = new Response();
        return $response->redirect('/login');
    }

    private function validateLoginInput(string $email, string $password): array 
    {
        $errors = [];
        
        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }
        
        if (empty($password)) {
            $errors[] = "Password is required.";
        }
        
        return $errors;
    }

    private function checkRateLimit(string $email): bool 
    {
        $key = "login_attempts_" . md5($email);
        $attempts = $_SESSION[$key] ?? 0;
        $lockoutTime = $_SESSION[$key . '_lockout'] ?? 0;
        
        if ($lockoutTime > time()) {
            return false; // Still locked out
        }
        
        if ($attempts >= 5) {
            $_SESSION[$key . '_lockout'] = time() + 900; // 15 minute lockout
            return false;
        }
        
        return true;
    }

    private function recordFailedAttempt(string $email): void 
    {
        $key = "login_attempts_" . md5($email);
        $_SESSION[$key] = ($_SESSION[$key] ?? 0) + 1;
    }

    private function clearFailedAttempts(string $email): void 
    {
        $key = "login_attempts_" . md5($email);
        unset($_SESSION[$key], $_SESSION[$key . '_lockout']);
    }
}