<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Models\User;
use App\Modules\Auth\Contracts\UserRepositoryInterface;
use App\Core\Request;
use App\Core\Response;

class LoginController
{
    private UserRepositoryInterface $users;
    private Request $request;

    public function __construct(UserRepositoryInterface $users, Request $request) 
    {
        $this->users = $users;
        $this->request = $request;
    }

    public function redirectToProperPage(): Response 
    {
        $response = new Response();

        if (isset($_SESSION['user_id'])) {
            return $response->redirect('/dashboard');
        }

        return $response->redirect('/login');
    }

    public function showLoginForm()
    {
        // Generate CSRF token if not exists
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $csrfToken = $_SESSION['csrf_token'];
        
        // Get any login errors from session
        $errors = [];
        if (isset($_SESSION['login_error'])) {
            $errors[] = $_SESSION['login_error'];
            unset($_SESSION['login_error']); // Clear the error after displaying
        }

        require __DIR__ . '/../Views/login.php';
    }

    public function handleLogin(): Response 
    {
        $response = new Response();
        
        // Get input values
        $email = trim($this->request->post('email', ''));
        $password = trim($this->request->post('password', ''));

        // Validate CSRF token first
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $this->request->post('csrf_token', ''))) {
            $_SESSION['login_error'] = "Invalid request. Please try again.";
            return $response->redirect('/login');
        }

        // Validate input
        $validationErrors = $this->validateLoginInput($email, $password);
        if (!empty($validationErrors)) {
            $_SESSION['login_error'] = implode(' ', $validationErrors);
            return $response->redirect('/login');
        }

        // Check rate limiting
        if (!$this->checkRateLimit($email)) {
            $_SESSION['login_error'] = "Too many failed attempts. Please try again later.";
            return $response->redirect('/login');
        }

        // Find user and verify credentials
        $user = $this->users->findByEmail($email);
        
        if (!$user || !password_verify($password, $user['password'])) {
            $this->recordFailedAttempt($email);
            $_SESSION['login_error'] = "Invalid email or password.";
            return $response->redirect('/login');
        }

        // Clear failed attempts on successful authentication
        $this->clearFailedAttempts($email);

        // Check user status
        switch ($user['status']) {
            case 'active':
                // Regenerate session ID for security
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                return $response->redirect('/dashboard');

            case 'inactive':
                $_SESSION['login_error'] = "Your account is inactive. Please contact support.";
                break;

            case 'deleted':
                $_SESSION['login_error'] = "This account has been deleted.";
                break;

            case 'locked':
                $_SESSION['login_error'] = "Your account is locked due to too many failed login attempts.";
                break;

            default:
                $_SESSION['login_error'] = "Your account status is not recognized. Please contact support.";
        }

        return $response->redirect('/login');
    }

    public function dashboard(): Response 
    {
        $response = new Response();

        if (!isset($_SESSION['user_id'])) {
            return $response->redirect('/login');
        }

        $currentUser = $this->users->findById($_SESSION['user_id']);
        if (!$currentUser) {
            $_SESSION['login_error'] = "User not found.";
            return $response->redirect('/login');
        }

        ob_start();
        require __DIR__ . '/../Views/dashboard.php';
        $response->body = ob_get_clean();
        return $response;
    }

    public function logout(): Response 
    {
        session_destroy();
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