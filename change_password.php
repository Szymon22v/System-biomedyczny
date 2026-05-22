<?php
require 'config.php';
requireLogin();
$error=''; $success='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $old=$_POST['old_password']??''; $new1=$_POST['new_password']??''; $new2=$_POST['new_password2']??'';
    $stmt=$pdo->prepare("SELECT password_hash FROM users WHERE id=?"); $stmt->execute([$_SESSION['user_id']]); $user=$stmt->fetch(PDO::FETCH_ASSOC);
    if (!password_verify($old,$user['password_hash'])) { $error='Stare hasło jest nieprawidłowe.'; }
    elseif (strlen($new1)<6) { $error='Nowe hasło musi mieć co najmniej 6 znaków.'; }
    elseif ($new1!==$new2) { $error='Nowe hasła nie są identyczne.'; }
    else { $hash=password_hash($new1,PASSWORD_DEFAULT); $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash,$_SESSION['user_id']]); $success='Hasło zostało zmienione.'; }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Zmień hasło</title><link rel="stylesheet" href="style.css"></head>
<body>
<header><div class="container"><h1>System biomedyczny</h1><nav><a href="dashboard.php">← Dashboard</a><a href="logout.php">Wyloguj</a></nav></div></header>
<main><div class="container">
<h2>Zmiana hasła</h2>
<?php if ($error): ?><div class="msg-error"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="msg-success"><?= $success ?></div><?php endif; ?>
<div class="form-block">
<form method="post">
    <label>Stare hasło</label><input type="password" name="old_password" required>
    <label>Nowe hasło</label><input type="password" name="new_password" required>
    <label>Powtórz nowe hasło</label><input type="password" name="new_password2" required>
    <button type="submit">Zmień hasło</button>
</form>
</div>
</div></main>
</body></html>
