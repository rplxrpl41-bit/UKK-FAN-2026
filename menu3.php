<?php
require 'cek_akses.php';
require 'koneksi.php';

$name = $_SESSION['name'] ?? 'User';
$role = $_SESSION['role'] ?? 'guru';

$stmt = $pdo->query("SELECT * FROM t_pelanggaran ORDER BY id DESC LIMIT 100");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_data = count($rows);

function e($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/* MENU SIDEBAR */
$menuAdmin = [
    ['Dashboard', 'dashboard.php', 'â–¦'],
    ['Kelola Siswa', 'menu1.php', 'â™™'],
    ['Kelola Guru', 'menu2.php', 'â™Ÿ'],
    ['Kelola Jenis Pelanggaran', 'menu3.php', 'âš‘'],
    ['Admin', 'menu4.php', 'âš™'],
    ['Kelola Kelas', 'kelola_kelas.php', 'â–¤'],
    ['Kelola Tahun Ajaran', 'kelola_tahun_ajaran.php', 'â–£'],
    ['Penempatan Siswa', 'penempatan_siwa.php', 'â‡„'],
    ['Kelola Wali Kelas', 'kelola_wali_kelas.php', 'â™§'],
    ['Kelola Kategori Pelanggaran', 'kelola_kategori_pelanggaran.php', 'â˜·'],
    ['Laporan', 'laporan.php', 'â–¥'],
    ['Logout', 'logout.php', 'â‡¥']
];

$menuGuru = [
    ['Dashboard', 'dashboard.php', 'â–¦'],
    ['Kelola Siswa', 'menu1.php', 'â™™'],
    ['Kelola Jenis Pelanggaran', 'menu3.php', 'âš‘'],
    ['Kelola Kategori Pelanggaran', 'kelola_kategori_pelanggaran.php', 'â˜·'],
    ['Laporan', 'laporan.php', 'â–¥'],
    ['Logout', 'logout.php', 'â‡¥']
];

$menus = $role === 'admin' ? $menuAdmin : $menuGuru;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Jenis Pelanggaran | UKK 2026</title>

<style>
:root {
    --primary: #3157d5;
    --primary-dark: #2445b3;
    --sidebar: #172554;
    --text: #1e293b;
    --muted: #64748b;
    --border: #e2e8f0;
    --background: #f4f7fb;
    --white: #ffffff;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: var(--background);
    color: var(--text);
    font-size: 14px;
}

/* SIDEBAR */
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 250px;
    padding: 25px 16px;
    background: var(--sidebar);
    overflow-y: auto;
    z-index: 100;
}

.brand {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 8px 28px;
    color: white;
}

.brand-logo {
    width: 43px;
    height: 43px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--primary);
    border-radius: 12px;
    font-size: 23px;
    font-weight: 800;
}

.brand h2 {
    font-size: 18px;
    margin-bottom: 4px;
}

.brand p {
    font-size: 11px;
    color: #a5b4fc;
}

.menu-label {
    padding: 10px 12px;
    margin-bottom: 8px;
    color: #94a3b8;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.3px;
}

.nav-menu {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.nav-menu a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    border-radius: 9px;
    color: #cbd5e1;
    text-decoration: none;
    transition: .2s;
}

.nav-menu a:hover,
.nav-menu a.active {
    background: var(--primary);
    color: white;
}

.nav-icon {
    width: 22px;
    text-align: center;
    font-size: 17px;
}

/* KONTEN */
.main {
    min-height: 100vh;
    margin-left: 250px;
    padding: 28px;
}

/* HEADER */
.topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 28px;
}

.topbar h1 {
    margin-bottom: 7px;
    color: #0f172a;
    font-size: 25px;
}

.topbar p {
    color: var(--muted);
}

.user-info {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 15px;
    background: white;
    border: 1px solid var(--border);
    border-radius: 12px;
}

.avatar {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #dbeafe;
    color: var(--primary);
    border-radius: 50%;
    font-size: 17px;
    font-weight: 800;
}

.user-info strong {
    display: block;
    margin-bottom: 4px;
}

.user-info small {
    color: var(--muted);
}

/* JUDUL HALAMAN */
.page-heading {
    margin-bottom: 22px;
}

.page-heading h2 {
    margin-bottom: 7px;
    color: #0f172a;
    font-size: 21px;
}

.page-heading p {
    color: var(--muted);
}

/* STATISTIK */
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 18px;
    margin-bottom: 22px;
}

.stat-card {
    padding: 22px;
    background: white;
    border: 1px solid var(--border);
    border-radius: 14px;
    box-shadow: 0 3px 12px rgba(15, 23, 42, .025);
}

.stat-card span {
    display: block;
    margin-bottom: 12px;
    color: var(--muted);
}

.stat-card strong {
    color: var(--primary);
    font-size: 29px;
}

