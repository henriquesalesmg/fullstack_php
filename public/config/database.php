<?php
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $db_host = 'mysql';  
    $db_name = 'task_manager';
    $db_user = 'root';
    $db_pass = 'root';     
    $db_charset = 'utf8mb4';

    // Conexão PDO
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=$db_charset",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    $fluent = new Envms\FluentPDO\Query($pdo);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Database connection failed',
        'message' => $e->getMessage()
    ]);
    exit;
}
?>
