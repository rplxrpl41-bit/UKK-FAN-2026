<?php require 'cek_akses.php'; require 'koneksi.php';
$rows = $pdo->query('SELECT id,name,email,role FROM t_users ORDER BY id')->fetchAll(); ?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><title>Admin</title></head><body>
<h1>Admin</h1><table border="1" cellpadding="5"><tr><th>ID</th><th>Nama</th><th>Email</th><th>Role</th></tr>
<?php foreach ($rows as $r): ?><tr><td><?= htmlspecialchars($r['id']) ?></td><td><?= htmlspecialchars($r['name']) ?></td><td><?= htmlspecialchars($r['email']) ?></td><td><?= htmlspecialchars($r['role']) ?></td></tr><?php endforeach; ?></table>
<p><a href="dashboard.php">Kembali ke Dashboard</a></p></body></html>