/* KARTU TABEL */
.card {
    padding: 24px;
    margin-bottom: 22px;
    background: white;
    border: 1px solid var(--border);
    border-radius: 14px;
    box-shadow: 0 3px 12px rgba(15, 23, 42, .025);
}

.card-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding-bottom: 17px;
    margin-bottom: 20px;
    border-bottom: 1px solid var(--border);
}

.card-title h3 {
    color: #0f172a;
    font-size: 17px;
}

.badge {
    display: inline-block;
    padding: 6px 10px;
    color: #1d4ed8;
    background: #dbeafe;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
}

/* TABEL */
.table-wrapper {
    width: 100%;
    overflow-x: auto;
    border: 1px solid var(--border);
    border-radius: 10px;
}

table {
    width: 100%;
    min-width: 550px;
    border-collapse: collapse;
    background: white;
}

thead {
    background: #f8fafc;
}

th {
    padding: 15px;
    color: #475569;
    border-bottom: 1px solid var(--border);
    font-size: 12px;
    font-weight: 700;
    text-align: left;
    text-transform: uppercase;
    letter-spacing: .3px;
    white-space: nowrap;
}

td {
    padding: 14px 15px;
    color: #475569;
    border-bottom: 1px solid var(--border);
}

tbody tr:hover {
    background: #f8fafc;
}

tbody tr:last-child td {
    border-bottom: none;
}

.empty-data {
    padding: 30px;
    color: var(--muted);
    text-align: center;
}

/* FOOTER */
.footer {
    padding: 20px 0;
    margin-top: 20px;
    color: #94a3b8;
    font-size: 12px;
    text-align: center;
}

/* RESPONSIVE */
@media (max-width: 1000px) {
    .sidebar {
        width: 215px;
    }

    .main {
        margin-left: 215px;
        padding: 22px;
    }

    .topbar {
        align-items: flex-start;
        flex-direction: column;
    }
}

@media (max-width: 650px) {
    .sidebar {
        position: relative;
        width: 100%;
        padding: 18px 14px;
    }

    .brand {
        padding-bottom: 16px;
    }

    .nav-menu {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .nav-menu a {
        padding: 10px 8px;
        font-size: 12px;
    }

    .main {
        margin-left: 0;
        padding: 18px 12px;
    }

    .topbar h1 {
        font-size: 22px;
    }

    .user-info {
        width: 100%;
    }

    .card {
        padding: 17px;
    }
}
</style>
</head>

<body>

<aside class="sidebar">
    <div class="brand">
        <div class="brand-logo">U</div>
        <div>
            <h2>UKK 2026</h2>
            <p>Sistem Pelanggaran Siswa</p>
        </div>
    </div>

    <div class="menu-label">MENU NAVIGASI</div>

    <nav class="nav-menu">
        <?php foreach ($menus as $menu): ?>
            <a
                href="<?= e($menu[1]) ?>"
                class="<?= $menu[1] === 'menu3.php' ? 'active' : '' ?>"
            >
                <span class="nav-icon"><?= e($menu[2]) ?></span>
                <span><?= e($menu[0]) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>

<main class="main">

    <header class="topbar">
        <div>
            <h1>Kelola Jenis Pelanggaran</h1>
            <p>Kelola dan lihat data jenis pelanggaran siswa.</p>
        </div>

        <div class="user-info">
            <div class="avatar">
                <?= e(strtoupper(substr($name, 0, 1))) ?>
            </div>
            <div>
                <strong><?= e($name) ?></strong>
                <small><?= e(ucfirst($role)) ?> Â· UKK 2026</small>
            </div>
        </div>
    </header>

    <section class="page-heading">
        <h2>Data Jenis Pelanggaran</h2>
        <p>Berikut daftar jenis pelanggaran yang tersimpan di sistem.</p>
    </section>

    <section class="stats">
        <div class="stat-card">
            <span>Total Jenis Pelanggaran</span>
            <strong><?= $total_data ?></strong>
        </div>
    </section>

    <section class="card">
        <div class="card-title">
            <h3>Daftar Jenis Pelanggaran</h3>
            <span class="badge"><?= $total_data ?> data</span>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>ID</th>
                        <th>Kode Pelanggaran</th>
                        <th>Nama Pelanggaran</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($total_data > 0): ?>
                    <?php $no = 1; ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= e($r['id'] ?? '') ?></td>
                            <td>
                                <span class="badge">
                                    <?= e($r['kode'] ?? '') ?>
                                </span>
                            </td>
                            <td>
                                <?= e($r['nama'] ?? $r['nama_pelanggaran'] ?? '') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="empty-data">
                            Belum ada data jenis pelanggaran.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <footer class="footer">
        &copy; <?= date('Y') ?> UKK 2026 â€” Sistem Pelanggaran Siswa
    </footer>

</main>

</body>
</html>
