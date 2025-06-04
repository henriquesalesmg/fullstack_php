<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['recover_user_id'])) {
    header('Location: /login');
    exit;
}
include_once __DIR__ . '/../../components/header.php';
?>

<section class="page-section mt-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4">Nova Senha</h2>
                <?php if (!empty($_GET['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                <?php endif; ?>
                <form action="/api/user?action=reset-password" method="POST" id="resetPasswordForm" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <div class="mb-3">
                        <label for="password" class="form-label">Nova Senha</label>
                        <input type="password" name="password" id="password" class="form-control" required minlength="6">
                        <small class="form-text text-muted">
                            Mínimo 6 caracteres, com pelo menos uma maiúscula, uma minúscula, um número e um caractere especial.
                        </small>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirme a Nova Senha</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6">
                    </div>
                    <div id="passwordError" class="alert alert-danger d-none"></div>
                    <button type="submit" class="btn btn-success w-100">Salvar Nova Senha</button>
                </form>
            </div>
        </div>
    </div>
</section>
<script src="/assets/js/password-confirm.js"></script>
<?php include_once __DIR__ . '/../../components/footer.php'; ?>