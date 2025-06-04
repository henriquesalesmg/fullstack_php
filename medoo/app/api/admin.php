<?php
$db = require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../resources/components/auth.php';
header('Content-Type: application/json');

if (empty($_SESSION['is_admin'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado. Apenas administradores.']);
    exit;
}

$action = $_GET['action'] ?? null;

switch ($action) {
    case 'list':
        // Lista todos os usuários
        try {
            $users = $query->select('users', ['id', 'name', 'email', 'role']);
            echo json_encode(['success' => true, 'users' => $users]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao listar usuários.']);
        }
        break;

    case 'get':
        // Busca usuário por ID
        $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID não informado.']);
            exit;
        }
        try {
            $user = $query->get('users', ['id', 'name', 'email', 'role'], ['id' => $id]);
            if ($user) {
                echo json_encode(['success' => true, 'user' => $user]);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Usuário não encontrado.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar usuário.']);
        }
        break;

    case 'save':
        // Adiciona ou edita usuário
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
            exit;
        }

        $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : null;
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? '';

        if (empty($name) || empty($email)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Nome e e-mail são obrigatórios.']);
            exit;
        }

        try {
            if ($id) {
                // Editar usuário
                $set = [
                    'name' => $name,
                    'email' => $email,
                    'role' => $role
                ];
                if (!empty($password)) {
                    $set['password'] = password_hash($password, PASSWORD_DEFAULT);
                }
                $query->update('users', $set, ['id' => $id]);
                echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso.']);
            } else {
                // Adicionar usuário
                if (empty($password)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Senha é obrigatória para novo usuário.']);
                    exit;
                }
                $exists = $query->has('users', ['email' => $email]);
                if ($exists) {
                    http_response_code(409);
                    echo json_encode(['success' => false, 'message' => 'E-mail já cadastrado.']);
                    exit;
                }
                $values = [
                    'name' => $name,
                    'email' => $email,
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'role' => $role
                ];
                $query->insert('users', $values);
                echo json_encode(['success' => true, 'message' => 'Usuário criado com sucesso.']);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao salvar usuário.']);
        }
        break;

    case 'delete':
        // Remove usuário
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
            exit;
        }
        $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID não informado.']);
            exit;
        }
        // Não permite remover a si mesmo
        if ($id == $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Você não pode remover seu próprio usuário.']);
            exit;
        }
        try {
            $query->delete('users', ['id' => $id]);
            echo json_encode(['success' => true, 'message' => 'Usuário removido com sucesso.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao remover usuário.']);
        }
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
        break;
}