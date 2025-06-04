<?php
$db = require __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../resources/components/auth.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(['success' => false, 'message' => 'Não autorizado. Faça login para acessar.']);
    exit;
}

header('Content-Type: application/json');

$action = $_GET['action'] ?? null;
$userId = $_SESSION['user_id'];

switch ($action) {
    case 'list':
        try {
            $where = [];
            $status = isset($_GET['status']) ? trim($_GET['status']) : '';
            $category = isset($_GET['category']) ? filter_var($_GET['category'], FILTER_VALIDATE_INT) : null;

            if (empty($_SESSION['is_admin'])) {
                $where['user_id'] = $userId;
            }
            if ($status === 'completed' || $status === 'pending') {
                $where['status'] = $status;
            }
            if (!empty($category)) {
                $where['category_id'] = $category;
            }

            $tasks = $query->select('tasks', [
                '[>]users' => ['user_id' => 'id'],
                '[>]task_categories' => ['category_id' => 'id']
            ], [
                'tasks.id',
                'tasks.title',
                'tasks.description',
                'tasks.status',
                'tasks.due_date',
                'tasks.priority',
                'tasks.created_at',
                'tasks.updated_at',
                'users.name(user_name)',
                'task_categories.name(category)'
            ], $where);

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
                $where = ['id' => $id];
                if (empty($_SESSION['is_admin'])) {
                    $where['user_id'] = $userId;
                }
                $task = $query->get('tasks', '*', $where);
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
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Erro de segurança (CSRF inválido).']);
                exit;
            }

            $title = isset($_POST['title']) ? trim(strip_tags($_POST['title'])) : null;
            $description = isset($_POST['description']) ? trim(strip_tags($_POST['description'])) : null;
            $category_id = isset($_POST['category_id']) ? filter_var($_POST['category_id'], FILTER_VALIDATE_INT) : null;
            $due_date = isset($_POST['due_date']) ? $_POST['due_date'] : null;
            $priority = isset($_POST['priority']) ? $_POST['priority'] : 'medium';

            if (empty($title) || empty($category_id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Título e categoria são obrigatórios.']);
                exit;
            }

            try {
                $values = [
                    'title' => $title,
                    'description' => $description,
                    'category_id' => $category_id,
                    'user_id' => $userId,
                    'due_date' => $due_date,
                    'priority' => $priority,
                    'status' => 'pending',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                $taskId = $query->insert('tasks', $values);

                if ($taskId) {
                    echo json_encode(['success' => true, 'message' => 'Tarefa criada com sucesso!', 'task_id' => $taskId]);
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
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido para esta ação.']);
        }
        break;

    case 'update':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Erro de segurança (CSRF inválido).']);
                exit;
            }

            $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : null;
            $title = isset($_POST['title']) ? trim(strip_tags($_POST['title'])) : null;
            $description = isset($_POST['description']) ? trim(strip_tags($_POST['description'])) : null;
            $category_id = isset($_POST['category_id']) ? filter_var($_POST['category_id'], FILTER_VALIDATE_INT) : null;
            $completed = isset($_POST['completed']) ? (bool)$_POST['completed'] : null;
            $due_date = isset($_POST['due_date']) ? $_POST['due_date'] : null;
            $priority = isset($_POST['priority']) ? $_POST['priority'] : null;

            if (empty($id)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID da tarefa é obrigatório.']);
                exit;
            }

            $set = [];
            if ($title !== null) $set['title'] = $title;
            if ($description !== null) $set['description'] = $description;
            if ($category_id !== null) $set['category_id'] = $category_id;
            if ($completed !== null) $set['status'] = $completed ? 'completed' : 'pending';
            if ($due_date !== null) $set['due_date'] = $due_date;
            if ($priority !== null) $set['priority'] = $priority;
            $set['updated_at'] = date('Y-m-d H:i:s');

            try {
                $where = ['id' => $id];
                if (empty($_SESSION['is_admin'])) {
                    $where['user_id'] = $userId;
                }
                $result = $query->update('tasks', $set, $where);

                if ($result->rowCount() > 0) {
                    $task = $query->get('tasks', '*', ['id' => $id]);
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
            $categories = $query->select('task_categories', '*');
            echo json_encode(['categories' => $categories]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar categorias.']);
        }
        break;

    case 'delete':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
                http_response_code(403);
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
                $where = ['id' => $id];
                if (empty($_SESSION['is_admin'])) {
                    $where['user_id'] = $userId;
                }
                $result = $query->delete('tasks', $where);

                if ($result->rowCount() > 0) {
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
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ação de API inválida.']);
        break;
}