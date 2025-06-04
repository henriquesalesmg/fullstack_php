<?php include_once __DIR__ . '/../../components/header.php'; ?>

<section class="page-section mt-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4">Cadastrar</h2>

                <?php if (!empty($_GET['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                <?php endif; ?>
                <?php if (!empty($_GET['success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
                <?php endif; ?>

                <form action="/api/user?action=register" method="POST" id="registerForm" autocomplete="off">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome</label>
                        <input type="text" name="name" id="name" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" name="password" id="password" class="form-control" required minlength="6" autocomplete="new-password">
                        <small class="form-text text-muted">
                            Mínimo 6 caracteres, com pelo menos uma maiúscula, uma minúscula, um número e um caractere especial.
                        </small>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirme a Senha</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6" autocomplete="new-password">
                    </div>
                    <div id="passwordError" class="alert alert-danger d-none"></div>
                    <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
                </form>
                <div class="mt-3 text-center">
                    Já tem uma conta? <a href="/login">Entrar</a>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="/assets/js/password-confirm.js"></script>
<?php include_once __DIR__ . '/../../components/footer.php'; ?>