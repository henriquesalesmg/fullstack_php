<?php
include_once __DIR__ . '/../../components/verify_csrf.php';
include_once __DIR__ . '/../../components/header.php'; ?>

<section class="page-section mt-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center mb-4">Recuperar Senha</h2>
                <?php if (!empty($_GET['error'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
                <?php endif; ?>
                <form action="/api/user?action=recover" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    <div class="mb-3">
                        <label for="email" class="form-label">E-mail cadastrado</label>
                        <input type="email" name="email" id="email" class="form-control" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome cadastrado</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Verificar</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../../components/footer.php'; ?>