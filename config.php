<?php
$host = 'mysql.agh.edu.pl';
$port = 3306;
$db   = 'TWOJA_BAZA';   // np. mlynek
$user = 'TWOJ_LOGIN';   // np. mlynek
$pass = 'TWOJE_HASLO';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą: " . $e->getMessage());
}

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}
