<?php
$query = require __DIR__ . '/../config/db.php';
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

switch ($action) {
    case 'list':
        try {
            // Sanitização dos filtros
            $status = isset($_GET['status']) ? trim($_GET['status']) : '';
            $category = isset($_GET['category']) ? filter_var($_GET['category'], FILTER_VALIDATE_INT) : '';

            if (!empty($_SESSION['is_admin'])) {
                $tasksQuery = $query->from('tasks')
                    ->select('tasks.*, users.name AS user_name, task_categories.name AS category')
                    ->leftJoin('users ON users.id = tasks.user_id')
                    ->leftJoin('task_categories ON task_categories.id = tasks.category_id');
            } else {
                $tasksQuery = $query->from('tasks')
                    ->select('tasks.*, users.name AS user_name, task_categories.name AS category')
                    ->leftJoin('users ON users.id = tasks.user_id')
                    ->leftJoin('task_categories ON task_categories.id = tasks.category_id')
                    ->where('tasks.user_id', $userId);
            }

            if ($status === 'completed' || $status === 'pending') {
                $tasksQuery->where('tasks.status', $status);
            }
            if (!empty($category)) {
                $tasksQuery->where('tasks.category_id', $category);
            }

            $tasks = $tasksQuery->fetchAll();
            echo json_encode(['tasks' => $tasks]);
        } catch (PDOException $e) {
            error_log("Erro ao listar tarefas: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao listar tarefas.']);
        }
        break;

    case 'edit':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
            if (empty($id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID da tarefa é obrigatório.']);
                exit;
            }
            try {
                if (!empty($_SESSION['is_admin'])) {
                    $task = $query->from('tasks')->where('id', $id)->fetch();
                } else {
                    $task = $query->from('tasks')->where('id', $id)->where('user_id', $userId)->fetch();
                }
                if ($task) {
                    echo json_encode(['success' => true, 'task' => $task]);
                } else {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Tarefa não encontrada ou não pertence a você.']);
                }
            } catch (PDOException $e) {
                error_log("Erro ao buscar tarefa: " . $e->getMessage());
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Erro ao buscar tarefa.']);
            }
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido para esta ação.']);
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

            // Sanitização dos dados recebidos
            $title = isset($_POST['title']) ? trim(strip_tags($_POST['title'])) : null;
            $description = isset($_POST['description']) ? trim(strip_tags($_POST['description'])) : null;
            $category_id = isset($_POST['category_id']) ? filter_var($_POST['category_id'], FILTER_VALIDATE_INT) : null;

            if (empty($title) || empty($category_id)) {
                http_response_code(400); // Bad Request
                echo json_encode(['success' => false, 'message' => 'Título e categoria são obrigatórios.']);
                exit;
            }

            try {
                $values = [
                    'title' => $title,
                    'description' => $description,
                    'category_id' => $category_id,
                    'user_id' => $userId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $result = $query->insertInto('tasks', $values)->execute();

                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Tarefa criada com sucesso!', 'task_id' => $query->getPdo()->lastInsertId()]);
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

            $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : null;
            $title = isset($_POST['title']) ? trim(strip_tags($_POST['title'])) : null;
            $description = isset($_POST['description']) ? trim(strip_tags($_POST['description'])) : null;
            $category_id = isset($_POST['category_id']) ? filter_var($_POST['category_id'], FILTER_VALIDATE_INT) : null;
            $completed = isset($_POST['completed']) ? (bool)$_POST['completed'] : null;

            if (empty($id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID da tarefa é obrigatório.']);
                exit;
            }

            // Só exige título/categoria se for atualização de título/descrição/categoria/status
            if ($title === null && $completed === null && $category_id === null && $description === null) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Nada para atualizar.']);
                exit;
            }

            $set = [];
            if ($title !== null) $set['title'] = $title;
            if ($description !== null) $set['description'] = $description;
            if ($category_id !== null) $set['category_id'] = $category_id;
            if ($completed !== null) $set['status'] = $completed ? 'completed' : 'pending';
            $set['updated_at'] = date('Y-m-d H:i:s');

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

    case 'categories':
        try {
            $categories = $query->from('task_categories')->fetchAll();
            echo json_encode(['categories' => $categories]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar categorias.']);
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

            $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : null;

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