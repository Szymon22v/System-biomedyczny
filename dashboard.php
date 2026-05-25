<?php
require 'config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="container">
        <h1>System biomedyczny</h1>
        <nav>
            <span class="user-info"><?= htmlspecialchars($_SESSION['email']) ?></span>
            <a href="change_password.php">Zmień hasło</a>
            <a href="logout.php">Wyloguj</a>
        </nav>
    </div>
</header>
<main>
<div class="container">
    <h2>Panel główny</h2>
    <a href="dokumentacja.pdf" class="doc-link" target="_blank"> Dokumentacja projektu (PDF)</a>
    <div class="menu-grid">
        <a href="measurements.php" class="menu-card">
            <span></span>
            <strong>Pomiary</strong>
            <small>Dodaj i przeglądaj wyniki</small>
        </a>
        <a href="parameters.php" class="menu-card">
            <span></span>
            <strong>Katalog badań</strong>
            <small>Zarządzaj parametrami</small>
        </a>
        <a href="units.php" class="menu-card">
            <span></span>
            <strong>Jednostki</strong>
            <small>Jednostki miar</small>
        </a>
        <a href="statistics.php" class="menu-card">
            <span></span>
            <strong>Statystyki</strong>
            <small>Analiza wyników</small>
        </a>
    </div>
</div>
</main>
</body>
</html>
