<?php
$query = require __DIR__ . '/../config/db.php';
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
            $users = $query->from('users')->select('id, name, email, role')->fetchAll();
            echo json_encode(['success' => true, 'users' => $users]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao listar usuários.']);
        }
        break;

    case 'get':
        // Busca usuário por ID
        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID não informado.']);
            exit;
        }
        try {
            $user = $query->from('users')->select('id, name, email, role')->where('id', $id)->fetch();
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

        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
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
                $result = $query->update('users', $set, $id)->execute();
                echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso.']);
            } else {
                // Adicionar usuário
                if (empty($password)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Senha é obrigatória para novo usuário.']);
                    exit;
                }
                $exists = $query->from('users')->where('email', $email)->fetch();
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
                $query->insertInto('users', $values)->execute();
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
        $id = $_POST['id'] ?? null;
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
            $result = $query->deleteFrom('users', $id)->execute();
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