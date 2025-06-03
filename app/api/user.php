<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../resources/components/auth.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';

    if ($action === 'login') {
        $email = $_POST['email'] ?? '';
        $psw = $_POST['password'] ?? '';
        $csrfToken = $_POST['csrf_token'] ?? '';

        // Verifica CSRF
        if (!verifyCsrfToken($csrfToken)) {
            header("Location: /login?error=Token CSRF inválido.");
            exit;
        }

        if (empty($email) || empty($psw)) {
            header("Location: /login?error=E-mail e senha são obrigatórios.");
            exit;
        }

        $query = require __DIR__ . '/../config/db.php';

        // Busca o usuário pelo e-mail
        $user = $query->from('users')->where('email', $email)->fetch();
        if (!$user || !password_verify($psw, $user['password'])) {
            header("Location: /login?error=E-mail ou senha inválidos.");
            exit;
        }

        // Login bem-sucedido
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role']; // Salva a role na sessão
        $_SESSION['is_admin'] = ($user['role'] === 'admin'); // Booleano para facilitar

        header("Location: /tasks");
        exit;
    }
}
