<?php

echo "Iniciando processo de seeding de dados...\n";

$query = require __DIR__ . '/../../app/config/db.php';

$plain_password = 'senha123';
$hashed_password = password_hash($plain_password, PASSWORD_DEFAULT);

$admin_email = 'admin@admin.com';
$user_email = 'user@teste.com';

try {
    // --- SEED DE USUÁRIOS ---
    // Verifica se o usuário admin já existe (garante que o seed de usuários rode apenas uma vez)
    $admin_user = $query->from('users')->where('email', $admin_email)->fetch();

    if (!$admin_user) {
        echo "Inserindo usuários iniciais...\n";
        // Inserção de usuários
        $query->insertInto('users', [
            ['name' => 'Admin', 'email' => $admin_email, 'password' => $hashed_password, 'role' => 'admin'],
            ['name' => 'User Teste', 'email' => $user_email, 'password' => $hashed_password, 'role' => 'user']
        ])->execute();
        echo "Usuários inseridos.\n";
    } else {
        echo "Usuários já existem. Pulando inserção de usuários.\n";
    }

    // Recupere os IDs dos usuários para inserir as tarefas
    $admin_id = $query->from('users')->where('email', $admin_email)->fetch('id');
    $user_test_id = $query->from('users')->where('email', $user_email)->fetch('id');

    // --- SEED DE CATEGORIAS ---
    // Verifica se as categorias já existem (ex: pelo nome, para idempotência)
    $existing_categories_count = $query->from('task_categories')->count();

    if ($existing_categories_count === 0) {
        echo "Inserindo categorias...\n";
        // Inserção de categorias
        $query->insertInto('task_categories', [
            ['name' => 'Pessoal'],
            ['name' => 'Trabalho'],
            ['name' => 'Estudos']
        ])->execute();
        echo "Categorias inseridas.\n";
    } else {
        echo "Categorias já existem. Pulando inserção de categorias.\n";
    }

    // Recupere os IDs das categorias para inserir as tarefas
    $cat_pessoal_id = $query->from('task_categories')->where('name', 'Pessoal')->fetch('id');
    $cat_trabalho_id = $query->from('task_categories')->where('name', 'Trabalho')->fetch('id');
    $cat_estudos_id = $query->from('task_categories')->where('name', 'Estudos')->fetch('id');

    // --- SEED DE TAREFAS ---
    // Verifica se as tarefas já existem (ex: verificando se há tarefas para o admin)
    $existing_admin_tasks_count = $query->from('tasks')->where('user_id', $admin_id)->count();

    if ($existing_admin_tasks_count === 0) {
        echo "Inserindo tarefas iniciais...\n";
        // Inserção de tarefas para Admin (ID 1)
        $query->insertInto('tasks', [
            ['user_id' => $admin_id, 'category_id' => $cat_pessoal_id, 'title' => 'Comprar mantimentos', 'description' => 'Ir ao supermercado e comprar itens básicos', 'status' => 'pending', 'due_date' => '2025-06-05', 'priority' => 'medium'],
            ['user_id' => $admin_id, 'category_id' => $cat_trabalho_id, 'title' => 'Reunião com equipe', 'description' => 'Discutir progresso do projeto X', 'status' => 'pending', 'due_date' => '2025-06-04', 'priority' => 'high'],
            ['user_id' => $admin_id, 'category_id' => $cat_estudos_id, 'title' => 'Estudar Docker', 'description' => 'Concluir o módulo de containers', 'status' => 'pending', 'due_date' => '2025-06-06', 'priority' => 'medium'],
            ['user_id' => $admin_id, 'category_id' => $cat_pessoal_id, 'title' => 'Passear com o cachorro', 'description' => 'Levar o Max para o parque', 'status' => 'completed', 'due_date' => '2025-06-01', 'priority' => 'low'],
            ['user_id' => $admin_id, 'category_id' => $cat_trabalho_id, 'title' => 'Enviar relatório', 'description' => 'Finalizar e enviar o relatório mensal', 'status' => 'pending', 'due_date' => '2025-06-03', 'priority' => 'high'],

            // Inserção de tarefas para User Teste (ID 2)
            ['user_id' => $user_test_id, 'category_id' => $cat_pessoal_id, 'title' => 'Lavar o carro', 'description' => 'Dar uma geral no carro antes da viagem', 'status' => 'pending', 'due_date' => '2025-06-07', 'priority' => 'low'],
            ['user_id' => $user_test_id, 'category_id' => $cat_trabalho_id, 'title' => 'Revisar cronograma', 'description' => 'Verificar tarefas da sprint atual', 'status' => 'completed', 'due_date' => '2025-06-01', 'priority' => 'medium'],
            ['user_id' => $user_test_id, 'category_id' => $cat_estudos_id, 'title' => 'Estudar PHP', 'description' => 'Estudar classes e namespaces', 'status' => 'pending', 'due_date' => '2025-06-06', 'priority' => 'medium'],
            ['user_id' => $user_test_id, 'category_id' => $cat_trabalho_id, 'title' => 'Deploy da API', 'description' => 'Realizar deploy no ambiente de produção', 'status' => 'pending', 'due_date' => '2025-06-03', 'priority' => 'high'],
            ['user_id' => $user_test_id, 'category_id' => $cat_pessoal_id, 'title' => 'Pagar contas', 'description' => 'Contas de luz e internet', 'status' => 'pending', 'due_date' => '2025-06-02', 'priority' => 'high']
        ])->execute();
        echo "Tarefas inseridas.\n";
    } else {
        echo "Tarefas já existem. Pulando inserção de tarefas.\n";
    }


} catch (PDOException $e) {
    echo "Erro durante o seeding: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Processo de seeding concluído.\n";

?>