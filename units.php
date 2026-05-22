<?php
require 'config.php';
requireLogin();
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $name = trim($_POST['name'] ?? ''); $symbol = trim($_POST['symbol'] ?? '');
    if ($name && $symbol) {
        $pdo->prepare("INSERT INTO units (name, symbol, created_by) VALUES (?, ?, ?)")->execute([$name, $symbol, $_SESSION['user_id']]);
        $success = 'Dodano jednostkę.';
    } else { $error = 'Wypełnij wszystkie pola.'; }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id = (int)$_POST['id']; $name = trim($_POST['name'] ?? ''); $symbol = trim($_POST['symbol'] ?? '');
    if ($name && $symbol) { $pdo->prepare("UPDATE units SET name=?, symbol=? WHERE id=?")->execute([$name, $symbol, $id]); $success = 'Zaktualizowano.'; }
}
if (isset($_GET['delete'])) { $pdo->prepare("DELETE FROM units WHERE id=?")->execute([(int)$_GET['delete']]); header('Location: units.php'); exit; }

$edit_id = (int)($_GET['edit'] ?? 0); $edit_row = null;
if ($edit_id) { $stmt = $pdo->prepare("SELECT * FROM units WHERE id=?"); $stmt->execute([$edit_id]); $edit_row = $stmt->fetch(PDO::FETCH_ASSOC); }
$units = $pdo->query("SELECT u.*, us.email FROM units u JOIN users us ON u.created_by=us.id ORDER BY u.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pl">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Jednostki</title><link rel="stylesheet" href="style.css"></head>
<body>
<header><div class="container"><h1>System biomedyczny</h1><nav><a href="dashboard.php">← Dashboard</a><a href="logout.php">Wyloguj</a></nav></div></header>
<main><div class="container">
<h2>Jednostki</h2>
<?php if ($error): ?><div class="msg-error"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="msg-success"><?= $success ?></div><?php endif; ?>
<h3><?= $edit_row ? 'Edytuj jednostkę' : 'Dodaj jednostkę' ?></h3>
<div class="form-block">
<form method="post">
    <?php if ($edit_row): ?><input type="hidden" name="id" value="<?= $edit_row['id'] ?>"><?php endif; ?>
    <label>Nazwa</label><input type="text" name="name" value="<?= htmlspecialchars($edit_row['name'] ?? '') ?>" required>
    <label>Symbol</label><input type="text" name="symbol" value="<?= htmlspecialchars($edit_row['symbol'] ?? '') ?>" required>
    <button type="submit" name="<?= $edit_row ? 'edit' : 'add' ?>"><?= $edit_row ? 'Zapisz zmiany' : 'Dodaj' ?></button>
    <?php if ($edit_row): ?> <a href="units.php" class="btn btn-secondary">Anuluj</a><?php endif; ?>
</form>
</div>
<h3>Lista jednostek</h3>
<table>
    <tr><th>Nazwa</th><th>Symbol</th><th>Dodał</th><th>Akcje</th></tr>
    <?php foreach ($units as $u): ?>
    <tr>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= htmlspecialchars($u['symbol']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td>
            <a href="?edit=<?= $u['id'] ?>">Edytuj</a>
            <a href="?delete=<?= $u['id'] ?>" class="del" onclick="return confirm('Usunąć?')">Usuń</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
</div></main>
</body></html>
