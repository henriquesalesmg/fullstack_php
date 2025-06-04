<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (isset($_SESSION['role'])) {
  error_log('ROLE: ' . $_SESSION['role']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>Task Manager (FluentPDO)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Font Awesome icons (free version)-->
  <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
  <!-- Google fonts-->
  <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
  <link href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.10/css/jquery.dataTables.min.css">
  <!-- Core theme CSS (includes Bootstrap)-->
  <link rel="stylesheet" href="/assets/css/styles.css">
</head>

<body>
  <nav class="navbar navbar-expand-lg bg-secondary text-uppercase fixed-top" id="mainNav">
    <div class="container">
      <a class="navbar-brand" href="/">Task Manager (FluentPDO)</a>
      <button class="navbar-toggler text-uppercase font-weight-bold bg-primary text-white rounded" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        Menu
        <i class="fas fa-bars"></i>
      </button>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav ms-auto">
          <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item mx-0 mx-lg-1">
              <a class="nav-link py-3 px-0 px-lg-3 rounded" href="/tasks">
                <i class="fas fa-users-cog me-1"></i>Tarefas
              </a>
            </li>
            <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
              <li class="nav-item mx-0 mx-lg-1">
                <a class="nav-link py-3 px-0 px-lg-3 rounded" href="/admin/users">
                  <i class="fas fa-users-cog me-1"></i>Gerenciar Usuários
                </a>
              </li>
            <?php endif; ?>
            <li class="nav-item mx-0 mx-lg-1 dropdown d-flex align-items-center">
              <a class="nav-link dropdown-toggle py-3 px-0 px-lg-3 rounded d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="badge rounded-pill bg-primary text-white px-3 py-2 me-2" style="font-size:1rem;" data-username="<?= htmlspecialchars($_SESSION['user_name']) ?>">
                  <i class="fas fa-user-circle me-1"></i>
                  <?= htmlspecialchars($_SESSION['user_name']) ?>
                </span>
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li>
                  <a class="dropdown-item" href="#" id="openSettingsModal">
                    <i class="fas fa-cog me-2"></i>Configurações
                  </a>
                </li>
                <li>
                  <a class="dropdown-item" href="/logout">
                    <i class="fas fa-sign-out-alt me-2"></i>Sair
                  </a>
                </li>
              </ul>
            </li>
          <?php else: ?>
            <li class="nav-item mx-0 mx-lg-1">
              <a class="nav-link py-3 px-0 px-lg-3 rounded" href="/login">Login</a>
            </li>
            <li class="nav-item mx-0 mx-lg-1">
              <a class="nav-link py-3 px-0 px-lg-3 rounded" href="/register">Cadastrar</a>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Modal de Configurações do Usuário -->
  <div class="modal fade" id="settingsModal" tabindex="-1" aria-labelledby="settingsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form class="modal-content" id="settingsForm" autocomplete="off">
        <div class="modal-header">
          <h5 class="modal-title" id="settingsModalLabel">Configurações da Conta</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="settingsName" class="form-label">Nome</label>
            <input type="text" class="form-control" id="settingsName" name="name" value="" required>
          </div>
          <div class="mb-3">
            <label for="settingsPassword" class="form-label">Nova Senha</label>
            <input type="password" class="form-control" id="settingsPassword" name="password" minlength="6" autocomplete="new-password" placeholder="Deixe em branco para não alterar">
            <small class="form-text text-muted">
              Mínimo 6 caracteres, com pelo menos uma maiúscula, uma minúscula, um número e um caractere especial.
            </small>
          </div>
          <div id="settingsError" class="alert alert-danger d-none"></div>
          <div id="settingsSuccess" class="alert alert-success d-none"></div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Salvar</button>
        </div>
      </form>
    </div>
  </div>