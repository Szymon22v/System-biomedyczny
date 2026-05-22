<?php
require 'config.php';
requireLogin();
$params=$pdo->query("SELECT p.*,u.symbol FROM parameters p JOIN units u ON p.unit_id=u.id ORDER BY p.name")->fetchAll(PDO::FETCH_ASSOC);
$stats=null; $selected=null; $data=[];
$date_from=$_GET['date_from']??date('Y-m-d',strtotime('-30 days'));
$date_to=$_GET['date_to']??date('Y-m-d');
$param_id=(int)($_GET['parameter_id']??0);
if ($param_id) {
    foreach ($params as $p) { if ($p['id']==$param_id) { $selected=$p; break; } }
    $stmt=$pdo->prepare("SELECT value,measured_at FROM measurements WHERE user_id=? AND parameter_id=? AND measured_at BETWEEN ? AND ? ORDER BY measured_at ASC");
    $stmt->execute([$_SESSION['user_id'],$param_id,$date_from.' 00:00:00',$date_to.' 23:59:59']); $data=$stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($data) {
        $values=array_column($data,'value');
        $stats=['count'=>count($values),'min'=>min($values),'max'=>max($values),'avg'=>round(array_sum($values)/count($values),2)];
        if ($selected['norm_min']!==null||$selected['norm_max']!==null) {
            $stats['overcount']=count(array_filter($values,function($v) use ($selected) {
                return ($selected['norm_min']!==null&&$v<$selected['norm_min'])||($selected['norm_max']!==null&&$v>$selected['norm_max']);
            }));
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Statystyki</title><link rel="stylesheet" href="style.css"></head>
<body>
<header><div class="container"><h1>System biomedyczny</h1><nav><a href="dashboard.php">← Dashboard</a><a href="logout.php">Wyloguj</a></nav></div></header>
<main><div class="container">
<h2>Statystyki</h2>
<div class="form-block" style="max-width:600px">
<form method="get">
    <label>Parametr</label>
    <select name="parameter_id">
        <option value="">— wybierz parametr —</option>
        <?php foreach ($params as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $param_id==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['symbol']) ?>)</option>
        <?php endforeach; ?>
    </select>
    <label>Od</label><input type="date" name="date_from" value="<?= $date_from ?>">
    <label>Do</label><input type="date" name="date_to" value="<?= $date_to ?>">
    <button type="submit">Pokaż statystyki</button>
</form>
</div>

<?php if ($param_id && $selected): ?>
<h3><?= htmlspecialchars($selected['name']) ?></h3>
<?php if (empty($data)): ?>
    <p>Brak pomiarów w wybranym okresie.</p>
<?php else: ?>
<table style="max-width:400px;margin-bottom:24px">
    <tr><th>Miara</th><th>Wartość</th></tr>
    <tr><td>Liczba pomiarów</td><td><?= $stats['count'] ?></td></tr>
    <tr><td>Minimum</td><td><?= $stats['min'] ?> <?= $selected['symbol'] ?></td></tr>
    <tr><td>Maksimum</td><td><?= $stats['max'] ?> <?= $selected['symbol'] ?></td></tr>
    <tr><td>Średnia</td><td><?= $stats['avg'] ?> <?= $selected['symbol'] ?></td></tr>
    <?php if ($selected['norm_min']!==null): ?><tr><td>Norma min</td><td><?= $selected['norm_min'] ?> <?= $selected['symbol'] ?></td></tr><?php endif; ?>
    <?php if ($selected['norm_max']!==null): ?><tr><td>Norma max</td><td><?= $selected['norm_max'] ?> <?= $selected['symbol'] ?></td></tr><?php endif; ?>
    <?php if (isset($stats['overcount'])): ?><tr><td><strong>Przekroczenia normy</strong></td><td style="color:#c00"><strong><?= $stats['overcount'] ?> / <?= $stats['count'] ?></strong></td></tr><?php endif; ?>
</table>
<h3>Wszystkie pomiary</h3>
<table>
    <tr><th>Data i czas</th><th>Wartość</th><th>Status</th></tr>
    <?php foreach ($data as $row):
        $over=($selected['norm_min']!==null&&$row['value']<$selected['norm_min'])||($selected['norm_max']!==null&&$row['value']>$selected['norm_max']); ?>
    <tr class="<?= $over?'over':'' ?>">
        <td><?= $row['measured_at'] ?></td>
        <td><?= $row['value'] ?> <?= $selected['symbol'] ?></td>
        <td><?= $over?'⚠ Poza normą':'✓ OK' ?></td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
<?php endif; ?>
</div></main>
</body></html>
