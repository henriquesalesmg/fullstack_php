<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}
include_once __DIR__ . '/../../components/header.php';
include_once __DIR__ . '/../../components/auth.php';
require_once __DIR__ . '/../../../helpers/cache.php';
$categories = cache_get('task_categories');
if ($categories === false) {
    $categories = $query->from('task_categories')->fetchAll();
    cache_set('task_categories', $categories);
}
$csrfToken = generateCsrfToken(); // Gera o token para usar nos formulários JS
?>

<script>
    window.isAdmin = <?= json_encode($_SESSION['is_admin'] ?? false) ?>;
</script>
<section class="page-section mt-5">
    <div class="container">
        <h2 class="text-center text-secondary mb-0">Tarefas</h2>
        <hr class="star-dark mb-5">
        <div id="statusMessage" class="alert alert-success text-center mx-auto" role="alert" style="display:none; max-width: 600px;"></div>
        <div class="row">
            <div class="col-lg-12">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3>Lista de Tarefas</h3>
                    <button class="btn btn-primary" id="addTaskButton" data-bs-toggle="modal" data-bs-target="#createTaskModal">
                        <i class="fas fa-plus"></i> Nova Tarefa
                    </button>
                </div>
                <small>Para alterar o status da tarefa, clique no botão da coluna status ou edite a tarefa.</small>

                <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                    <div>
                        <label for="filterStatus" class="form-label mb-0 me-2">Status:</label>
                        <select id="filterStatus" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="">Todos</option>
                            <option value="completed">Concluída</option>
                            <option value="pending">Pendente</option>
                        </select>
                    </div>
                    <div>
                        <label for="filterCategory" class="form-label mb-0 me-2">Categoria:</label>
                        <select id="filterCategory" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="">Todas</option>
                            <!-- As opções de categoria podem ser preenchidas via backend ou JS -->
                        </select>
                    </div>
                    <div>
                        <button type="button" id="orderByDate" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-sort"></i> Ordenar por Data
                        </button>
                    </div>
                    <div>
                        <div>
                            <button type="button" id="resetFilters" class="btn btn-outline-secondary btn-sm">
                                Limpar Filtros
                            </button>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table id='tasksTable' class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Categoria</th>
                                <th>Descrição</th>
                                <th>Status</th>
                                <th>Criação</th>
                                <?php if (!empty($_SESSION['is_admin'])): ?>
                                    <th>Usuário</th>
                                <?php endif; ?>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tasks-table-body">
                            <tr>
                                <td colspan="7" class="text-center">Carregando tarefas...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="createTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createTaskModalLabel">Criar Nova Tarefa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createTaskForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="mb-3">
                        <label for="createTitle" class="form-label">Título</label>
                        <input type="text" class="form-control" id="createTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="createCategory" class="form-label">Categoria</label>
                        <select class="form-select" id="createCategory" name="category_id" required>
                            <option value="">Selecione...</option>
                            <!-- Opções preenchidas via JS -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="createDescription" class="form-label">Descrição</label>
                        <textarea class="form-control" id="createDescription" name="description" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Salvar Tarefa</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editTaskModalLabel">Editar Tarefa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editTaskForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <input type="hidden" id="editTaskId" name="id">
                    <div class="mb-3">
                        <label for="editTitle" class="form-label">Título</label>
                        <input type="text" class="form-control" id="editTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="editCategory" class="form-label">Categoria</label>
                        <select class="form-select" id="editCategory" name="category_id" required>
                            <option value="">Selecione...</option>
                            <!-- Opções preenchidas via JS -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="editDescription" class="form-label">Descrição</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="editCompleted" name="completed">
                        <label class="form-check-label" for="editCompleted">
                            Concluída
                        </label>
                    </div>
                    <button type="submit" class="btn btn-success">Atualizar Tarefa</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.10/js/jquery.dataTables.min.js"></script>
<script src="/assets/js/scripts.js"></script>
<script src="/assets/js/tasks.js"></script>
<?php include_once __DIR__ . '/../../components/footer.php'; ?>