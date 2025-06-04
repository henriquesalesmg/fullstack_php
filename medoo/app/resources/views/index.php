<?php include_once __DIR__ . '/../components/header.php'; ?>

<script>
    window.isAdmin = <?= json_encode($_SESSION['is_admin'] ?? false) ?>;
</script>

<header class="masthead bg-warning text-white text-center">
    <div class="container d-flex align-items-center flex-column">
        <img class="masthead-avatar mb-5" src="/logo.png" alt="..." />

        <h1 class="masthead-heading text-uppercase mb-0">Gerenciamento de Tarefas</h1>

        <div class="divider-custom divider-light">
            <div class="divider-custom-line"></div>
            <div class="divider-custom-icon"><i class="fas fa-star"></i></div>
            <div class="divider-custom-line"></div>
        </div>

        <p class="masthead-subheading font-weight-light mb-0">Acesse o sistema com seu Login e Senha. </p>
        <a href="/login" class="btn btn-lg btn-outline-light mt-4 shadow-sm px-5 py-2 fw-bold">
            <i class="fas fa-sign-in-alt me-2"></i> Acessar
        </a>
    </div>
</header>
<script src="/assets/js/scripts.js"></script>
<?php include_once __DIR__ . '/../components/footer.php'; ?>