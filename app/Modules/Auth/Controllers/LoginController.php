<?php

namespace App\Modules\Auth\Controllers;

use App\Modules\Auth\Models\User;

class LoginController
{
    public function redirectToProperPage()
{
    if (isset($_SESSION['user_id'])) {
        header('Location: /dashboard');
    } else {
        header('Location: /login');
    }
    exit;
}

    public function showLoginForm()
    {
        require __DIR__ . '/../Views/login.php';
    }

   public function handleLogin()
{
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $userModel = new User();
    $user = $userModel->findByEmail($email);

    if (!$user) {
        echo "Invalid credentials.";
        return;
    }

    // Check password
    if (!password_verify($password, $user['password'])) {
        echo "Invalid credentials.";
        return;
    }

    // Status-based restrictions
    switch ($user['status']) {
        case 'active':
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            header('Location: /dashboard');
            exit;

        case 'inactive':
            echo "Your account is inactive. Please contact support.";
            break;

        case 'deleted':
            echo "This account has been deleted.";
            break;

        case 'locked':
            echo "Your account is locked due to too many failed login attempts.";
            break;

        default:
            echo "Your account status is not recognized. Please contact support.";
            break;
    }
}


    public function dashboard()
    {
        echo "Welcome to the dashboard, " . htmlspecialchars($_SESSION['email'] ?? 'Guest');
    }
}
