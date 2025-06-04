<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../resources/components/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';

    // --- RATE LIMITING PARA LOGIN ---
    if ($action === 'login') {
        $ip = $_SERVER['REMOTE_ADDR'];
        $maxAttempts = 5;
        $lockoutTime = 300; // 5 minutos

        if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = [];
        if (!isset($_SESSION['login_lockout'])) $_SESSION['login_lockout'] = [];

        // Limpa tentativas antigas
        if (isset($_SESSION['login_attempts'][$ip]) && $_SESSION['login_attempts'][$ip]['time'] < time() - $lockoutTime) {
            unset($_SESSION['login_attempts'][$ip]);
            unset($_SESSION['login_lockout'][$ip]);
        }

        // Verifica se está bloqueado
        if (isset($_SESSION['login_lockout'][$ip]) && $_SESSION['login_lockout'][$ip] > time()) {
            $wait = $_SESSION['login_lockout'][$ip] - time();
            header("Location: /login?error=Excesso de tentativas. Tente novamente em {$wait} segundos.");
            exit;
        }

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

        // Busca o usuário pelo e-mail
        $user = $query->get('users', '*', ['email' => $email]);
        if (!$user || !password_verify($psw, $user['password'])) {
            // Incrementa tentativas
            if (!isset($_SESSION['login_attempts'][$ip])) {
                $_SESSION['login_attempts'][$ip] = ['count' => 1, 'time' => time()];
            } else {
                $_SESSION['login_attempts'][$ip]['count']++;
                $_SESSION['login_attempts'][$ip]['time'] = time();
            }
            // Bloqueia se exceder o limite
            if ($_SESSION['login_attempts'][$ip]['count'] >= $maxAttempts) {
                $_SESSION['login_lockout'][$ip] = time() + $lockoutTime;
                header("Location: /login?error=Excesso de tentativas. Tente novamente em {$lockoutTime} segundos.");
                exit;
            }
            header("Location: /login?error=E-mail ou senha inválidos.");
            exit;
        }

        // Login bem-sucedido: limpa tentativas
        unset($_SESSION['login_attempts'][$ip]);
        unset($_SESSION['login_lockout'][$ip]);

        $userId = $user['id'];
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+2 hours'));

        // Insere sessão do usuário
        $query->insert('user_sessions', [
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expiresAt
        ]);
        // Remove sessões expiradas
        $query->delete('user_sessions', [
            'expires_at[<]' => date('Y-m-d H:i:s')
        ]);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['is_admin'] = ($user['role'] === 'admin');
        $_SESSION['session_token'] = $token;
        $_SESSION['role'] = $user['role'];
        header("Location: /tasks");
        exit;
    }

    // --- ATUALIZAÇÃO DE DADOS DO USUÁRIO ---
    if ($action === 'settings') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $name = trim($_POST['name'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($name)) {
            echo json_encode(['success' => false, 'message' => 'O nome é obrigatório.']);
            exit;
        }

        if (!empty($password)) {
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $password)) {
                echo json_encode(['success' => false, 'message' => 'A senha deve ter pelo menos 6 caracteres, incluindo maiúscula, minúscula, número e caractere especial.']);
                exit;
            }
        }

        try {
            $set = ['name' => $name];
            if (!empty($password)) {
                $set['password'] = password_hash($password, PASSWORD_DEFAULT);
            }
            $query->update('users', $set, ['id' => $userId]);
            $_SESSION['user_name'] = $name;
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar dados.']);
        }
        exit;
    }

    // --- REGISTRO DE NOVO USUÁRIO ---
    if ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (empty($name) || empty($email) || empty($password) || empty($confirm)) {
            header("Location: /register?error=Todos os campos são obrigatórios.");
            exit;
        }

        if ($password !== $confirm) {
            header("Location: /register?error=As senhas não conferem.");
            exit;
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $password)) {
            header("Location: /register?error=Senha fraca. Use pelo menos 6 caracteres, incluindo maiúscula, minúscula, número e caractere especial.");
            exit;
        }

        $exists = $query->has('users', ['email' => $email]);
        if ($exists) {
            header("Location: /register?error=E-mail já cadastrado.");
            exit;
        }

        try {
            $query->insert('users', [
                'name' => $name,
                'email' => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role' => 'user'
            ]);

            header("Location: /login?success=Cadastro realizado com sucesso! Acesse com o email e senha.");
            exit;
        } catch (PDOException $e) {
            header("Location: /register?error=Erro ao registrar usuário.");
            exit;
        }
    }

    // --- RECUPERAÇÃO DE SENHA ---
    if ($action === 'recover') {
        $email = trim($_POST['email'] ?? '');
        $name = trim($_POST['name'] ?? '');

        $user = $query->get('users', '*', ['email' => $email]);
        if (!$user || strtolower($user['name']) !== strtolower($name)) {
            header("Location: /recover?error=Dados não conferem.");
            exit;
        }

        $_SESSION['recover_user_id'] = $user['id'];
        header("Location: /reset-password");
        exit;
    }

    // --- RESET DE SENHA ---
    if ($action === 'reset-password') {
        if (empty($_SESSION['recover_user_id'])) {
            header('Location: /login');
            exit;
        }
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if ($password !== $confirm) {
            header("Location: /reset-password?error=As senhas não conferem.");
            exit;
        }
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $password)) {
            header("Location: /reset-password?error=Senha fraca.");
            exit;
        }
        $userId = $_SESSION['recover_user_id'];
        $query->update('users', [
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ], ['id' => $userId]);
        unset($_SESSION['recover_user_id']);
        header("Location: /login?success=Senha redefinida com sucesso! Faça o login.");
        exit;
    }
}