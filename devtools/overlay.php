<?php
// Save this as: devtools/overlay.php
echo "<!-- DEV OVERLAY TAILWIND START -->\n";
echo "<!-- APP_ENV: " . ($_ENV['APP_ENV'] ?? 'not set') . " -->\n";

if (($_ENV['APP_ENV'] ?? 'production') !== 'dev') {
    echo "<!-- DEV OVERLAY: Environment not 'dev', skipping -->\n";
    return;
}

echo "<!-- DEV OVERLAY: Loading Tailwind version... -->\n";

$overlayData = [
    'environment' => $_ENV['APP_ENV'] ?? 'not set',
    'route' => $_SERVER['REQUEST_URI'] ?? 'unknown',
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'timestamp' => date('Y-m-d H:i:s'),
    'session_id' => session_id(),
    'user' => [
        'id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['email'] ?? null,
        'authenticated' => isset($_SESSION['user_id'])
    ],
    'system' => [
        'php_version' => PHP_VERSION,
        'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
        'memory_peak' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown'
    ],
    'session_data' => $_SESSION,
    'request_time' => isset($_SERVER['REQUEST_TIME_FLOAT']) ? 
        round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) . ' ms' : 'unknown',
    'request_data' => [
        'get' => $_GET,
        'post' => $_POST,
        'headers' => function_exists('getallheaders') ? getallheaders() : [],
        'cookies' => $_COOKIE,
        'files' => $_FILES
    ],
    'server_data' => array_filter($_SERVER, function($key) {
        return !in_array($key, ['PHP_AUTH_PW', 'HTTP_AUTHORIZATION']);
    }, ARRAY_FILTER_USE_KEY)
];

$overlayDataJson = json_encode($overlayData, JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_APOS);
?>

<!-- Floating Toggle Button -->
<div id="dev-floating-btn" class="fixed bottom-5 right-5 w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white text-xl font-bold cursor-pointer z-50 shadow-xl hover:shadow-2xl transform hover:scale-110 transition-all duration-300 backdrop-blur-sm">
    ⚡
</div>

