<nav>
    <a href="/">Início</a>

    <?php if (isAuthenticated()): ?>
        <a href="/tasks">Tarefas</a>
        <a href="/logout">Sair (<?= htmlspecialchars(currentUserName()) ?>)</a>
    <?php else: ?>
        <a href="/login">Login</a>
    <?php endif; ?>
</nav>
