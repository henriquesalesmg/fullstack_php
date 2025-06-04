<?php
echo "Iniciando processo de seeding de dados para FluentPDO e Medoo...\n";

// Função para popular um banco usando FluentPDO ou Medoo
function seedDatabase($query, $type = 'fluentpdo') {
    $plain_password = 'senha123';
    $hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

    $admin_email = 'admin@admin.com';
    $user_email = 'user@teste.com';

    try {
        // --- SEED DE USUÁRIOS ---
        if ($type === 'fluentpdo') {
            $admin_user = $query->from('users')->where('email', $admin_email)->fetch();
        } else {
            $admin_user = $query->get('users', '*', ['email' => $admin_email]);
        }

        if (!$admin_user) {
            echo "[$type] Inserindo usuários iniciais...\n";
            $users = [
                ['name' => 'Admin', 'email' => $admin_email, 'password' => $hashed_password, 'role' => 'admin'],
                ['name' => 'User Teste', 'email' => $user_email, 'password' => $hashed_password, 'role' => 'user']
            ];
            if ($type === 'fluentpdo') {
                foreach ($users as $user) {
                    $query->insertInto('users')->values($user)->execute();
                }
            } else {
                foreach ($users as $user) {
                    $query->insert('users', $user);
                }
            }
            echo "[$type] Usuários inseridos.\n";
        } else {
            echo "[$type] Usuários já existem. Pulando inserção de usuários.\n";
        }

        // IDs dos usuários
        if ($type === 'fluentpdo') {
            $admin_id = $query->from('users')->where('email', $admin_email)->fetch('id');
            $user_test_id = $query->from('users')->where('email', $user_email)->fetch('id');
        } else {
            $admin_id = $query->get('users', 'id', ['email' => $admin_email]);
            $user_test_id = $query->get('users', 'id', ['email' => $user_email]);
        }

        // --- SEED DE CATEGORIAS ---
        if ($type === 'fluentpdo') {
            $existing_categories_count = $query->from('task_categories')->count();
        } else {
            $existing_categories_count = $query->count('task_categories');
        }

        if ($existing_categories_count === 0) {
            echo "[$type] Inserindo categorias...\n";
            $categories = [
                ['name' => 'Pessoal'],
                ['name' => 'Trabalho'],
                ['name' => 'Estudos']
            ];
            if ($type === 'fluentpdo') {
                foreach ($categories as $cat) {
                    $query->insertInto('task_categories')->values($cat)->execute();
                }
            } else {
                foreach ($categories as $cat) {
                    $query->insert('task_categories', $cat);
                }
            }
            echo "[$type] Categorias inseridas.\n";
        } else {
            echo "[$type] Categorias já existem. Pulando inserção de categorias.\n";
        }

        // IDs das categorias
        if ($type === 'fluentpdo') {
            $cat_pessoal_id = $query->from('task_categories')->where('name', 'Pessoal')->fetch('id');
            $cat_trabalho_id = $query->from('task_categories')->where('name', 'Trabalho')->fetch('id');
            $cat_estudos_id = $query->from('task_categories')->where('name', 'Estudos')->fetch('id');
        } else {
            $cat_pessoal_id = $query->get('task_categories', 'id', ['name' => 'Pessoal']);
            $cat_trabalho_id = $query->get('task_categories', 'id', ['name' => 'Trabalho']);
            $cat_estudos_id = $query->get('task_categories', 'id', ['name' => 'Estudos']);
        }

        // --- SEED DE TAREFAS ---
        if ($type === 'fluentpdo') {
            $existing_admin_tasks_count = $query->from('tasks')->where('user_id', $admin_id)->count();
        } else {
            $existing_admin_tasks_count = $query->count('tasks', ['user_id' => $admin_id]);
        }

        if ($existing_admin_tasks_count === 0) {
            echo "[$type] Inserindo tarefas iniciais...\n";
            $tasks = [
                ['user_id' => $admin_id, 'category_id' => $cat_pessoal_id, 'title' => 'Comprar mantimentos', 'description' => 'Ir ao supermercado e comprar itens básicos', 'status' => 'pending', 'due_date' => '2025-06-05', 'priority' => 'medium'],
                ['user_id' => $admin_id, 'category_id' => $cat_trabalho_id, 'title' => 'Reunião com equipe', 'description' => 'Discutir progresso do projeto X', 'status' => 'pending', 'due_date' => '2025-06-04', 'priority' => 'high'],
                ['user_id' => $admin_id, 'category_id' => $cat_estudos_id, 'title' => 'Estudar Docker', 'description' => 'Concluir o módulo de containers', 'status' => 'pending', 'due_date' => '2025-06-06', 'priority' => 'medium'],
                ['user_id' => $admin_id, 'category_id' => $cat_pessoal_id, 'title' => 'Passear com o cachorro', 'description' => 'Levar o Max para o parque', 'status' => 'completed', 'due_date' => '2025-06-01', 'priority' => 'low'],
                ['user_id' => $admin_id, 'category_id' => $cat_trabalho_id, 'title' => 'Enviar relatório', 'description' => 'Finalizar e enviar o relatório mensal', 'status' => 'pending', 'due_date' => '2025-06-03', 'priority' => 'high'],

                // User Teste
                ['user_id' => $user_test_id, 'category_id' => $cat_pessoal_id, 'title' => 'Lavar o carro', 'description' => 'Dar uma geral no carro antes da viagem', 'status' => 'pending', 'due_date' => '2025-06-07', 'priority' => 'low'],
                ['user_id' => $user_test_id, 'category_id' => $cat_trabalho_id, 'title' => 'Revisar cronograma', 'description' => 'Verificar tarefas da sprint atual', 'status' => 'completed', 'due_date' => '2025-06-01', 'priority' => 'medium'],
                ['user_id' => $user_test_id, 'category_id' => $cat_estudos_id, 'title' => 'Estudar PHP', 'description' => 'Estudar classes e namespaces', 'status' => 'pending', 'due_date' => '2025-06-06', 'priority' => 'medium'],
                ['user_id' => $user_test_id, 'category_id' => $cat_trabalho_id, 'title' => 'Deploy da API', 'description' => 'Realizar deploy no ambiente de produção', 'status' => 'pending', 'due_date' => '2025-06-03', 'priority' => 'high'],
                ['user_id' => $user_test_id, 'category_id' => $cat_pessoal_id, 'title' => 'Pagar contas', 'description' => 'Contas de luz e internet', 'status' => 'pending', 'due_date' => '2025-06-02', 'priority' => 'high']
            ];
            if ($type === 'fluentpdo') {
                foreach ($tasks as $task) {
                    $query->insertInto('tasks')->values($task)->execute();
                }
            } else {
                foreach ($tasks as $task) {
                    $query->insert('tasks', $task);
                }
            }
            echo "[$type] Tarefas inseridas.\n";
        } else {
            echo "[$type] Tarefas já existem. Pulando inserção de tarefas.\n";
        }

    } catch (Exception $e) {
        echo "[$type] Erro durante o seeding: " . $e->getMessage() . "\n";
        exit(1);
    }
}

// --- SEED PARA FLUENTPDO ---
echo "Populando banco fluentpdo...\n";
$query_fluentpdo = require __DIR__ . '/../../fluentpdo/app/config/db.php';
seedDatabase($query_fluentpdo, 'fluentpdo');

// --- SEED PARA MEDOO ---
echo "Populando banco medoo...\n";
require_once __DIR__ . '/../../medoo/vendor/autoload.php';
use Medoo\Medoo;
$query_medoo = new Medoo([
    'database_type' => 'mysql',
    'database_name' => 'medoo',
    'server'        => 'mysql',
    'username'      => 'root',
    'password'      => 'root',
    'charset'       => 'utf8',
    'option'        => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
]);
seedDatabase($query_medoo, 'medoo');

echo "Processo de seeding concluído para ambos os bancos.\n";