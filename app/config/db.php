<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Envms\FluentPDO\Query;

$dsn = 'mysql:host=mysql;dbname=fluentpdo;charset=utf8';
$user = 'root';
$password = 'root';

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    $query = new Query($pdo);

    return $query;

} catch (PDOException $e) {
    error_log("Database connection failed in db.php: " . $e->getMessage());
    throw new PDOException("Erro ao conectar ao banco de dados: " . $e->getMessage(), (int)$e->getCode());
}