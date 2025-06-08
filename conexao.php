<?php
$host = "localhost";
$db = "guardiao";
$user = "adminphp";
$pass = "1234";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
?>

