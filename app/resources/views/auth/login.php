<?php
require_once __DIR__ . '/../../../config/db.php';
include_once __DIR__ . '/../../components/header.php';

session_start();

// Se já estiver logado, redireciona
if (isset($_SESSION['user_id'])) {
    header('Location: /tasks');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header('Location: /tasks');
        exit;
    } else {
        $error = 'Email ou senha inválidos.';
    }
}
?>

<h1>Login</h1>

<?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="post">
    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Senha:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Entrar</button>
</form>

<?php include_once __DIR__ . '/../../components/footer.php'; ?>
