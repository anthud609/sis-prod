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

    public function __construct(UserRepositoryInterface $users, Request $request) {
        $this->users = $users;
        $this->request = $request;
    }

public function redirectToProperPage(): Response {
    $response = new Response();

    if (isset($_SESSION['user_id'])) {
        return $response->redirect('/dashboard');
    }

    return $response->redirect('/login');
}


    public function showLoginForm()
    {
        require __DIR__ . '/../Views/login.php';
    }

public function handleLogin(): Response {
    $email = trim($this->request->post('email', ''));
    $password = trim($this->request->post('password', ''));

    $user = $this->users->findByEmail($email);
    $response = new Response();

    if (!$user || !password_verify($password, $user['password'])) {
        $response->body = "Invalid credentials.";
        return $response;
    }

    switch ($user['status']) {
        case 'active':
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            return $response->redirect('/dashboard');

        case 'inactive':
            $response->body = "Your account is inactive. Please contact support.";
            break;

        case 'deleted':
            $response->body = "This account has been deleted.";
            break;

        case 'locked':
            $response->body = "Your account is locked due to too many failed login attempts.";
            break;

        default:
            $response->body = "Your account status is not recognized. Please contact support.";
    }

    return $response;
}

public function dashboard(): Response {
    $response = new Response();

    if (!isset($_SESSION['user_id'])) {
        return $response->redirect('/login');
    }

    $currentUser = $this->users->findById($_SESSION['user_id']);
    if (!$currentUser) {
        $response->body = "User not found.";
        return $response;
    }

    ob_start();
    require __DIR__ . '/../Views/dashboard.php';
    $response->body = ob_get_clean();
    return $response;
}



}
