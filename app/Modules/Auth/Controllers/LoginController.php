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
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            header('Location: /dashboard');
        } else {
            echo "Invalid credentials";
        }
    }

    public function dashboard()
    {
        echo "Welcome to the dashboard, " . htmlspecialchars($_SESSION['email'] ?? 'Guest');
    }
}
