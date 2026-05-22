<?php
require 'config.php';
$token=$_GET['token']??''; $error=''; $success='';
$stmt=$pdo->prepare("SELECT pr.*,u.email FROM password_resets pr JOIN users u ON pr.user_id=u.id WHERE pr.token=? AND pr.expires_at>NOW()");
$stmt->execute([$token]); $reset=$stmt->fetch(PDO::FETCH_ASSOC);
if (!$reset) die("Nieprawidłowy lub wygasły token. <a href='index.php'>Wróć</a>");
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $pass1=$_POST['password']??''; $pass2=$_POST['password2']??'';
    if (strlen($pass1)<6) { $error='Hasło musi mieć co najmniej 6 znaków.'; }
    elseif ($pass1!==$pass2) { $error='Hasła nie są identyczne.'; }
    else { $hash=password_hash($pass1,PASSWORD_DEFAULT); $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash,$reset['user_id']]); $pdo->prepare("DELETE FROM password_resets WHERE token=?")->execute([$token]); $success='Hasło zmienione. <a href="index.php">Zaloguj się</a>'; }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Reset hasła</title><link rel="stylesheet" href="style.css"></head>
<body>
<header><div class="container"><h1>System biomedyczny</h1></div></header>
<main><div class="container">
<h2>Nowe hasło</h2>
<p style="margin-bottom:20px;color:#555">Konto: <?= htmlspecialchars($reset['email']) ?></p>
<?php if ($error): ?><div class="msg-error"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="msg-success"><?= $success ?></div><?php else: ?>
<div class="form-block">
<form method="post">
    <label>Nowe hasło</label><input type="password" name="password" required>
    <label>Powtórz hasło</label><input type="password" name="password2" required>
    <button type="submit">Ustaw nowe hasło</button>
</form>
</div>
<?php endif; ?>
</div></main>
</body></html>
