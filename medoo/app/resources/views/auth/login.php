<?php
include_once __DIR__ . '/../../components/header.php';
include_once __DIR__ . '/../../components/auth.php';
?>

<section class="page-section mt-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4">Login</h2>

                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                <?php endif; ?>
                <?php if (!empty($_GET['success'])): ?>
                    <div class="alert alert-success text-center">
                        <?= htmlspecialchars($_GET['success']) ?>
                    </div>
                <?php endif; ?>

                <form action="/api/user?action=login" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail</label>
                        <input type="email" name="email" id="email" class="form-control" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Senha</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Entrar</button>
                </form>
                <div class="mt-3 text-center">
                    Não tem uma conta? <a href="/register">Registre-se</a>
                </div>
                <div class="mt-3 text-center">
                    <small>
                        <a href="/recover">Recuperar Senha</a>
                    </small>
                </div>

            </div>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../../components/footer.php'; ?>