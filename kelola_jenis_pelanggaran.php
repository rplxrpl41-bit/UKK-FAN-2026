<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$name = $_SESSION['name'] ?? 'User';
$role = $_SESSION['role'] ?? '';

if (!in_array($role, ['admin', 'guru'], true)) {
    http_response_code(403);
    die("Akses ditolak.");
}

require 'koneksi.php';

function e($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/* =========================
   AMBIL KATEGORI
========================= */
$query_kategori = $pdo->query("
    SELECT id, nama FROM t_pelanggaran_kategori ORDER BY nama ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   TAMBAH DATA
========================= */
if (isset($_POST['tambah'])) {
    $kategori_id = (int)($_POST['pelanggaran_kategori_id'] ?? 0);
    $kode = trim($_POST['kode'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $poin = (int)($_POST['poin'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status_aktif = (int)($_POST['status_aktif'] ?? 1);

    if ($kategori_id <= 0 || $kode === '' || $nama === '' || $poin < 0) {
        echo "<script>alert('Kategori, kode, nama, dan poin wajib diisi!');location.href='kelola_jenis_pelanggaran.php';</script>";
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO t_pelanggaran
                (pelanggaran_kategori_id, kode, nama, poin, deskripsi, status_aktif)
            VALUES
                (:kategori, :kode, :nama, :poin, :deskripsi, :status)
        ");

        $stmt->execute([
            ':kategori' => $kategori_id,
            ':kode' => $kode,
            ':nama' => $nama,
            ':poin' => $poin,
            ':deskripsi' => $deskripsi,
            ':status' => $status_aktif
        ]);

        header("Location: kelola_jenis_pelanggaran.php?pesan=tambah");
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Gagal menambahkan data. Periksa kode pelanggaran dan struktur tabel.');location.href='kelola_jenis_pelanggaran.php';</script>";
        exit;
    }
}

/* =========================
   EDIT DATA
========================= */
if (isset($_POST['edit'])) {
    $id = (int)($_POST['id'] ?? 0);
    $kategori_id = (int)($_POST['pelanggaran_kategori_id'] ?? 0);
    $kode = trim($_POST['kode'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $poin = (int)($_POST['poin'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status_aktif = (int)($_POST['status_aktif'] ?? 1);

    if ($id < 1 || $kategori_id <= 0 || $kode === '' || $nama === '' || $poin < 0) {
        echo "<script>alert('Semua data wajib diisi!');location.href='kelola_jenis_pelanggaran.php';</script>";
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE t_pelanggaran
            SET pelanggaran_kategori_id = :kategori,
                kode = :kode,
                nama = :nama,
                poin = :poin,
                deskripsi = :deskripsi,
                status_aktif = :status,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            ':kategori' => $kategori_id,
            ':kode' => $kode,
            ':nama' => $nama,
            ':poin' => $poin,
            ':deskripsi' => $deskripsi,
            ':status' => $status_aktif,
            ':id' => $id
        ]);

        header("Location: kelola_jenis_pelanggaran.php?pesan=edit");
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Gagal memperbarui data. Periksa kembali data yang dimasukkan.');location.href='kelola_jenis_pelanggaran.php';</script>";
        exit;
    }
}

/* =========================
   HAPUS DATA
========================= */
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("
                DELETE FROM t_pelanggaran
                WHERE id = :id
            ");

            $stmt->execute(['id' => $id]);

            header("Location: kelola_jenis_pelanggaran.php?pesan=hapus");
            exit;
        } catch (PDOException $e) {
            echo "<script>alert('Data tidak dapat dihapus karena mungkin masih digunakan.');location.href='kelola_jenis_pelanggaran.php';</script>";
            exit;
        }
    }

    header("Location: kelola_jenis_pelanggaran.php");
    exit;
}

/* =========================
   AMBIL DATA UNTUK EDIT
========================= */
$data_edit = null;

if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];

    $stmt = $pdo->prepare("
        SELECT *
        FROM t_pelanggaran
        WHERE id = :id
    ");

    $stmt->execute(['id' => $id_edit]);
    $data_edit = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* =========================
   PENCARIAN DATA
========================= */
$cari = trim($_GET['cari'] ?? '');

if ($cari !== '') {
    $stmt = $pdo->prepare("
        SELECT p.*, k.nama AS nama_kategori
        FROM t_pelanggaran p
        LEFT JOIN t_pelanggaran_kategori k
            ON p.pelanggaran_kategori_id = k.id
        WHERE p.kode LIKE :cari
           OR p.nama LIKE :cari
        ORDER BY p.id DESC
    ");

    $stmt->execute(['cari' => "%$cari%"]);
} else {
    $stmt = $pdo->query("
        SELECT p.*, k.nama AS nama_kategori
        FROM t_pelanggaran p
        LEFT JOIN t_pelanggaran_kategori k
            ON p.pelanggaran_kategori_id = k.id
        ORDER BY p.id DESC
    ");
}

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_data = count($data);
$pesan = $_GET['pesan'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Jenis Pelanggaran | UKK 2026</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: #f1f5f9;
    color: #1e293b;
    font-size: 14px;
}

/* HEADER */
.header {
    background: #ffffff;
    padding: 22px 30px;
    border-bottom: 1px solid #e2e8f0;
}

.header h1 {
    font-size: 25px;
    margin-bottom: 8px;
    color: #0f172a;
}

.header p {
    color: #64748b;
}

.header b {
    color: #2563eb;
}

/* NAVIGASI */
.navbar {
    background: #0f172a;
    padding: 13px 25px;
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}

.navbar a {
    color: #cbd5e1;
    text-decoration: none;
    padding: 10px 12px;
    border-radius: 7px;
    transition: .2s;
}

.navbar a:hover,
.navbar a.active {
    background: #2563eb;
    color: white;
}

/* KONTEN */
.container {
    max-width: 1400px;
    margin: 28px auto;
    padding: 0 25px;
}

.page-heading {
    margin-bottom: 22px;
}

.page-heading h2 {
    font-size: 24px;
    color: #0f172a;
    margin-bottom: 7px;
}

.page-heading p {
    color: #64748b;
}

/* KARTU */
.card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 4px 15px rgba(15, 23, 42, .04);
}

.card-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 22px;
    padding-bottom: 15px;
    border-bottom: 1px solid #e2e8f0;
}

.card-title h3 {
    color: #0f172a;
    font-size: 18px;
}

/* STATISTIK */
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
    gap: 18px;
    margin-bottom: 25px;
}

.stat-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 22px;
    box-shadow: 0 4px 15px rgba(15, 23, 42, .03);
}

.stat-card span {
    display: block;
    color: #64748b;
    margin-bottom: 10px;
}

.stat-card strong {
    font-size: 28px;
    color: #2563eb;
}

/* FORM */
.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
}

.form-group input {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
    transition: .2s;
}

.form-group input:focus,
.search-form input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
}

/* TOMBOL */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 11px 16px;
    border: none;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: .2s;
}

.btn-primary {
    background: #2563eb;
    color: #ffffff;
}

.btn-primary:hover {
    background: #1d4ed8;
}

.btn-secondary {
    background: #e2e8f0;
    color: #334155;
}

.btn-secondary:hover {
    background: #cbd5e1;
}

.btn-edit {
    background: #dbeafe;
    color: #1d4ed8;
}

.btn-edit:hover {
    background: #bfdbfe;
}

.btn-delete {
    background: #fee2e2;
    color: #b91c1c;
}

.btn-delete:hover {
    background: #fecaca;
}

/* PENCARIAN */
.search-form {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 20px;
}

.search-form input {
    flex: 1;
    min-width: 220px;
    padding: 12px 14px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    outline: none;
}

/* TABEL */
.table-wrapper {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
}

table {
    width: 100%;
    min-width: 650px;
    border-collapse: collapse;
    background: #ffffff;
}

thead {
    background: #eff6ff;
}

th {
    color: #1e40af;
    font-weight: 700;
    text-align: left;
    padding: 15px 16px;
    border-bottom: 1px solid #dbeafe;
    white-space: nowrap;
}

td {
    padding: 14px 16px;
    color: #475569;
    border-bottom: 1px solid #e2e8f0;
}

tbody tr:hover {
    background: #f8fafc;
}

tbody tr:last-child td {
    border-bottom: none;
}

.action-buttons {
    display: flex;
    align-items: center;
    gap: 7px;
}

/* BADGE */
.badge {
    display: inline-block;
    background: #dbeafe;
    color: #1d4ed8;
    border-radius: 20px;
    padding: 6px 10px;
    font-size: 12px;
    font-weight: 600;
}

/* NOTIFIKASI */
.alert {
    padding: 13px 16px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: #dcfce7;
    color: #166534;
}

.empty-data {
    padding: 30px;
    text-align: center;
    color: #64748b;
}

/* FOOTER */
.footer {
    text-align: center;
    color: #94a3b8;
    padding: 25px;
    margin-top: 20px;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .header {
        padding: 20px;
    }

    .header h1 {
        font-size: 21px;
    }

    .navbar {
        padding: 12px;
    }

    .navbar a {
        padding: 9px;
        font-size: 12px;
    }

    .container {
        padding: 0 12px;
        margin-top: 20px;
    }

    .card {
        padding: 17px;
    }

    .search-form {
        flex-direction: column;
    }

    .search-form input {
        width: 100%;
    }

    .action-buttons {
        flex-wrap: wrap;
    }
}
</style>
</head>

<body>

<header class="header">
    <h1>UKK 2026</h1>
    <p>
        Selamat datang,
        <b><?= e($name) ?></b>
        &nbsp; | &nbsp; Role:
        <b><?= e(ucfirst($role)) ?></b>
    </p>
</header>

<nav class="navbar">
    <a href="dashboard.php">Dashboard</a>

    <?php if ($role === 'admin'): ?>
        <a href="menu1.php">Kelola Siswa</a>
        <a href="menu2.php">Kelola Guru</a>
    <?php endif; ?>

    <a href="menu3.php" class="active">Jenis Pelanggaran</a>

    <?php if ($role === 'admin'): ?>
        <a href="menu4.php">Admin</a>
        <a href="kelola_kelas.php">Kelola Kelas</a>
        <a href="kelola_tahun_ajaran.php">Tahun Ajaran</a>
        <a href="penempatan_siwa.php">Penempatan Siswa</a>
        <a href="kelola_wali_kelas.php">Wali Kelas</a>
        <a href="kelola_kategori_pelanggaran.php">Kategori Pelanggaran</a>
    <?php endif; ?>

    <a href="laporan.php">Laporan</a>
    <a href="logout.php">Logout</a>
</nav>

<main class="container">

    <div class="page-heading">
        <h2>Kelola Jenis Pelanggaran</h2>
        <p>Kelola kode dan nama jenis pelanggaran siswa.</p>
    </div>

    <?php if ($pesan !== ''): ?>
        <div class="alert alert-success">
            <?php
            if ($pesan === 'tambah') {
                echo 'Data jenis pelanggaran berhasil ditambahkan.';
            } elseif ($pesan === 'edit') {
                echo 'Data jenis pelanggaran berhasil diperbarui.';
            } elseif ($pesan === 'hapus') {
                echo 'Data jenis pelanggaran berhasil dihapus.';
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="stats">
        <div class="stat-card">
            <span>Total data yang ditampilkan</span>
            <strong><?= $total_data ?></strong>
        </div>
    </div>

    <!-- FORM TAMBAH DAN EDIT -->
    <section class="card">
        <div class="card-title">
            <h3>
                <?= $data_edit ? 'Edit Jenis Pelanggaran' : 'Tambah Jenis Pelanggaran' ?>
            </h3>
        </div>

        <form method="POST" action="kelola_jenis_pelanggaran.php">
            <?php if ($data_edit): ?>
                <input type="hidden" name="id" value="<?= e($data_edit['id']) ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="pelanggaran_kategori_id">Kategori</label>
                <select id="pelanggaran_kategori_id" name="pelanggaran_kategori_id" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($query_kategori as $kategori): ?>
                        <option value="<?= (int)$kategori['id'] ?>"
                            <?= (int)($data_edit['pelanggaran_kategori_id'] ?? 0) === (int)$kategori['id'] ? 'selected' : '' ?>>
                            <?= e($kategori['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="kode">Kode Pelanggaran</label>
                <input
                    type="text"
                    id="kode"
                    name="kode"
                    placeholder="Contoh: PG-001"
                    value="<?= $data_edit ? e($data_edit['kode']) : '' ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="nama">Nama Pelanggaran</label>
                <input
                    type="text"
                    id="nama"
                    name="nama"
                    placeholder="Contoh: Terlambat datang ke sekolah"
                    value="<?= $data_edit ? e($data_edit['nama']) : '' ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="poin">Poin</label>
                <input
                    type="number"
                    id="poin"
                    name="poin"
                    min="0"
                    value="<?= $data_edit ? (int)$data_edit['poin'] : '0' ?>"
                    required
                >
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi"><?= $data_edit ? e($data_edit['deskripsi']) : '' ?></textarea>
            </div>

            <div class="form-group">
                <label for="status_aktif">Status</label>
                <select id="status_aktif" name="status_aktif" required>
                    <option value="1" <?= (string)($data_edit['status_aktif'] ?? '1') === '1' ? 'selected' : '' ?>>Aktif</option>
                    <option value="0" <?= (string)($data_edit['status_aktif'] ?? '1') === '0' ? 'selected' : '' ?>>Tidak Aktif</option>
                </select>
            </div>

            <?php if ($data_edit): ?>
                <button type="submit" name="edit" class="btn btn-primary">
                    Simpan Perubahan
                </button>

                <a href="kelola_jenis_pelanggaran.php" class="btn btn-secondary">Batal</a>
            <?php else: ?>
                <button type="submit" name="tambah" class="btn btn-primary">
                    + Tambah Data
                </button>
            <?php endif; ?>
        </form>
    </section>

    <!-- TABEL DATA -->
    <section class="card">
        <div class="card-title">
            <h3>Data Jenis Pelanggaran</h3>
            <span class="badge"><?= $total_data ?> data</span>
        </div>

        <form method="GET" action="kelola_jenis_pelanggaran.php" class="search-form">
            <input
                type="text"
                name="cari"
                placeholder="Cari kode atau nama pelanggaran..."
                value="<?= e($cari) ?>"
            >

            <button type="submit" class="btn btn-primary">
                Cari Data
            </button>

            <a href="kelola_jenis_pelanggaran.php" class="btn btn-secondary">Reset</a>
        </form>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>ID</th>
                        <th>Kategori</th>
                        <th>Kode Pelanggaran</th>
                        <th>Nama Pelanggaran</th>
                        <th>Poin</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php if ($total_data > 0): ?>
                    <?php $no = 1; ?>

                    <?php foreach ($data as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= e($row['id']) ?></td>
                            <td><?= e($row['nama_kategori'] ?? '-') ?></td>
                            <td>
                                <span class="badge"><?= e($row['kode']) ?></span>
                            </td>
                            <td><?= e($row['nama']) ?></td>
                            <td><?= e($row['poin']) ?></td>
                            <td><?= (int)($row['status_aktif'] ?? 0) === 1 ? 'Aktif' : 'Tidak Aktif' ?></td>
                            <td>
                                <div class="action-buttons">
                                    <a
                                        href="kelola_jenis_pelanggaran.php?edit=<?= (int)$row['id'] ?>"
                                        class="btn btn-edit"
                                    >Edit</a>

                                    <a
                                        href="kelola_jenis_pelanggaran.php?hapus=<?= (int)$row['id'] ?>"
                                        class="btn btn-delete"
                                        onclick="return confirm('Yakin ingin menghapus jenis pelanggaran ini?')"
                                    >Hapus</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="8" class="empty-data">
                            Data jenis pelanggaran belum ditemukan.
                        </td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

</main>

<footer class="footer">
    &copy; <?= date('Y') ?> UKK 2026 â€” Sistem Pelanggaran Siswa
</footer>

</body>
</html>
