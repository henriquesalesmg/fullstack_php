<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../app/resources/components/auth.php'; 

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

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

    case '/logout':
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
    default:
        http_response_code(404);
        require __DIR__ . '/../app/resources/views/auth/unauthorized.php'; 
        break;
}
?>