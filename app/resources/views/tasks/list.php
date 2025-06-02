<?php
require_once __DIR__ . '/../../../config/db.php';
include_once __DIR__ . '/../../resources/components/header.php';
require_once __DIR__ . '/../../components/auth.php';
requireLogin();
$tasks = $fluent->from('tasks')
    ->orderBy('id DESC')
    ->fetchAll();
?>

<h1>Lista de Tarefas</h1>

<a href="/controllers/tasks/create.php">Criar Nova Tarefa</a>
<br><br>

<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Título</th>
        <th>Status</th>
        <th>Prioridade</th>
        <th>Data</th>
        <th>Ações</th>
    </tr>
    <?php foreach ($tasks as $task): ?>
    <tr>
        <td><?= htmlspecialchars($task['id']) ?></td>
        <td><?= htmlspecialchars($task['title']) ?></td>
        <td><?= htmlspecialchars($task['status']) ?></td>
        <td><?= htmlspecialchars($task['priority']) ?></td>
        <td><?= htmlspecialchars($task['due_date']) ?></td>
        <td>
            <a href="/controllers/tasks/update.php?id=<?= $task['id'] ?>">Editar</a> |
            <a href="/controllers/tasks/delete.php?id=<?= $task['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir?')">Excluir</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

<?php
include_once __DIR__ . '/../../../resources/components/footer.php';
?>
