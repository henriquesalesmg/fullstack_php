<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/resources/components/auth.php';
require_once __DIR__ . '/../app/middleware/SecurityMiddleware.php';
$query = require __DIR__ . '/../app/config/db.php';

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$publicRoutes = [
    '/',
    '/login',
    '/register',
    '/recover',
    '/reset-password',
    '/forgot-password',
    '/unauthorized',
    '/api/user'
];

if (!in_array($requestUri, $publicRoutes)) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (!isset($_SESSION['user_id'], $_SESSION['session_token'])) {
        header('Location: /login');
        exit;
    }
    // Verifica sessão usando Medoo
    $session = $query->get('user_sessions', '*', [
        'user_id' => $_SESSION['user_id'],
        'token' => $_SESSION['session_token'],
        'expires_at[>=]' => date('Y-m-d H:i:s')
    ]);
    if (!$session) {
        session_destroy();
        header('Location: /login?error=Sessão expirada');
        exit;
    }
}

// --- Rotas da Aplicação ---
switch ($requestUri) {
    case '/':
        require __DIR__ . '/../app/resources/views/index.php';
        break;

    case '/login':
        require __DIR__ . '/../app/resources/views/auth/login.php';
        break;

    case '/register':
        require __DIR__ . '/../app/resources/views/auth/register.php';
        break;

    case '/forgot-password':
        require __DIR__ . '/../app/resources/views/auth/forgot-password.php';
        break;

    case '/unauthorized':
        require __DIR__ . '/../app/resources/views/auth/unauthorized.php';
        break;

    case '/tasks':
        require __DIR__ . '/../app/resources/views/tasks/index.php';
        break;

    case '/tasks/create':
        require __DIR__ . '/../app/resources/views/tasks/create.php';
        break;

    case '/tasks/update':
        require __DIR__ . '/../app/resources/views/tasks/update.php';
        break;

    case '/tasks/list':
        require __DIR__ . '/../app/resources/views/tasks/list.php';
        break;

    case '/admin/users':
        require __DIR__ . '/../app/resources/views/admin/users.php';
        break;

    case '/recover':
        require __DIR__ . '/../app/resources/views/auth/recover.php';
        break;

    case '/reset-password':
        require __DIR__ . '/../app/resources/views/auth/reset-password.php';
        break;

    case '/logout':
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_id'], $_SESSION['session_token'])) {
            $query->delete('user_sessions', [
                'user_id' => $_SESSION['user_id'],
                'token' => $_SESSION['session_token']
            ]);
        }
        session_destroy();
        header("Location: /login");
        exit;
        break;

    case '/api/user':
        require __DIR__ . '/../app/api/user.php';
        break;

    case '/api/tasks':
        require __DIR__ . '/../app/api/index.php';
        break;

    case '/api/admin':
        require __DIR__ . '/../app/api/admin.php';
        break;

    default:
        http_response_code(404);
        require __DIR__ . '/../app/resources/views/auth/unauthorized.php';
        break;
}