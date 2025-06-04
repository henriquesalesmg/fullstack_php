<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Medoo\Medoo;

$database = [
    'database_type' => 'mysql',
    'database_name' => 'medoo',
    'server'        => 'mysql',
    'username'      => 'root',
    'password'      => 'root',
    'charset'       => 'utf8',
    'option'        => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];

try {
    $medoo = new Medoo($database);
    return $medoo;
} catch (PDOException $e) {
    error_log("Database connection failed in db.php: " . $e->getMessage());
    throw new PDOException("Erro ao conectar ao banco de dados: " . $e->getMessage(), (int)$e->getCode());
}