<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <title>Task Manager</title>
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
      <a class="navbar-brand" href="">Task Manager</a>
      <button class="navbar-toggler text-uppercase font-weight-bold bg-primary text-white rounded" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
        Menu
        <i class="fas fa-bars"></i>
      </button>
      <div class="collapse navbar-collapse" id="navbarResponsive">
        <ul class="navbar-nav ms-auto">
          <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item mx-0 mx-lg-1 d-flex align-items-center">
              <span class="badge rounded-pill bg-primary text-white px-3 py-2 me-2" style="font-size:1rem;">
                <i class="fas fa-user-circle me-1"></i>
                <?= htmlspecialchars($_SESSION['user_name']) ?>
              </span>
              <a class="nav-link py-3 px-0 px-lg-3 rounded" href="/logout">Sair</a>
            </li>
          <?php else: ?>
            <li class="nav-item mx-0 mx-lg-1"><a class="nav-link py-3 px-0 px-lg-3 rounded" href="/login">Login</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>