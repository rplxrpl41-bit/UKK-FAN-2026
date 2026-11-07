<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

if (!isset($_SESSION['user_id'], $_SESSION['role'])) {
    header('Location: login.php');
    exit;
}

$role = $_SESSION['role'];
$halaman = basename($_SERVER['PHP_SELF']);

// Admin dapat mengakses seluruh halaman sistem.
$aksesAdmin = [
    'dashboard.php', 'menu1.php', 'menu2.php', 'menu3.php', 'menu4.php',
    'kelola_kelas.php', 'kelola_tahun_ajaran.php', 'penempatan_siwa.php',
    'kelola_wali_kelas.php', 'kelola_kategori_pelanggaran.php', 'laporan.php',
    'eksport.php', 'reset_password.php'
];

// Guru diberi akses ke fitur yang dibutuhkan untuk pekerjaan guru.
$aksesGuru = [
    'dashboard.php', 'menu1.php', 'menu3.php',
    'kelola_kategori_pelanggaran.php', 'laporan.php'
];

$izin = $role === 'admin' ? $aksesAdmin : ($role === 'guru' ? $aksesGuru : []);

if (!in_array($halaman, $izin, true)) {
    http_response_code(403);
    ?>
    <!doctype html>
    <html lang="id">
    <head><meta charset="utf-8"><title>Akses Ditolak</title></head>
    <body>
    <h1>AKSES DITOLAK</h1>
    <p>Akun dengan role <b><?= htmlspecialchars($role) ?></b> tidak memiliki izin membuka halaman tersebut.</p>
    <p><a href="dashboard.php">Kembali ke Dashboard</a></p>
    <p><a href="logout.php">Logout</a></p>
    </body>
    </html>
    <?php
    exit;
}
