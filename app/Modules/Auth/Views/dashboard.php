<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Dashboard</title>
 <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  
</head>
<body class="bg-gray-100 text-gray-800 font-sans p-8">

    <div class="max-w-4xl mx-auto space-y-10">
        <h1 class="text-3xl font-bold mb-6">
            <i class="fas fa-user-circle text-blue-500"></i>
            Welcome, <?= htmlspecialchars($currentUser['email']) ?>
        </h1>

        <!-- Account Details -->
        <div class="bg-white p-6 rounded-2xl shadow-md">
            <h2 class="text-xl font-semibold mb-2 flex items-center gap-2 text-blue-600">
                <i class="fas fa-database"></i>
                Your Account Details
            </h2>
            <p class="text-sm text-gray-500 mb-4">
                This section shows <strong>every column</strong> from the `users` table in the database.
            </p>
            <table class="table-auto w-full border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-2 font-medium text-sm text-gray-600">Field</th>
                        <th class="px-4 py-2 font-medium text-sm text-gray-600">Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($currentUser as $key => $value): ?>
                        <tr class="border-t border-gray-200 hover:bg-gray-50">
                            <td class="px-4 py-2"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) ?></td>
                            <td class="px-4 py-2"><?= htmlspecialchars($value) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Session Data -->
        <div class="bg-white p-6 rounded-2xl shadow-md">
            <h2 class="text-xl font-semibold mb-2 flex items-center gap-2 text-green-600">
                <i class="fas fa-user-lock"></i>
                Your Session Data
            </h2>
            <p class="text-sm text-gray-500 mb-4">
                This section shows all <strong>session key/value pairs</strong> currently stored.
            </p>
            <table class="table-auto w-full border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-100 text-left">
                    <tr>
                        <th class="px-4 py-2 font-medium text-sm text-gray-600">Key</th>
                        <th class="px-4 py-2 font-medium text-sm text-gray-600">Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($_SESSION as $key => $value): ?>
                        <tr class="border-t border-gray-200 hover:bg-gray-50">
                            <td class="px-4 py-2"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $key))) ?></td>
                            <td class="px-4 py-2">
                                <?php
                                if (is_array($value) || is_object($value)) {
                                    echo '<pre class="bg-gray-100 p-2 rounded text-xs text-gray-700 whitespace-pre-wrap">' . htmlspecialchars(print_r($value, true)) . '</pre>';
                                } else {
                                    echo htmlspecialchars($value);
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php echo "<!-- DEBUG: About to include overlay -->"; ?>
<?php if (($_ENV['APP_ENV'] ?? 'production') === 'dev'): ?>
    <?php echo "<!-- DEBUG: APP_ENV is dev, including overlay -->"; ?>
    <?php require __DIR__ . '/../../../../devtools/overlay.php'; ?>
    <?php echo "<!-- DEBUG: Overlay included -->"; ?>
<?php else: ?>
    <?php echo "<!-- DEBUG: APP_ENV is not dev, APP_ENV = " . ($_ENV['APP_ENV'] ?? 'not set') . " -->"; ?>
<?php endif; ?>
</body>
</html>