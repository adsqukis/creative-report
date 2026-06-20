<?php
// === SETUP MODE — diakses via ?setup=creativeops2026 ===
if (isset($_GET['setup']) && $_GET['setup'] === 'creativeops2026') {
    define('APP_ROOT', __DIR__);
    require_once APP_ROOT . '/config/app.php';
    $cfg = require APP_ROOT . '/config/database.php';
    $dsn = 'mysql:host=' . $cfg['host'] . ';port=' . ($cfg['port']??'3306') . ';dbname=' . $cfg['name'] . ';charset=utf8mb4';
    header('Content-Type: text/plain');
    try {
        $pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::MYSQL_ATTR_MULTI_STATEMENTS=>true]);
        echo "[OK] DB: {$cfg['host']}:{$cfg['port']}/{$cfg['name']}\n\n";
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "Tables: " . implode(', ', $tables) . "\n\n";
        $users = $pdo->query("SELECT id, email, role, is_active FROM co_users")->fetchAll(PDO::FETCH_ASSOC);
        echo "Users:\n";
        foreach ($users as $u) echo "  {$u['id']} | {$u['email']} | {$u['role']} | active={$u['is_active']}\n";
        if (empty($users)) echo "  (none)\n";
        // Test password
        $row = $pdo->query("SELECT password FROM co_users WHERE email='admin@creative-ops.local'")->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $match = password_verify('Admin@2026', $row['password']);
            echo "\nPassword Admin@2026: " . ($match ? 'MATCH' : 'NO MATCH') . "\n";
        }
    } catch (Exception $e) {
        echo "ERROR: " . $e->getMessage() . "\n";
    }
    exit;
}
// === END SETUP MODE ===

session_start();

define('APP_ROOT', __DIR__);

spl_autoload_register(function($class) {
    $locations = [
        APP_ROOT . '/app/Core/'          . $class . '.php',
        APP_ROOT . '/app/Models/'        . $class . '.php',
        APP_ROOT . '/app/Services/'      . $class . '.php',
        APP_ROOT . '/app/Providers/'     . $class . '.php',
        APP_ROOT . '/app/Repositories/'  . $class . '.php',
        APP_ROOT . '/app/Helpers/'       . $class . '.php',
        APP_ROOT . '/controllers/'       . $class . '.php',
    ];
    foreach ($locations as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

require_once APP_ROOT . '/config/app.php';

// CSRF check for all non-API POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reqUri  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $isApi   = strpos($reqUri, '/api/') !== false;
    $isLogin = ($reqUri === '/login' || substr($reqUri, -6) === '/login');
    if (!$isApi && !$isLogin) {
        Csrf::verifyOrFail();
    }
}

$router = new Router();

// Auth
$router->get('/login',   ['AuthController', 'showLogin']);
$router->post('/login',  ['AuthController', 'login']);
$router->get('/logout',  ['AuthController', 'logout']);

// Dashboard
$router->get('/',          ['DashboardController', 'overview']);
$router->get('/dashboard', ['DashboardController', 'overview']);
$router->get('/executive', ['DashboardController', 'executive']);

// Requests
$router->get('/requests',              ['RequestController', 'index']);
$router->get('/requests/create',       ['RequestController', 'create']);
$router->post('/requests/store',       ['RequestController', 'store']);
$router->get('/requests/{id}',         ['RequestController', 'show']);
$router->get('/requests/{id}/edit',    ['RequestController', 'edit']);
$router->post('/requests/{id}/update', ['RequestController', 'update']);
$router->post('/requests/{id}/status', ['RequestController', 'updateStatus']);
$router->post('/requests/{id}/assign', ['RequestController', 'assign']);
$router->post('/requests/{id}/comment',  ['RequestController', 'addComment']);
$router->post('/requests/{id}/revision', ['RequestController', 'addRevision']);

// Users
$router->get('/users',               ['UserController', 'index']);
$router->get('/users/create',        ['UserController', 'create']);
$router->post('/users/store',        ['UserController', 'store']);
$router->get('/users/{id}/edit',     ['UserController', 'edit']);
$router->post('/users/{id}/update',  ['UserController', 'update']);

// Products & Campaigns
$router->get('/products',              ['ProductController', 'index']);
$router->get('/products/create',       ['ProductController', 'create']);
$router->post('/products/store',       ['ProductController', 'store']);
$router->get('/products/{id}/edit',    ['ProductController', 'edit']);
$router->post('/products/{id}/update', ['ProductController', 'update']);
$router->get('/campaigns',             ['CampaignController', 'index']);
$router->get('/campaigns/create',      ['CampaignController', 'create']);
$router->post('/campaigns/store',      ['CampaignController', 'store']);

// Departments
$router->get('/departments',              ['DepartmentController', 'index']);
$router->get('/departments/create',       ['DepartmentController', 'create']);
$router->post('/departments/store',       ['DepartmentController', 'store']);
$router->get('/departments/{id}/edit',    ['DepartmentController', 'edit']);
$router->post('/departments/{id}/update', ['DepartmentController', 'update']);
$router->post('/departments/{id}/delete', ['DepartmentController', 'delete']);

// Workload
$router->get('/workload', ['WorkloadController', 'index']);

// Analytics
$router->get('/analytics',            ['AnalyticsController', 'index']);
$router->get('/analytics/sla',        ['AnalyticsController', 'sla']);
$router->get('/analytics/scorecard',  ['AnalyticsController', 'scorecard']);

// Workspace
$router->get('/workspace',               ['WorkspaceController', 'index']);
$router->post('/workspace/{id}/start',   ['WorkspaceController', 'startTask']);
$router->post('/workspace/{id}/submit',  ['WorkspaceController', 'submitReview']);

// AI
$router->get('/ai/insights',       ['AiController', 'insights']);
$router->get('/ai/briefing',       ['AiController', 'briefing']);
$router->get('/ai/chat',           ['AiController', 'chat']);
$router->post('/ai/chat/send',     ['AiController', 'chatSend']);
$router->get('/ai/reports',        ['AiController', 'reports']);

// Settings
$router->get('/settings',        ['SettingsController', 'index']);
$router->post('/settings/update', ['SettingsController', 'update']);

// Profile
$router->get('/profile',         ['ProfileController', 'index']);
$router->post('/profile/update', ['ProfileController', 'update']);

// Files
$router->post('/requests/{id}/files',       ['FileController', 'upload']);
$router->get('/files/{id}/download',        ['FileController', 'download']);
$router->post('/files/{id}/delete',         ['FileController', 'delete']);

// API
$router->get('/api/v1/workload',              ['ApiController', 'workload']);
$router->get('/api/v1/notifications',         ['ApiController', 'notifications']);
$router->post('/api/v1/notifications/read',   ['ApiController', 'markRead']);
$router->post('/api/v1/ai/chat',              ['ApiController', 'aiChat']);

$router->dispatch();