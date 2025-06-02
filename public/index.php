<?php
session_start();
require_once __DIR__ . '/../app/config/db.php';


function isAuthenticated() {
    return isset($_SESSION['user_id']);
}


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($uri) {
    case '/':
        require_once __DIR__ . '/../app/resources/views/index.php';
        break;

    case '/login':
        require_once __DIR__ . '/../app/resources/views/auth/login.php';
        break;

    case '/logout':
        require_once __DIR__ . '/../app/resources/views/auth/logout.php';
        break;

    case '/tasks':
        if (!isAuthenticated()) {
            header('Location: /login');
            exit;
        }
        require_once __DIR__ . '/../app/resources/views/tasks/list.php';
        break;

    case '/tasks/create':
        if (!isAuthenticated()) {
            header('Location: /login');
            exit;
        }
        require_once __DIR__ . '/../app/resources/views/tasks/create.php';
        break;

    case '/tasks/update':
        if (!isAuthenticated()) {
            header('Location: /login');
            exit;
        }
        require_once __DIR__ . '/../app/resources/views/tasks/update.php';
        break;

    case '/tasks/delete':
        if (!isAuthenticated()) {
            header('Location: /login');
            exit;
        }
        require_once __DIR__ . '/../app/resources/views/tasks/delete.php';
        break;

    default:
        http_response_code(404);
        echo "Página não encontrada.";
        break;
}
