<?php
require 'config.php';
requireLogin();
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $parameter_id=(int)$_POST['parameter_id']; $value=(float)$_POST['value']; $measured_at=$_POST['measured_at']??'';
    if ($parameter_id && $measured_at) { $pdo->prepare("INSERT INTO measurements (user_id,parameter_id,value,measured_at) VALUES (?,?,?,?)")->execute([$_SESSION['user_id'],$parameter_id,$value,$measured_at]); $success='Dodano pomiar.'; }
    else { $error='Wypełnij wszystkie pola.'; }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit'])) {
    $id=(int)$_POST['id']; $value=(float)$_POST['value']; $measured_at=$_POST['measured_at']??''; $parameter_id=(int)$_POST['parameter_id'];
    $pdo->prepare("UPDATE measurements SET value=?,measured_at=?,parameter_id=? WHERE id=? AND user_id=?")->execute([$value,$measured_at,$parameter_id,$id,$_SESSION['user_id']]); $success='Zaktualizowano pomiar.';
}
if (isset($_GET['delete'])) { $pdo->prepare("DELETE FROM measurements WHERE id=? AND user_id=?")->execute([(int)$_GET['delete'],$_SESSION['user_id']]); header('Location: measurements.php'); exit; }

$edit_id=(int)($_GET['edit']??0); $edit_row=null;
if ($edit_id) { $stmt=$pdo->prepare("SELECT * FROM measurements WHERE id=? AND user_id=?"); $stmt->execute([$edit_id,$_SESSION['user_id']]); $edit_row=$stmt->fetch(PDO::FETCH_ASSOC); }
$params=$pdo->query("SELECT p.*,u.symbol FROM parameters p JOIN units u ON p.unit_id=u.id ORDER BY p.name")->fetchAll(PDO::FETCH_ASSOC);
$stmt=$pdo->prepare("SELECT m.*,p.name AS param_name,u.symbol FROM measurements m JOIN parameters p ON m.parameter_id=p.id JOIN units u ON p.unit_id=u.id WHERE m.user_id=? ORDER BY m.measured_at DESC");
$stmt->execute([$_SESSION['user_id']]); $measurements=$stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pl">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pomiary</title><link rel="stylesheet" href="style.css"></head>
<body>
<header><div class="container"><h1>System biomedyczny</h1><nav><a href="dashboard.php">← Dashboard</a><a href="logout.php">Wyloguj</a></nav></div></header>
<main><div class="container">
<h2>Pomiary</h2>
<?php if ($error): ?><div class="msg-error"><?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="msg-success"><?= $success ?></div><?php endif; ?>
<h3><?= $edit_row?'Edytuj pomiar':'Dodaj pomiar' ?></h3>
<?php if (empty($params)): ?>
    <p>Brak parametrów. Dodaj je w <a href="parameters.php">Katalogu badań</a>.</p>
<?php else: ?>
<div class="form-block">
<form method="post">
    <?php if ($edit_row): ?><input type="hidden" name="id" value="<?= $edit_row['id'] ?>"><?php endif; ?>
    <label>Parametr</label>
    <select name="parameter_id" required>
        <?php foreach ($params as $p): ?>
            <option value="<?= $p['id'] ?>" <?= ($edit_row['parameter_id']??0)==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['symbol']) ?>)</option>
        <?php endforeach; ?>
    </select>
    <label>Wartość</label><input type="number" step="any" name="value" value="<?= $edit_row['value']??'' ?>" required>
    <label>Data i czas pomiaru</label><input type="datetime-local" name="measured_at" value="<?= $edit_row?date('Y-m-d\TH:i',strtotime($edit_row['measured_at'])):date('Y-m-d\TH:i') ?>" required>
    <button type="submit" name="<?= $edit_row?'edit':'add' ?>"><?= $edit_row?'Zapisz zmiany':'Dodaj pomiar' ?></button>
    <?php if ($edit_row): ?> <a href="measurements.php" class="btn btn-secondary">Anuluj</a><?php endif; ?>
</form>
</div>
<?php endif; ?>
<h3>Twoje pomiary</h3>
<?php if (empty($measurements)): ?>
    <p>Brak pomiarów.</p>
<?php else: ?>
<table>
    <tr><th>Parametr</th><th>Wartość</th><th>Data pomiaru</th><th>Akcje</th></tr>
    <?php foreach ($measurements as $m): ?>
    <tr>
        <td><?= htmlspecialchars($m['param_name']) ?></td>
        <td><?= $m['value'] ?> <?= htmlspecialchars($m['symbol']) ?></td>
        <td><?= $m['measured_at'] ?></td>
        <td><a href="?edit=<?= $m['id'] ?>">Edytuj</a><a href="?delete=<?= $m['id'] ?>" class="del" onclick="return confirm('Usunąć?')">Usuń</a></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
</div></main>
</body></html>
