<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../resources/components/auth.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Não autorizado. Faça login para acessar.']);
    exit;
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? null;
$userId = $_SESSION['user_id'];

require_once __DIR__ . '/../../vendor/autoload.php';

use Envms\FluentPDO\Query;

switch ($action) {
    case 'list':
        try {
            if (!empty($_SESSION['is_admin'])) {
                // Admin: vê todas as tarefas com nome do usuário
                $tasks = $query->from('tasks')
                    ->select('tasks.*, users.name AS user_name')
                    ->leftJoin('users ON users.id = tasks.user_id')
                    ->fetchAll();
            } else {
                // Usuário comum: só vê as dele
                $tasks = $query->from('tasks')
                    ->select('tasks.*, users.name AS user_name')
                    ->leftJoin('users ON users.id = tasks.user_id')
                    ->where('tasks.user_id', $userId)
                    ->fetchAll();
            }
            echo json_encode(['tasks' => $tasks]);
        } catch (PDOException $e) {
            error_log("Erro ao listar tarefas: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao listar tarefas.']);
        }
        break;

    case 'create':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validação CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                http_response_code(403); // Forbidden
                echo json_encode(['success' => false, 'message' => 'Erro de segurança (CSRF inválido).']);
                exit;
            }

            $title = $_POST['title'] ?? null;
            $description = $_POST['description'] ?? null;

            if (empty($title)) {
                http_response_code(400); // Bad Request
                echo json_encode(['success' => false, 'message' => 'O título da tarefa é obrigatório.']);
                exit;
            }

            try {
                $values = [
                    'title' => $title,
                    'description' => $description,
                    'user_id' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $result = $query->insertInto('tasks', $values)->execute();

                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Tarefa criada com sucesso!', 'task_id' => $pdo->lastInsertId()]);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Falha ao criar tarefa.']);
                }
            } catch (PDOException $e) {
                error_log("Erro ao criar tarefa: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao criar tarefa.']);
            }
        } else {
            http_response_code(405); // Method Not Allowed
            echo json_encode(['success' => false, 'message' => 'Método não permitido para esta ação.']);
        }
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validação CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                http_response_code(403); // Forbidden
                echo json_encode(['success' => false, 'message' => 'Erro de segurança (CSRF inválido).']);
                exit;
            }

            $id = $_POST['id'] ?? null;
            $title = $_POST['title'] ?? null;
            $description = $_POST['description'] ?? null;
            $completed = isset($_POST['completed']) ? (bool)$_POST['completed'] : null;

            if (empty($id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID da tarefa é obrigatório.']);
                exit;
            }

            // Só exige título se for atualização de título/descrição
            if ($title === null && $completed === null) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nada para atualizar.']);
                exit;
            }

            // --- COLOQUE ESTE BLOCO AQUI ---
            $set = [];
            if ($title !== null) $set['title'] = $title;
            if ($description !== null) $set['description'] = $description;
            if ($completed !== null) $set['status'] = $completed ? 'completed' : 'pending';
            $set['updated_at'] = date('Y-m-d H:i:s');
            // --- FIM DO BLOCO ---

            try {
                if (!empty($_SESSION['is_admin'])) {
                    // Admin pode atualizar qualquer tarefa
                    $result = $query->update('tasks', $set, $id)
                        ->execute();
                } else {
                    $result = $query->update('tasks', $set, $id)
                        ->where('user_id', $userId)
                        ->execute();
                }

                if ($result) {
                    $task = $query->from('tasks')->where('id', $id)->fetch();
                    echo json_encode([
                        'success' => true,
                        'message' => 'Tarefa atualizada com sucesso!',
                        'task_title' => $task['title'],
                        'task_status' => $task['status']
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Tarefa não encontrada ou não pertence a você.']);
                }
            } catch (PDOException $e) {
                error_log("Erro ao atualizar tarefa: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao atualizar tarefa: ' . $e->getMessage()]);
            }
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido para esta ação.']);
        }
        break;
    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validação CSRF
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                http_response_code(403); // Forbidden
                echo json_encode(['success' => false, 'message' => 'Erro de segurança (CSRF inválido).']);
                exit;
            }

            $id = $_POST['id'] ?? null;

            if (empty($id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'O ID da tarefa é obrigatório.']);
                exit;
            }

            try {
                $result = $query->deleteFrom('tasks', $id)
                    ->where('user_id', $userId) // Garante que o usuário só delete suas próprias tarefas
                    ->execute();

                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Tarefa excluída com sucesso!']);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Tarefa não encontrada ou não pertence a você.']);
                }
            } catch (PDOException $e) {
                error_log("Erro ao excluir tarefa: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao excluir tarefa.']);
            }
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido para esta ação.']);
        }
        break;

    default:
        http_response_code(400); // Bad Request
        echo json_encode(['success' => false, 'message' => 'Ação de API inválida.']);
        break;
}