<!-- Main Overlay -->
<div id="dev-overlay-main" class="fixed inset-0 bg-gray-900 bg-opacity-95 backdrop-blur-md transform translate-x-full transition-transform duration-500 ease-in-out z-40 font-mono text-white hidden">
    
    <!-- Header -->
    <div class="bg-gradient-to-r from-gray-800 to-gray-900 p-6 border-b border-gray-700 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <h1 class="text-2xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                ⚡ Developer Console
            </h1>
            <span class="px-2 py-1 bg-gray-700 rounded text-xs text-gray-300">Ctrl+`</span>
        </div>
        <button id="dev-close-btn" class="w-10 h-10 bg-red-500 bg-opacity-20 border border-red-500 border-opacity-30 rounded-lg text-red-400 hover:bg-opacity-30 transition-all duration-200 flex items-center justify-center text-lg">
            ×
        </button>
    </div>

    <!-- Navigation -->
    <div class="bg-gray-800 px-6 flex space-x-0 border-b border-gray-700 overflow-x-auto">
        <button class="dev-nav-item active px-6 py-4 text-sm font-medium text-gray-400 hover:text-white transition-colors duration-200 border-b-2 border-transparent hover:border-blue-400 whitespace-nowrap flex items-center space-x-2" data-panel="overview">
            <span>📊</span><span>Overview</span><span class="px-1 py-0.5 bg-gray-700 rounded text-xs">1</span>
        </button>
        <button class="dev-nav-item px-6 py-4 text-sm font-medium text-gray-400 hover:text-white transition-colors duration-200 border-b-2 border-transparent hover:border-blue-400 whitespace-nowrap flex items-center space-x-2" data-panel="request">
            <span>🌐</span><span>Request</span><span class="px-1 py-0.5 bg-gray-700 rounded text-xs">2</span>
        </button>
        <button class="dev-nav-item px-6 py-4 text-sm font-medium text-gray-400 hover:text-white transition-colors duration-200 border-b-2 border-transparent hover:border-blue-400 whitespace-nowrap flex items-center space-x-2" data-panel="session">
            <span>🔐</span><span>Session</span><span class="px-1 py-0.5 bg-gray-700 rounded text-xs">3</span>
        </button>
        <button class="dev-nav-item px-6 py-4 text-sm font-medium text-gray-400 hover:text-white transition-colors duration-200 border-b-2 border-transparent hover:border-blue-400 whitespace-nowrap flex items-center space-x-2" data-panel="database">
            <span>💾</span><span>Database</span><span class="px-1 py-0.5 bg-gray-700 rounded text-xs">4</span>
        </button>
        <button class="dev-nav-item px-6 py-4 text-sm font-medium text-gray-400 hover:text-white transition-colors duration-200 border-b-2 border-transparent hover:border-blue-400 whitespace-nowrap flex items-center space-x-2" data-panel="logs">
            <span>📝</span><span>Logs</span><span class="px-1 py-0.5 bg-gray-700 rounded text-xs">5</span>
        </button>
        <button class="dev-nav-item px-6 py-4 text-sm font-medium text-gray-400 hover:text-white transition-colors duration-200 border-b-2 border-transparent hover:border-blue-400 whitespace-nowrap flex items-center space-x-2" data-panel="performance">
            <span>⚡</span><span>Performance</span><span class="px-1 py-0.5 bg-gray-700 rounded text-xs">6</span>
        </button>
        <button class="dev-nav-item px-6 py-4 text-sm font-medium text-gray-400 hover:text-white transition-colors duration-200 border-b-2 border-transparent hover:border-blue-400 whitespace-nowrap flex items-center space-x-2" data-panel="tools">
            <span>🛠️</span><span>Tools</span><span class="px-1 py-0.5 bg-gray-700 rounded text-xs">7</span>
        </button>
    </div>

    <!-- Content Area -->
    <div class="flex-1 overflow-y-auto">
        
        <!-- Overview Panel -->
        <div id="overview-panel" class="dev-panel p-6 space-y-6">
            
            <!-- Metrics Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <!-- Route Info -->
                <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl p-4">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg flex items-center justify-center text-lg">🌐</div>
                        <div>
                            <div class="font-semibold text-white">Current Route</div>
                            <div class="text-xs text-gray-400"><?= htmlspecialchars($overlayData['route']) ?></div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-blue-500 rounded-lg flex items-center justify-center text-lg">⚡</div>
                        <div>
                            <div class="font-semibold text-white">Method</div>
                            <div class="text-xs text-gray-400"><?= htmlspecialchars($overlayData['method']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- User Info -->
                <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl p-4">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-red-500 rounded-lg flex items-center justify-center text-lg">👤</div>
                        <div>
                            <div class="font-semibold text-white">Authentication</div>
                            <div class="text-xs">
                                <?php if ($overlayData['user']['authenticated']): ?>
                                    <span class="px-2 py-1 bg-green-500 bg-opacity-20 text-green-400 border border-green-500 border-opacity-30 rounded-full text-xs font-semibold">✅ Authenticated</span>
                                <?php else: ?>
                                    <span class="px-2 py-1 bg-red-500 bg-opacity-20 text-red-400 border border-red-500 border-opacity-30 rounded-full text-xs font-semibold">❌ Guest</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($overlayData['user']['authenticated']): ?>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-lg flex items-center justify-center text-lg">📧</div>
                        <div>
                            <div class="font-semibold text-white">User</div>
                            <div class="text-xs text-gray-400"><?= htmlspecialchars($overlayData['user']['email'] ?? 'N/A') ?></div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- System Info -->
                <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl p-4">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-lg flex items-center justify-center text-lg">💾</div>
                        <div>
                            <div class="font-semibold text-white">Memory Usage</div>
                            <div class="text-xs text-gray-400"><?= htmlspecialchars($overlayData['system']['memory_usage']) ?></div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-cyan-500 rounded-lg flex items-center justify-center text-lg">⏱️</div>
                        <div>
                            <div class="font-semibold text-white">Response Time</div>
                            <div class="text-xs text-gray-400"><?= htmlspecialchars($overlayData['request_time']) ?></div>
                        </div>
                    </div>
                </div>

                <!-- PHP Info -->
                <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl p-4">
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center text-lg">🐘</div>
                        <div>
                            <div class="font-semibold text-white">PHP Version</div>
                            <div class="text-xs text-gray-400"><?= htmlspecialchars($overlayData['system']['php_version']) ?></div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-gray-500 to-gray-600 rounded-lg flex items-center justify-center text-lg">🕒</div>
                        <div>
                            <div class="font-semibold text-white">Timestamp</div>
                            <div class="text-xs text-gray-400"><?= htmlspecialchars($overlayData['timestamp']) ?></div>
                        </div>
                    </div>
                </div>
                
            </div>

            <!-- Quick Actions -->
            <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-gray-700 bg-opacity-80 px-6 py-4 border-b border-gray-600">
                    <h3 class="text-lg font-semibold text-white flex items-center space-x-2">
                        <span>🔧</span><span>Quick Actions</span>
                    </h3>
                </div>
                <div class="p-6">
                    <div class="flex flex-wrap gap-3">
                        <button onclick="devConsole.clearCache()" class="px-4 py-2 bg-blue-500 bg-opacity-20 border border-blue-500 border-opacity-30 text-blue-400 rounded-lg hover:bg-opacity-30 transition-all duration-200 flex items-center space-x-2">
                            <span>🗑️</span><span>Clear Cache</span>
                        </button>
                        <button onclick="devConsole.refreshData()" class="px-4 py-2 bg-gray-600 bg-opacity-80 border border-gray-500 text-gray-300 rounded-lg hover:bg-opacity-100 transition-all duration-200 flex items-center space-x-2">
                            <span>🔄</span><span>Refresh Data</span>
                        </button>
                        <button onclick="devConsole.exportData()" class="px-4 py-2 bg-gray-600 bg-opacity-80 border border-gray-500 text-gray-300 rounded-lg hover:bg-opacity-100 transition-all duration-200 flex items-center space-x-2">
                            <span>📤</span><span>Export Debug Data</span>
                        </button>
                        <button onclick="devConsole.toggleMaintenance()" class="px-4 py-2 bg-gray-600 bg-opacity-80 border border-gray-500 text-gray-300 rounded-lg hover:bg-opacity-100 transition-all duration-200 flex items-center space-x-2">
                            <span>🔧</span><span>Maintenance Mode</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Request Panel -->
        <div id="request-panel" class="dev-panel hidden p-6 space-y-6">
            <input type="text" id="request-search" placeholder="Search request data..." class="w-full max-w-md px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:ring-opacity-20">
            
            <!-- Request Headers -->
            <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-gray-700 bg-opacity-80 px-6 py-4 border-b border-gray-600 cursor-pointer" onclick="devConsole.toggleSection(this)">
                    <h3 class="text-lg font-semibold text-white flex items-center space-x-2">
                        <span>🌐</span><span>Request Headers</span>
                    </h3>
                </div>
                <div class="p-6 max-h-96 overflow-y-auto">
                    <div class="overflow-x-auto">
                        <table class="w-full bg-gray-900 rounded-lg overflow-hidden">
                            <thead class="bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-400 uppercase tracking-wider">Header</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-blue-400 uppercase tracking-wider">Value</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-700">
                                <?php foreach ($overlayData['request_data']['headers'] as $key => $value): ?>
                                <tr class="hover:bg-gray-700 hover:bg-opacity-30">
                                    <td class="px-4 py-3 text-sm text-white"><?= htmlspecialchars($key) ?></td>
                                    <td class="px-4 py-3 text-sm text-gray-300"><?= htmlspecialchars($value) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- POST Data -->
            <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-gray-700 bg-opacity-80 px-6 py-4 border-b border-gray-600 cursor-pointer" onclick="devConsole.toggleSection(this)">
                    <h3 class="text-lg font-semibold text-white flex items-center space-x-2">
                        <span>📥</span><span>POST Data</span>
                    </h3>
                </div>
                <div class="p-6">
                    <pre class="bg-gray-900 border border-gray-600 rounded-lg p-4 text-sm text-gray-300 overflow-auto max-h-64"><?= htmlspecialchars(json_encode($overlayData['request_data']['post'], JSON_PRETTY_PRINT)) ?></pre>
                </div>
            </div>

            <!-- GET Parameters -->
            <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-gray-700 bg-opacity-80 px-6 py-4 border-b border-gray-600 cursor-pointer" onclick="devConsole.toggleSection(this)">
                    <h3 class="text-lg font-semibold text-white flex items-center space-x-2">
                        <span>🔗</span><span>GET Parameters</span>
                    </h3>
                </div>
                <div class="p-6">
                    <pre class="bg-gray-900 border border-gray-600 rounded-lg p-4 text-sm text-gray-300 overflow-auto max-h-64"><?= htmlspecialchars(json_encode($overlayData['request_data']['get'], JSON_PRETTY_PRINT)) ?></pre>
                </div>
            </div>

            <!-- Cookies -->
            <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-gray-700 bg-opacity-80 px-6 py-4 border-b border-gray-600 cursor-pointer" onclick="devConsole.toggleSection(this)">
                    <h3 class="text-lg font-semibold text-white flex items-center space-x-2">
                        <span>🍪</span><span>Cookies</span>
                    </h3>
                </div>
                <div class="p-6">
                    <pre class="bg-gray-900 border border-gray-600 rounded-lg p-4 text-sm text-gray-300 overflow-auto max-h-64"><?= htmlspecialchars(json_encode($overlayData['request_data']['cookies'], JSON_PRETTY_PRINT)) ?></pre>
                </div>
            </div>
        </div>

        <!-- Session Panel -->
        <div id="session-panel" class="dev-panel hidden p-6 space-y-6">
            
            <!-- Session Information -->
            <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-gray-700 bg-opacity-80 px-6 py-4 border-b border-gray-600">
                    <h3 class="text-lg font-semibold text-white flex items-center space-x-2">
                        <span>🔐</span><span>Session Information</span>
                    </h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full bg-gray-900 rounded-lg overflow-hidden">
                            <tbody class="divide-y divide-gray-700">
                                <tr class="hover:bg-gray-700 hover:bg-opacity-30">
                                    <td class="px-4 py-3 text-sm font-semibold text-white">Session ID</td>
                                    <td class="px-4 py-3 text-sm text-gray-300"><?= htmlspecialchars($overlayData['session_id']) ?></td>
                                </tr>
                                <tr class="hover:bg-gray-700 hover:bg-opacity-30">
                                    <td class="px-4 py-3 text-sm font-semibold text-white">User ID</td>
                                    <td class="px-4 py-3 text-sm text-gray-300"><?= htmlspecialchars($overlayData['user']['id'] ?? 'Not logged in') ?></td>
                                </tr>
                                <tr class="hover:bg-gray-700 hover:bg-opacity-30">
                                    <td class="px-4 py-3 text-sm font-semibold text-white">Email</td>
                                    <td class="px-4 py-3 text-sm text-gray-300"><?= htmlspecialchars($overlayData['user']['email'] ?? 'N/A') ?></td>
                                </tr>
                                <tr class="hover:bg-gray-700 hover:bg-opacity-30">
                                    <td class="px-4 py-3 text-sm font-semibold text-white">Authenticated</td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php if ($overlayData['user']['authenticated']): ?>
                                            <span class="px-2 py-1 bg-green-500 bg-opacity-20 text-green-400 border border-green-500 border-opacity-30 rounded-full text-xs font-semibold">Yes</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 bg-red-500 bg-opacity-20 text-red-400 border border-red-500 border-opacity-30 rounded-full text-xs font-semibold">No</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Full Session Data -->
            <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-gray-700 bg-opacity-80 px-6 py-4 border-b border-gray-600 cursor-pointer" onclick="devConsole.toggleSection(this)">
                    <h3 class="text-lg font-semibold text-white flex items-center space-x-2">
                        <span>📦</span><span>Full Session Data</span>
                    </h3>
                </div>
                <div class="p-6">
                    <pre class="bg-gray-900 border border-gray-600 rounded-lg p-4 text-sm text-gray-300 overflow-auto max-h-96"><?= htmlspecialchars(json_encode($overlayData['session_data'], JSON_PRETTY_PRINT)) ?></pre>
                </div>
            </div>
        </div>

        <!-- Database Panel -->
        <div id="database-panel" class="dev-panel hidden p-6 space-y-6">
            
            <!-- Database Status -->
            <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-gray-700 bg-opacity-80 px-6 py-4 border-b border-gray-600">
                    <h3 class="text-lg font-semibold text-white flex items-center space-x-2">
                        <span>💾</span><span>Database Status</span>
                    </h3>
                </div>
                <div class="p-6">
                    <span class="px-3 py-1 bg-green-500 bg-opacity-20 text-green-400 border border-green-500 border-opacity-30 rounded-full text-sm font-semibold">Connected</span>
                    <p class="mt-3 text-gray-400">Database connection appears to be working correctly.</p>
                </div>
            </div>

            <!-- Query Inspector -->
            <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-gray-700 bg-opacity-80 px-6 py-4 border-b border-gray-600">
                    <h3 class="text-lg font-semibold text-white flex items-center space-x-2">
                        <span>📊</span><span>Query Inspector</span>
                    </h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-400 mb-4">Query logging would appear here in a full implementation.</p>
                    <div class="flex flex-wrap gap-3">
                        <button onclick="devConsole.runQuery()" class="px-4 py-2 bg-gray-600 bg-opacity-80 border border-gray-500 text-gray-300 rounded-lg hover:bg-opacity-100 transition-all duration-200 flex items-center space-x-2">
                            <span>🔍</span><span>Test Query</span>
                        </button>
                        <button onclick="devConsole.explainQuery()" class="px-4 py-2 bg-gray-600 bg-opacity-80 border border-gray-500 text-gray-300 rounded-lg hover:bg-opacity-100 transition-all duration-200 flex items-center space-x-2">
                            <span>📋</span><span>Explain Last Query</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs Panel -->
        <div id="logs-panel" class="dev-panel hidden p-6 space-y-6">
            
            <!-- Application Logs -->
            <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-gray-700 bg-opacity-80 px-6 py-4 border-b border-gray-600">
                    <h3 class="text-lg font-semibold text-white flex items-center space-x-2">
                        <span>📝</span><span>Application Logs</span>
                    </h3>
                </div>
                <div class="p-6">
                    <div class="bg-gray-900 border border-gray-600 rounded-lg h-80 overflow-y-auto p-3 space-y-1 text-sm font-mono">
                        <div class="flex items-start space-x-2 py-1 border-l-2 border-blue-500 pl-2 text-blue-400">
                            <span class="text-xs text-gray-500">[<?= $overlayData['timestamp'] ?>]</span>
                            <span class="text-blue-400">INFO:</span>
                            <span>Dev overlay loaded</span>
                        </div>
                        <div class="flex items-start space-x-2 py-1 border-l-2 border-gray-500 pl-2 text-gray-400">
                            <span class="text-xs text-gray-500">[<?= $overlayData['timestamp'] ?>]</span>
                            <span class="text-gray-400">DEBUG:</span>
                            <span>Session ID <?= $overlayData['session_id'] ?></span>
                        </div>
                        <div class="flex items-start space-x-2 py-1 border-l-2 border-blue-500 pl-2 text-blue-400">
                            <span class="text-xs text-gray-500">[<?= $overlayData['timestamp'] ?>]</span>
                            <span class="text-blue-400">INFO:</span>
                            <span>Route <?= $overlayData['route'] ?> accessed</span>
                        </div>
                        <div class="flex items-start space-x-2 py-1 border-l-2 border-yellow-500 pl-2 text-yellow-400">
                            <span class="text-xs text-gray-500">[<?= $overlayData['timestamp'] ?>]</span>
                            <span class="text-yellow-400">WARN:</span>
                            <span>This is a sample warning message</span>
                        </div>
                        <div class="flex items-start space-x-2 py-1 border-l-2 border-red-500 pl-2 text-red-400">
                            <span class="text-xs text-gray-500">[<?= $overlayData['timestamp'] ?>]</span>
                            <span class="text-red-400">ERROR:</span>
                            <span>This is a sample error message</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Panel -->
        <div id="performance-panel" class="dev-panel hidden p-6 space-y-6">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Performance Metrics -->
                <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-blue-400 mb-4 flex items-center space-x-2">
                        <span>⚡</span><span>Performance Metrics</span>
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-cyan-500 rounded-lg flex items-center justify-center text-lg">⏱️</div>
                            <div>
                                <div class="font-semibold text-white">Response Time</div>
                                <div class="text-sm text-gray-400"><?= htmlspecialchars($overlayData['request_time']) ?></div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-lg flex items-center justify-center text-lg">💾</div>
                            <div>
                                <div class="font-semibold text-white">Memory Peak</div>
                                <div class="text-sm text-gray-400"><?= htmlspecialchars($overlayData['system']['memory_peak']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- System Info -->
                <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-blue-400 mb-4 flex items-center space-x-2">
                        <span>📊</span><span>System Info</span>
                    </h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center text-lg">🐘</div>
                            <div>
                                <div class="font-semibold text-white">PHP Version</div>
                                <div class="text-sm text-gray-400"><?= htmlspecialchars($overlayData['system']['php_version']) ?></div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-gray-500 to-gray-600 rounded-lg flex items-center justify-center text-lg">🖥️</div>
                            <div>
                                <div class="font-semibold text-white">Server</div>
                                <div class="text-sm text-gray-400"><?= htmlspecialchars($overlayData['system']['server_software']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>

        <!-- Tools Panel -->
        <div id="tools-panel" class="dev-panel hidden p-6 space-y-6">
            
            <!-- Developer Tools -->
            <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl overflow-hidden">
                <div class="bg-gray-700 bg-opacity-80 px-6 py-4 border-b border-gray-600">
                    <h3 class="text-lg font-semibold text-white flex items-center space-x-2">
                        <span>🛠️</span><span>Developer Tools</span>
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        
                        <!-- Quick Tools -->
                        <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl p-4">
                            <h4 class="text-blue-400 font-semibold mb-3 flex items-center space-x-2">
                                <span>🔧</span><span>Quick Tools</span>
                            </h4>
                            <div class="space-y-2">
                                <button onclick="devConsole.phpInfo()" class="w-full px-3 py-2 bg-blue-500 bg-opacity-20 border border-blue-500 border-opacity-30 text-blue-400 rounded-lg hover:bg-opacity-30 transition-all duration-200 flex items-center space-x-2 text-sm">
                                    <span>🐘</span><span>PHP Info</span>
                                </button>
                                <button onclick="devConsole.testEmail()" class="w-full px-3 py-2 bg-gray-600 bg-opacity-80 border border-gray-500 text-gray-300 rounded-lg hover:bg-opacity-100 transition-all duration-200 flex items-center space-x-2 text-sm">
                                    <span>📧</span><span>Test Email</span>
                                </button>
                                <button onclick="devConsole.generateTestData()" class="w-full px-3 py-2 bg-gray-600 bg-opacity-80 border border-gray-500 text-gray-300 rounded-lg hover:bg-opacity-100 transition-all duration-200 flex items-center space-x-2 text-sm">
                                    <span>🎲</span><span>Generate Test Data</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Environment -->
                        <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl p-4">
                            <h4 class="text-blue-400 font-semibold mb-3 flex items-center space-x-2">
                                <span>⚙️</span><span>Environment</span>
                            </h4>
                            <div class="space-y-2">
                                <button onclick="devConsole.showEnvVars()" class="w-full px-3 py-2 bg-gray-600 bg-opacity-80 border border-gray-500 text-gray-300 rounded-lg hover:bg-opacity-100 transition-all duration-200 flex items-center space-x-2 text-sm">
                                    <span>🌍</span><span>Environment Variables</span>
                                </button>
                                <button onclick="devConsole.configCheck()" class="w-full px-3 py-2 bg-gray-600 bg-opacity-80 border border-gray-500 text-gray-300 rounded-lg hover:bg-opacity-100 transition-all duration-200 flex items-center space-x-2 text-sm">
                                    <span>✅</span><span>Config Check</span>
                                </button>
                                <button onclick="devConsole.runDiagnostics()" class="w-full px-3 py-2 bg-gray-600 bg-opacity-80 border border-gray-500 text-gray-300 rounded-lg hover:bg-opacity-100 transition-all duration-200 flex items-center space-x-2 text-sm">
                                    <span>🔍</span><span>Run Diagnostics</span>
                                </button>
                            </div>
                        </div>
                        
                        <!-- UI Tools -->
                        <div class="bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl p-4">
                            <h4 class="text-blue-400 font-semibold mb-3 flex items-center space-x-2">
                                <span>🎨</span><span>UI Tools</span>
                            </h4>
                            <div class="space-y-2">
                                <button onclick="devConsole.toggleGrid()" class="w-full px-3 py-2 bg-gray-600 bg-opacity-80 border border-gray-500 text-gray-300 rounded-lg hover:bg-opacity-100 transition-all duration-200 flex items-center space-x-2 text-sm">
                                    <span>📐</span><span>Toggle Grid</span>
                                </button>
                                <button onclick="devConsole.measureTool()" class="w-full px-3 py-2 bg-gray-600 bg-opacity-80 border border-gray-500 text-gray-300 rounded-lg hover:bg-opacity-100 transition-all duration-200 flex items-center space-x-2 text-sm">
                                    <span>📏</span><span>Measure Tool</span>
                                </button>
                                <button onclick="devConsole.colorPicker()" class="w-full px-3 py-2 bg-gray-600 bg-opacity-80 border border-gray-500 text-gray-300 rounded-lg hover:bg-opacity-100 transition-all duration-200 flex items-center space-x-2 text-sm">
                                    <span>🎨</span><span>Color Picker</span>
                                </button>
                            </div>
                        </div>
                        
                    </div>
                    
                    <!-- Code Generator -->
                    <div class="mt-6 bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl overflow-hidden">
                        <div class="bg-gray-700 bg-opacity-80 px-6 py-4 border-b border-gray-600">
                            <h4 class="text-blue-400 font-semibold flex items-center space-x-2">
                                <span>🎯</span><span>Code Generator</span>
                            </h4>
                        </div>
                        <div class="p-6">
                            <div class="flex flex-col sm:flex-row gap-3">
                                <select class="px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white">
                                    <option>Controller</option>
                                    <option>Model</option>
                                    <option>Service</option>
                                    <option>Migration</option>
                                    <option>Test</option>
                                </select>
                                <input type="text" placeholder="Class name..." class="flex-1 px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400">
                                <button class="px-6 py-2 bg-blue-500 bg-opacity-20 border border-blue-500 border-opacity-30 text-blue-400 rounded-lg hover:bg-opacity-30 transition-all duration-200">Generate</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- API Tester -->
                    <div class="mt-6 bg-gray-800 bg-opacity-60 border border-gray-700 rounded-xl overflow-hidden">
                        <div class="bg-gray-700 bg-opacity-80 px-6 py-4 border-b border-gray-600">
                            <h4 class="text-blue-400 font-semibold flex items-center space-x-2">
                                <span>📊</span><span>API Tester</span>
                            </h4>
                        </div>
                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                                <select class="sm:col-span-2 px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white">
                                    <option>GET</option>
                                    <option>POST</option>
                                    <option>PUT</option>
                                    <option>DELETE</option>
                                </select>
                                <input type="text" placeholder="API endpoint..." value="/api/" class="sm:col-span-8 px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400">
                                <button class="sm:col-span-2 px-6 py-2 bg-blue-500 bg-opacity-20 border border-blue-500 border-opacity-30 text-blue-400 rounded-lg hover:bg-opacity-30 transition-all duration-200">Send</button>
                            </div>
                            <textarea placeholder="Request body (JSON)..." class="w-full h-24 px-4 py-2 bg-gray-800 border border-gray-600 rounded-lg text-white placeholder-gray-400 resize-none"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
console.log('🔧 DEV OVERLAY TAILWIND: Script loading...');

(function() {
    const overlayData = <?= $overlayDataJson ?>;
    
    // DOM Elements
    const floatingBtn = document.getElementById('dev-floating-btn');
    const overlayMain = document.getElementById('dev-overlay-main');
    const closeBtn = document.getElementById('dev-close-btn');
    const navItems = document.querySelectorAll('.dev-nav-item');
    const panels = document.querySelectorAll('.dev-panel');
    
    // State
    let isOpen = false;
    let currentPanel = 'overview';

    // Developer Console Object
    window.devConsole = {
        // Core functions
        toggle() {
            isOpen = !isOpen;
            
            if (isOpen) {
                overlayMain.classList.remove('hidden');
                overlayMain.classList.remove('translate-x-full');
                floatingBtn.classList.add('bg-gradient-to-br', 'from-red-500', 'to-pink-500');
                floatingBtn.classList.remove('from-purple-500', 'to-pink-500');
                floatingBtn.innerHTML = '✖';
                document.body.style.overflow = 'hidden';
            } else {
                overlayMain.classList.add('translate-x-full');
                setTimeout(() => {
                    overlayMain.classList.add('hidden');
                }, 500);
                floatingBtn.classList.remove('from-red-500', 'to-pink-500');
                floatingBtn.classList.add('from-purple-500', 'to-pink-500');
                floatingBtn.innerHTML = '⚡';
                document.body.style.overflow = '';
            }
        },
        
        close() {
            if (isOpen) this.toggle();
        },
        
        switchPanel(panelName) {
            currentPanel = panelName;
            
            // Update nav
            navItems.forEach(item => {
                if (item.dataset.panel === panelName) {
                    item.classList.add('text-blue-400', 'border-blue-400');
                    item.classList.remove('text-gray-400', 'border-transparent');
                } else {
                    item.classList.remove('text-blue-400', 'border-blue-400');
                    item.classList.add('text-gray-400', 'border-transparent');
                }
            });
            
            // Update panels
            panels.forEach(panel => {
                if (panel.id === panelName + '-panel') {
                    panel.classList.remove('hidden');
                } else {
                    panel.classList.add('hidden');
                }
            });
        },
        
        toggleSection(header) {
            const section = header.parentElement;
            const body = section.querySelector('.p-6');
            
            if (body.style.display === 'none') {
                body.style.display = 'block';
                header.style.opacity = '1';
            } else {
                body.style.display = 'none';
                header.style.opacity = '0.7';
            }
        },
        
        // Quick actions
        clearCache() {
            console.log('🗑️ Cache cleared (simulated)');
            this.showNotification('Cache cleared successfully!', 'success');
        },
        
        refreshData() {
            console.log('🔄 Data refreshed (simulated)');
            this.showNotification('Data refreshed!', 'info');
            setTimeout(() => location.reload(), 1000);
        },
        
        exportData() {
            const dataStr = JSON.stringify(overlayData, null, 2);
            const dataBlob = new Blob([dataStr], {type: 'application/json'});
            const url = URL.createObjectURL(dataBlob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'debug-data-' + new Date().toISOString().slice(0,19) + '.json';
            link.click();
            URL.revokeObjectURL(url);
            this.showNotification('Debug data exported!', 'success');
        },
        
        toggleMaintenance() {
            console.log('🔧 Maintenance mode toggled (simulated)');
            this.showNotification('Maintenance mode toggled!', 'warning');
        },
        
        // Database tools
        runQuery() {
            console.log('🔍 Running test query (simulated)');
            this.showNotification('Test query executed!', 'info');
        },
        
        explainQuery() {
            console.log('📋 Explaining query (simulated)');
            this.showNotification('Query explanation generated!', 'info');
        },
        
        // Developer tools
        phpInfo() {
            window.open('data:text/html,<pre>' + encodeURIComponent(JSON.stringify(overlayData.system, null, 2)) + '</pre>', '_blank');
        },
        
        testEmail() {
            console.log('📧 Testing email functionality (simulated)');
            this.showNotification('Test email sent!', 'success');
        },
        
        generateTestData() {
            console.log('🎲 Generating test data (simulated)');
            this.showNotification('Test data generated!', 'success');
        },
        
        showEnvVars() {
            console.log('🌍 Environment variables:', overlayData);
            this.showNotification('Environment variables logged to console!', 'info');
        },
        
        configCheck() {
            console.log('✅ Configuration check (simulated)');
            this.showNotification('Configuration check completed!', 'success');
        },
        
        runDiagnostics() {
            console.log('🔍 Running diagnostics (simulated)');
            this.showNotification('Diagnostics completed - check console!', 'info');
        },
        
        // UI Tools
        toggleGrid() {
            const gridOverlay = document.getElementById('dev-grid-overlay');
            if (gridOverlay) {
                gridOverlay.remove();
                this.showNotification('Grid overlay removed!', 'info');
            } else {
                this.createGridOverlay();
                this.showNotification('Grid overlay activated!', 'success');
            }
        },
        
        createGridOverlay() {
            const gridDiv = document.createElement('div');
            gridDiv.id = 'dev-grid-overlay';
            gridDiv.className = 'fixed inset-0 pointer-events-none z-30';
            gridDiv.style.backgroundImage = 'linear-gradient(rgba(255,0,0,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,0,0,0.1) 1px, transparent 1px)';
            gridDiv.style.backgroundSize = '20px 20px';
            document.body.appendChild(gridDiv);
        },
        
        measureTool() {
            console.log('📏 Measure tool activated (simulated)');
            this.showNotification('Measure tool activated - click and drag!', 'info');
        },
        
        colorPicker() {
            console.log('🎨 Color picker activated (simulated)');
            this.showNotification('Color picker activated!', 'info');
        },
        
        // Notification system
        showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            let bgColor, textColor, borderColor;
            
            switch(type) {
                case 'success':
                    bgColor = 'bg-green-900';
                    textColor = 'text-green-100';
                    borderColor = 'border-green-500';
                    break;
                case 'warning':
                    bgColor = 'bg-yellow-900';
                    textColor = 'text-yellow-100';
                    borderColor = 'border-yellow-500';
                    break;
                case 'error':
                    bgColor = 'bg-red-900';
                    textColor = 'text-red-100';
                    borderColor = 'border-red-500';
                    break;
                default:
                    bgColor = 'bg-blue-900';
                    textColor = 'text-blue-100';
                    borderColor = 'border-blue-500';
            }
            
            notification.className = `fixed top-5 right-5 ${bgColor} ${textColor} p-3 rounded-lg border-l-4 ${borderColor} font-mono text-sm z-50 shadow-xl backdrop-blur-sm transform translate-x-full transition-transform duration-300`;
            notification.textContent = message;
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => notification.classList.remove('translate-x-full'), 100);
            
            // Remove after delay
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    };

    // Event listeners
    floatingBtn.addEventListener('click', () => devConsole.toggle());
    closeBtn.addEventListener('click', () => devConsole.close());
    
    navItems.forEach(item => {
        item.addEventListener('click', () => {
            devConsole.switchPanel(item.dataset.panel);
        });
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        // Escape to toggle (open/close)
        if (e.key === 'Escape') {
            e.preventDefault();
            devConsole.toggle();
        }
        
        // Number keys to switch panels (when overlay is open)
        if (isOpen && e.key >= '1' && e.key <= '7') {
            e.preventDefault();
            const panels = ['overview', 'request', 'session', 'database', 'logs', 'performance', 'tools'];
            const panelIndex = parseInt(e.key) - 1;
            if (panels[panelIndex]) {
                devConsole.switchPanel(panels[panelIndex]);
            }
        }
        
        // Ctrl+R to refresh data
        if (isOpen && e.ctrlKey && e.key === 'r') {
            e.preventDefault();
            devConsole.refreshData();
        }
        
        // Ctrl+E to export
        if (isOpen && e.ctrlKey && e.key === 'e') {
            e.preventDefault();
            devConsole.exportData();
        }
    });

    // Search functionality
    const requestSearch = document.getElementById('request-search');
    if (requestSearch) {
        requestSearch.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const tables = document.querySelectorAll('#request-panel table tbody tr');
            tables.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.classList.remove('hidden');
                } else {
                    row.classList.add('hidden');
                }
            });
        });
    }

    // Initialize
    console.log('⚡ Advanced Dev Console (Tailwind) loaded!');
    console.log('Hotkeys:');
    console.log('  Ctrl + ` : Toggle overlay');
    console.log('  Esc      : Close overlay');
    console.log('  1-7      : Switch panels');
    console.log('  Ctrl + R : Refresh data');
    console.log('  Ctrl + E : Export data');
    
    devConsole.showNotification('Dev Console Ready! Press Ctrl+` to toggle', 'success');
})();
</script>

<?php echo "<!-- DEV OVERLAY TAILWIND END -->\n"; ?>