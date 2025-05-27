<?php

namespace App\Modules\Auth\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Core\Logger;
use App\Modules\Auth\Contracts\{
    AuthServiceInterface,
    ValidatorInterface,
    SessionManagerInterface
};

class LoginController
{
    private AuthServiceInterface $authService;
    private ValidatorInterface $validator;
    private SessionManagerInterface $sessionManager;
    private Request $request;

    public function __construct(
        AuthServiceInterface $authService,
        ValidatorInterface $validator,
        SessionManagerInterface $sessionManager,
        Request $request
    ) {
        $this->authService = $authService;
        $this->validator = $validator;
        $this->sessionManager = $sessionManager;
        $this->request = $request;
        Logger::debug('LoginController instantiated');
    }

    public function redirectToProperPage(): Response 
    {
        Logger::debug('redirectToProperPage called');
        
        $response = new Response();

        if ($this->authService->isAuthenticated()) {
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
        if (!$this->sessionManager->has('csrf_token')) {
            $this->sessionManager->set('csrf_token', bin2hex(random_bytes(32)));
            Logger::debug('Generated new CSRF token');
        }
        $csrfToken = $this->sessionManager->get('csrf_token');
        
        // Get any login errors from session
        $errors = [];
        if ($this->sessionManager->has('login_error')) {
            $errors[] = $this->sessionManager->get('login_error');
            Logger::debug('Login error found: ' . $this->sessionManager->get('login_error'));
            $this->sessionManager->remove('login_error');
        }

        Logger::debug('Rendering login form');
        require __DIR__ . '/../Views/login.php';
    }

    public function handleLogin(): Response 
    {
        Logger::debug('handleLogin called');
        Logger::debug('POST data: ' . print_r($_POST, true));
        
        $response = new Response();
        
        // Get input values
        $email = trim($this->request->post('email', ''));
        $password = trim($this->request->post('password', ''));
        
        Logger::debug('Login attempt for email: ' . $email);

        // Validate CSRF token first
        $submittedToken = $this->request->post('csrf_token', '');
        $sessionToken = $this->sessionManager->get('csrf_token', '');
        
        if (!$this->validator->validateCsrfToken($submittedToken, $sessionToken)) {
            Logger::error('CSRF token mismatch');
            $this->sessionManager->set('login_error', "Invalid request. Please try again.");
            return $response->redirect('/login');
        }

        // Validate input
        $validationErrors = $this->validator->validateLogin($email, $password);
        if (!empty($validationErrors)) {
            Logger::debug('Validation errors: ' . implode(', ', $validationErrors));
            $this->sessionManager->set('login_error', implode(' ', $validationErrors));
            return $response->redirect('/login');
        }

        // Attempt authentication
        Logger::debug('Attempting authentication...');
        $authResult = $this->authService->authenticate($email, $password);
        
        Logger::debug('Authentication result: ' . $authResult->getStatus());

        if ($authResult->isSuccess()) {
            Logger::debug('Authentication successful, logging in user');
            $this->authService->login($authResult->getUser());
            Logger::debug('Redirecting to dashboard');
            return $response->redirect('/dashboard');
        }

        // Authentication failed
        Logger::debug('Authentication failed: ' . $authResult->getMessage());
        $this->sessionManager->set('login_error', $authResult->getMessage());
        return $response->redirect('/login');
    }

    public function dashboard(): Response 
    {
        Logger::debug('dashboard called');
        
        $response = new Response();

        if (!$this->authService->isAuthenticated()) {
            Logger::debug('User not authenticated, redirecting to login');
            $this->sessionManager->set('login_error', "Please log in to access the dashboard.");
            return $response->redirect('/login');
        }

        $currentUser = $this->authService->getCurrentUser();
        if (!$currentUser) {
            Logger::error('Authenticated user not found in database');
            $this->authService->logout();
            $this->sessionManager->set('login_error', "Invalid email or password. Please log in again.");
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
        
        $this->authService->logout();
        
        Logger::debug('User logged out');
        
        $response = new Response();
        return $response->redirect('/login');
    }
}