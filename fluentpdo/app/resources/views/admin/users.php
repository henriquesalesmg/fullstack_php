<?php include_once __DIR__ . '/../../components/header.php';
if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    $redirect = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '/unauthorized';
    header('Location: ' . $redirect);
    exit;
}
?>

<section class="page-section mt-5">
    <div class="container">
        <h2 class="text-center text-secondary mb-4">Gerenciar Usuários</h2>
        <div class="mb-3 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" id="addUserBtn">
                <i class="fas fa-user-plus"></i> Novo Usuário
            </button>
        </div>
        <div class="table-responsive">
            <table id="usersTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfil</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Preenchido via JS -->
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- Modal de Adição/Edição -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content" id="userForm">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Novo Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="userId" name="id">
                <div class="mb-3">
                    <label for="userName" class="form-label">Nome</label>
                    <input type="text" class="form-control" id="userName" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="userEmail" class="form-label">E-mail</label>
                    <input type="email" class="form-control" id="userEmail" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="userPassword" class="form-label">Senha</label>
                    <input type="password" class="form-control" id="userPassword" name="password">
                    <small class="form-text text-muted">Preencha apenas para alterar a senha.</small>
                </div>
                <div class="mb-3">
                    <label for="userRole" class="form-label">Perfil</label>
                    <select class="form-select" id="userRole" name="role" required>
                        <option value="user">Usuário</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Salvar</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/assets/js/admin-users.js"></script>
<?php include_once __DIR__ . '/../../components/footer.php'; ?>