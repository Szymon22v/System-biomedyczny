<?php
require 'config.php';
requireLogin();
$error = ''; $success = '';
$MAX_PER_USER = 5;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM parameters WHERE created_by=?"); $stmt->execute([$_SESSION['user_id']]); $count = $stmt->fetchColumn();
    if ($count >= $MAX_PER_USER) { $error = "Możesz utworzyć maksymalnie $MAX_PER_USER pozycji."; }
    else {
        $name = trim($_POST['name'] ?? ''); $unit_id = (int)$_POST['unit_id'];
        $norm_min = $_POST['norm_min'] !== '' ? (float)$_POST['norm_min'] : null;
        $norm_max = $_POST['norm_max'] !== '' ? (float)$_POST['norm_max'] : null;
        if ($name && $unit_id) { $pdo->prepare("INSERT INTO parameters (name,unit_id,norm_min,norm_max,created_by) VALUES (?,?,?,?,?)")->execute([$name,$unit_id,$norm_min,$norm_max,$_SESSION['user_id']]); $success = 'Dodano parametr.'; }
        else { $error = 'Wypełnij wymagane pola.'; }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id=(int)$_POST['id']; $name=trim($_POST['name']??''); $unit_id=(int)$_POST['unit_id'];
    $norm_min=$_POST['norm_min']!==''?(float)$_POST['norm_min']:null; $norm_max=$_POST['norm_max']!==''?(float)$_POST['norm_max']:null;
    $pdo->prepare("UPDATE parameters SET name=?,unit_id=?,norm_min=?,norm_max=? WHERE id=?")->execute([$name,$unit_id,$norm_min,$norm_max,$id]); $success='Zaktualizowano.';
}
if (isset($_GET['delete'])) { $pdo->prepare("DELETE FROM parameters WHERE id=?")->execute([(int)$_GET['delete']]); header('Location: parameters.php'); exit; }

$edit_id=(int)($_GET['edit']??0); $edit_row=null;
if ($edit_id) { $stmt=$pdo->prepare("SELECT * FROM parameters WHERE id=?"); $stmt->execute([$edit_id]); $edit_row=$stmt->fetch(PDO::FETCH_ASSOC); }
$units=$pdo->query("SELECT * FROM units ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$params=$pdo->query("SELECT p.*,u.symbol,u.name AS unit_name,us.email FROM parameters p JOIN units u ON p.unit_id=u.id JOIN users us ON p.created_by=us.id ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pl">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Katalog badań</title><link rel="stylesheet" href="style.css"></head>
<body>
<header><div class="container"><h1>System biomedyczny</h1><nav><a href="dashboard.php">← Dashboard</a><a href="logout.php">Wyloguj</a></nav></div></header>
<main><div class="container">
<h2>Katalog badań</h2>
<?php if ($error): ?><div class="msg-error"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="msg-success"><?= $success ?></div><?php endif; ?>
<h3><?= $edit_row ? 'Edytuj parametr' : 'Dodaj parametr' ?></h3>
<?php if (empty($units)): ?>
    <p>Najpierw dodaj jednostki w zakładce <a href="units.php">Jednostki</a>.</p>
<?php else: ?>
<div class="form-block">
<form method="post">
    <?php if ($edit_row): ?><input type="hidden" name="id" value="<?= $edit_row['id'] ?>"><?php endif; ?>
    <label>Nazwa parametru</label><input type="text" name="name" value="<?= htmlspecialchars($edit_row['name']??'') ?>" required>
    <label>Jednostka</label>
    <select name="unit_id" required>
        <?php foreach ($units as $u): ?>
            <option value="<?= $u['id'] ?>" <?= ($edit_row['unit_id']??0)==$u['id']?'selected':'' ?>><?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['symbol']) ?>)</option>
        <?php endforeach; ?>
    </select>
    <label>Norma minimalna (opcjonalnie)</label><input type="number" step="any" name="norm_min" value="<?= $edit_row['norm_min']??'' ?>">
    <label>Norma maksymalna (opcjonalnie)</label><input type="number" step="any" name="norm_max" value="<?= $edit_row['norm_max']??'' ?>">
    <button type="submit" name="<?= $edit_row?'edit':'add' ?>"><?= $edit_row?'Zapisz zmiany':'Dodaj' ?></button>
    <?php if ($edit_row): ?> <a href="parameters.php" class="btn btn-secondary">Anuluj</a><?php endif; ?>
</form>
</div>
<?php endif; ?>
<h3>Lista parametrów</h3>
<table>
    <tr><th>Nazwa</th><th>Jednostka</th><th>Norma min</th><th>Norma max</th><th>Dodał</th><th>Akcje</th></tr>
    <?php foreach ($params as $p): ?>
    <tr>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td><?= htmlspecialchars($p['unit_name']) ?> (<?= htmlspecialchars($p['symbol']) ?>)</td>
        <td><?= $p['norm_min']??'—' ?></td>
        <td><?= $p['norm_max']??'—' ?></td>
        <td><?= htmlspecialchars($p['email']) ?></td>
        <td><a href="?edit=<?= $p['id'] ?>">Edytuj</a><a href="?delete=<?= $p['id'] ?>" class="del" onclick="return confirm('Usunąć?')">Usuń</a></td>
    </tr>
    <?php endforeach; ?>
</table>
</div></main>
</body></html>
