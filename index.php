<?php
require 'config.php';

$error = '';
$success = '';
$mode = $_GET['mode'] ?? 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mode === 'register') {
    $email = trim($_POST['email'] ?? '');
    $pass1 = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Nieprawidłowy email.';
    } elseif (strlen($pass1) < 6) {
        $error = 'Hasło musi mieć co najmniej 6 znaków.';
    } elseif ($pass1 !== $pass2) {
        $error = 'Hasła nie są identyczne.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Ten email jest już zajęty.';
        } else {
            $hash = password_hash($pass1, PASSWORD_DEFAULT);
            $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (?, ?)")->execute([$email, $hash]);
            $success = 'Konto utworzone. Możesz się zalogować.';
            $mode = 'login';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mode === 'login') {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $ip    = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $ok = $user && password_verify($pass, $user['password_hash']);
    $pdo->prepare("INSERT INTO login_attempts (email, ip, success) VALUES (?, ?, ?)")->execute([$email, $ip, $ok ? 1 : 0]);
    if ($ok) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email']   = $user['email'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Nieprawidłowy email lub hasło.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mode === 'reset') {
    $email = trim($_POST['email'] ?? '');
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        $pdo->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);
        $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)")->execute([$user['id'], $token, $expires]);
        $success = 'Link do resetu: <a href="reset_password.php?token=' . $token . '">reset_password.php?token=' . $token . '</a>';
    } else {
        $success = 'Jeśli email istnieje, token zostanie wygenerowany.';
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>System biomedyczny</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header>
    <div class="container">
        <h1>System biomedyczny</h1>
    </div>
</header>
<main>
<div class="container">
    <h2>Logowanie</h2>
    <div class="auth-tabs">
        <a href="?mode=login" class="<?= $mode === 'login' ? 'active' : '' ?>">Logowanie</a>
        <a href="?mode=register" class="<?= $mode === 'register' ? 'active' : '' ?>">Rejestracja</a>
        <a href="?mode=reset" class="<?= $mode === 'reset' ? 'active' : '' ?>">Reset hasła</a>
    </div>

    <?php if ($error): ?><div class="msg-error"><?= $error ?></div><?php endif; ?>
    <?php if ($success): ?><div class="msg-success"><?= $success ?></div><?php endif; ?>

    <?php if ($mode === 'login'): ?>
    <div class="form-block">
        <form method="post">
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Hasło</label>
            <input type="password" name="password" required>
            <button type="submit">Zaloguj się</button>
        </form>
    </div>

    <?php elseif ($mode === 'register'): ?>
    <div class="form-block">
        <form method="post">
            <label>Email</label>
            <input type="email" name="email" required>
            <label>Hasło (min. 6 znaków)</label>
            <input type="password" name="password" required>
            <label>Powtórz hasło</label>
            <input type="password" name="password2" required>
            <button type="submit">Zarejestruj się</button>
        </form>
    </div>

    <?php elseif ($mode === 'reset'): ?>
    <div class="form-block">
        <form method="post">
            <label>Email</label>
            <input type="email" name="email" required>
            <button type="submit">Generuj token resetu</button>
        </form>
    </div>
    <?php endif; ?>
</div>
</main>
</body>
</html>
