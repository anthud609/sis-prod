<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIS Application</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <div class="text-center mb-8">
                <div class="mx-auto w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-user-lock text-white text-xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900">Welcome Back</h2>
                <p class="text-gray-600 mt-1">Please sign in to your account</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <ul class="text-sm text-red-700">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" action="/login" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="email">
                        <i class="fas fa-envelope mr-1"></i>
                        Email Address
                    </label>
                    <input 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200" 
                        type="email" 
                        id="email" 
                        name="email" 
                        value=""
                        required 
                        autocomplete="email"
                        placeholder="Enter your email address"
                    >
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2" for="password">
                        <i class="fas fa-lock mr-1"></i>
                        Password
                    </label>
                    <div class="relative">
                        <input 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 pr-12" 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            autocomplete="current-password"
                            placeholder="Enter your password"
                        >
                        <button 
                            type="button" 
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600"
                            onclick="togglePassword()"
                            tabindex="-1"
                        >
                            <i id="passwordToggleIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                    <a href="/forgot-password" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">
                        Forgot password?
                    </a>
                </div>

                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200 font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    id="submitBtn"
                >
                    <span id="submitText">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Sign In
                    </span>
                    <span id="loadingText" class="hidden">
                        <i class="fas fa-spinner fa-spin mr-2"></i>
                        Signing In...
                    </span>
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Don't have an account? 
                    <a href="/register" class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                        Sign up here
                    </a>
                </p>
            </div>
        </div>

        <div class="text-center mt-8 text-sm text-gray-500">
            <p>&copy; 2025 SIS Application. All rights reserved.</p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordField = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggleIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.className = 'fas fa-eye-slash';
            } else {
                passwordField.type = 'password';
                toggleIcon.className = 'fas fa-eye';
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function() {
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const loadingText = document.getElementById('loadingText');
            
            submitBtn.disabled = true;
            submitText.classList.add('hidden');
            loadingText.classList.remove('hidden');
        });

        // Auto-focus email field on page load
        document.addEventListener('DOMContentLoaded', function() {
            const emailField = document.getElementById('email');
            if (!emailField.value) {
                emailField.focus();
            } else {
                document.getElementById('password').focus();
            }
        });
    </script>
</body>
</html>