<?php

$config = include __DIR__ . '/.env.php';

$host = $config['DB_HOST'];
$db   = $config['DB_NAME'];
$user = $config['DB_USER'];
$pass = $config['DB_PASS'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
