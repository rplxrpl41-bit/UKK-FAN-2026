<?php
session_start();
require 'koneksi.php';

/* =========================
   CEK LOGIN DAN ROLE
========================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$name = $_SESSION['name'] ?? 'User';
$role = $_SESSION['role'] ?? '';

if ($role !== 'admin') {
    http_response_code(403);
    die("Akses ditolak. Halaman ini hanya dapat diakses oleh Admin.");
}

/* =========================
   FUNGSI KEAMANAN OUTPUT
========================= */
function e($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/* =========================
   TAMBAH SISWA
========================= */
if (isset($_POST['tambah'])) {
    $nis = trim($_POST['nis'] ?? '');
    $nisn = trim($_POST['nisn'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    $status_aktif = (int) ($_POST['status_aktif'] ?? 1);

    if ($nisn === '' || $nama === '' ||
        !in_array($jenis_kelamin, ['L', 'P'], true) ||
        $tanggal_lahir === '' || $alamat === '') {
        echo "<script>
            alert('NISN, nama, jenis kelamin, tanggal lahir, dan alamat wajib diisi!');
            window.location='menu1.php';
        </script>";
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO t_siswa
                (nis, nisn, nama, jenis_kelamin, tanggal_lahir, alamat, status_aktif)
            VALUES
                (:nis, :nisn, :nama, :jenis_kelamin, :tanggal_lahir, :alamat, :status_aktif)
        ");

        $stmt->execute([
            ':nis' => $nis,
            ':nisn' => $nisn,
            ':nama' => $nama,
            ':jenis_kelamin' => $jenis_kelamin,
            ':tanggal_lahir' => $tanggal_lahir,
            ':alamat' => $alamat,
            ':status_aktif' => $status_aktif
        ]);

        echo "<script>
            alert('Data siswa berhasil ditambahkan!');
            window.location='menu1.php';
        </script>";
        exit;
    } catch (PDOException $ex) {
        error_log($ex->getMessage());
        echo "<script>
            alert('Gagal menambahkan siswa. Periksa NIS dan struktur tabel.');
            window.location='menu1.php';
        </script>";
        exit;
    }
}

/* =========================
   EDIT SISWA
========================= */
if (isset($_POST['edit'])) {
    $id = (int)($_POST['id'] ?? 0);
    $nis = trim($_POST['nis'] ?? '');
    $nisn = trim($_POST['nisn'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    $status_aktif = (int) ($_POST['status_aktif'] ?? 1);

    if ($id <= 0 || $nisn === '' || $nama === '' ||
        !in_array($jenis_kelamin, ['L', 'P'], true) ||
        $tanggal_lahir === '' || $alamat === '') {
        echo "<script>
            alert('Periksa kembali data siswa yang wajib diisi!');
            window.location='menu1.php';
        </script>";
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            UPDATE t_siswa
            SET nis = :nis,
                nisn = :nisn,
                nama = :nama,
                jenis_kelamin = :jenis_kelamin,
                tanggal_lahir = :tanggal_lahir,
                alamat = :alamat,
                status_aktif = :status_aktif,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            ':nis' => $nis,
            ':nisn' => $nisn,
            ':nama' => $nama,
            ':jenis_kelamin' => $jenis_kelamin,
            ':tanggal_lahir' => $tanggal_lahir,
            ':alamat' => $alamat,
            ':status_aktif' => $status_aktif,
            ':id' => $id
        ]);

        echo "<script>
            alert('Data siswa berhasil diperbarui!');
            window.location='menu1.php';
        </script>";
        exit;
    } catch (PDOException $ex) {
        error_log($ex->getMessage());
        echo "<script>
            alert('Gagal memperbarui data siswa.');
            window.location='menu1.php';
        </script>";
        exit;
    }
}

/* =========================
   HAPUS SISWA
========================= */
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];

    if ($id <= 0) {
        header("Location: menu1.php");
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM t_siswa WHERE id = :id");
        $stmt->execute([':id' => $id]);

        echo "<script>
            alert('Data siswa berhasil dihapus!');
            window.location='menu1.php';
        </script>";
        exit;
    } catch (PDOException $ex) {
        error_log($ex->getMessage());
        echo "<script>
            alert('Data siswa tidak dapat dihapus karena masih digunakan.');
            window.location='menu1.php';
        </script>";
        exit;
    }
}

/* =========================
   DATA UNTUK EDIT
========================= */
$data_edit = null;

if (isset($_GET['edit'])) {
    $id_edit = (int)$_GET['edit'];

    $stmt = $pdo->prepare("SELECT * FROM t_siswa WHERE id = :id");
    $stmt->execute([':id' => $id_edit]);
    $data_edit = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data_edit) {
        echo "<script>
            alert('Data siswa tidak ditemukan!');
            window.location='menu1.php';
        </script>";
        exit;
    }
}

/* =========================
   PENCARIAN DAN DATA SISWA
========================= */
$cari = trim($_GET['cari'] ?? '');

if ($cari !== '') {
    $stmt = $pdo->prepare("
        SELECT *
        FROM t_siswa
        WHERE nis LIKE :cari
           OR nisn LIKE :cari
           OR nama LIKE :cari
        ORDER BY id DESC
    ");

    $stmt->execute([':cari' => "%{$cari}%"]);
} else {
    $stmt = $pdo->query("
        SELECT *
        FROM t_siswa
        ORDER BY id DESC
    ");
}

$data_siswa = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_siswa = count($data_siswa);

/* =========================
   MENU SIDEBAR
========================= */
$menus = [
    ['Dashboard', 'dashboard.php', 'âŒ‚'],
    ['Kelola Siswa', 'menu1.php', 'â™™'],
    ['Kelola Guru', 'menu2.php', 'â™§'],
    ['Jenis Pelanggaran', 'menu3.php', 'â–¤'],
    ['Admin', 'menu4.php', 'âš™'],
    ['Kelola Kelas', 'kelola_kelas.php', 'â–¦'],
    ['Tahun Ajaran', 'kelola_tahun_ajaran.php', 'â–£'],
    ['Penempatan Siswa', 'penempatan_siwa.php', 'â‡„'],
    ['Wali Kelas', 'kelola_wali_kelas.php', 'â™§'],
    ['Kategori Pelanggaran', 'kelola_kategori_pelanggaran.php', 'â–¤'],
    ['Laporan', 'laporan.php', 'â–§']
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Siswa | UKK 2026</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        a {
            text-decoration: none;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 250px;
            background: #0f172a;
            color: #fff;
            padding: 24px 15px;
            overflow-y: auto;
            z-index: 100;
        }

        .brand {
            padding: 0 12px 24px;
            border-bottom: 1px solid #334155;
            margin-bottom: 20px;
        }

        .brand h2 {
            margin: 0 0 6px;
            font-size: 23px;
            letter-spacing: 1px;
        }

        .brand p {
            margin: 0;
            font-size: 12px;
            color: #94a3b8;
        }

        .user-info {
            background: #1e293b;
            border-radius: 12px;
            padding: 13px;
            margin-bottom: 20px;
        }

        .user-info strong {
            display: block;
            overflow-wrap: anywhere;
            font-size: 14px;
        }

        .user-info span {
            display: inline-block;
            margin-top: 7px;
            padding: 4px 9px;
            border-radius: 20px;
            background: #2563eb;
            font-size: 11px;
            text-transform: uppercase;
        }

        .nav-title {
            margin: 18px 12px 9px;
            font-size: 10px;
            color: #64748b;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            margin: 4px 0;
            color: #cbd5e1;
            font-size: 13px;
            border-radius: 9px;
            transition: .2s;
        }

        .nav-link:hover {
            background: #1e293b;
            color: white;
        }

        .nav-link.active {
            background: #2563eb;
            color: white;
            font-weight: 600;
        }

        .nav-icon {
            width: 20px;
            text-align: center;
            font-size: 17px;
        }

        .logout {
            display: block;
            padding: 12px;
            margin-top: 22px;
            color: #fecaca;
            border: 1px solid #7f1d1d;
            border-radius: 9px;
            text-align: center;
            font-size: 13px;
        }

        .logout:hover {
            background: #7f1d1d;
            color: white;
        }

        /* KONTEN */
        .main {
            margin-left: 250px;
            padding: 28px;
            min-height: 100vh;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 24px;
        }

        .topbar h1 {
            margin: 0;
            font-size: 27px;
            color: #0f172a;
        }

        .breadcrumb {
            margin-top: 7px;
            color: #64748b;
            font-size: 13px;
        }

        .welcome {
            color: #475569;
            font-size: 13px;
            text-align: right;
        }

        .welcome strong {
            color: #0f172a;
        }

        .card {
            background: white;
            border-radius: 14px;
            padding: 22px;
            margin-bottom: 22px;
            box-shadow: 0 3px 12px rgba(15, 23, 42, .04);
            border: 1px solid #e2e8f0;
        }

        .stats {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .stats p {
            margin: 0 0 7px;
            font-size: 13px;
            color: #64748b;
        }

        .stats h2 {
            margin: 0;
            font-size: 30px;
            color: #0f172a;
        }

        .stats-icon {
            width: 52px;
            height: 52px;
            display: grid;
            place-items: center;
            background: #dbeafe;
            color: #2563eb;
            border-radius: 12px;
            font-size: 24px;
        }

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .section-heading h2 {
            margin: 0;
            font-size: 18px;
        }

        .section-heading p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 12px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        .form-group input,
        .form-group select,
        .form-group textarea,
        .search-input {
            width: 100%;
            padding: 11px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: white;
            font: inherit;
            font-size: 13px;
            outline: none;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus,
        .search-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px #dbeafe;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 85px;
        }

        .full-width {
            grid-column: 1 / -1;
        }

        .form-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 9px;
            margin-top: 18px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 8px;
            padding: 11px 15px;
            cursor: pointer;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            transition: .2s;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
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
            background: #fef3c7;
            color: #92400e;
            padding: 7px 10px;
        }

        .btn-delete {
            background: #fee2e2;
            color: #b91c1c;
            padding: 7px 10px;
        }

        .search-form {
            display: flex;
            gap: 9px;
            width: 100%;
            max-width: 500px;
        }

        .search-form .search-input {
            flex: 1;
            min-width: 0;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            min-width: 950px;
            border-collapse: collapse;
            font-size: 12px;
        }

        thead {
            background: #f8fafc;
        }

        th {
            padding: 13px 12px;
            text-align: left;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: middle;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        .badge-gender {
            display: inline-block;
            padding: 5px 8px;
            border-radius: 20px;
            background: #e0e7ff;
            color: #3730a3;
            font-size: 11px;
        }

        .empty {
            padding: 30px;
            text-align: center;
            color: #64748b;
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding-top: 16px;
            color: #64748b;
            font-size: 12px;
        }

        @media (max-width: 900px) {
            .sidebar {
                width: 210px;
            }

            .main {
                margin-left: 210px;
                padding: 20px;
            }
        }

        @media (max-width: 650px) {
            .sidebar {
                position: relative;
                width: 100%;
                max-height: none;
                bottom: auto;
            }

            .main {
                margin-left: 0;
                padding: 15px;
            }

            .topbar {
                align-items: flex-start;
                flex-direction: column;
            }

            .welcome {
                text-align: left;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .full-width {
                grid-column: auto;
            }

            .search-form {
                max-width: 100%;
                flex-wrap: wrap;
            }

            .search-form .search-input {
                flex-basis: 100%;
            }

            .card {
                padding: 16px;
            }
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="brand">
        <h2>UKK 2026</h2>
        <p>Sistem Pelanggaran Siswa</p>
    </div>

    <div class="user-info">
        <strong><?= e($name) ?></strong>
        <span><?= e($role) ?></span>
    </div>

    <div class="nav-title">Menu Utama</div>

    <?php foreach ($menus as $menu): ?>
        <a
            class="nav-link <?= $menu[1] === 'menu1.php' ? 'active' : '' ?>"
            href="<?= e($menu[1]) ?>"
        >
            <span class="nav-icon"><?= e($menu[2]) ?></span>
            <span><?= e($menu[0]) ?></span>
        </a>
    <?php endforeach; ?>

    <a class="logout" href="logout.php"
       onclick="return confirm('Yakin ingin keluar dari aplikasi?')">
        â‡¥ &nbsp; Logout
    </a>
</aside>

<!-- KONTEN UTAMA -->
<main class="main">

    <header class="topbar">
        <div>
            <h1>Kelola Siswa</h1>
            <div class="breadcrumb">Dashboard / Kelola Siswa</div>
        </div>

        <div class="welcome">
            Selamat datang,<br>
            <strong><?= e($name) ?></strong>
            (<?= e($role) ?>)
        </div>
    </header>

    <!-- STATISTIK -->
    <section class="card stats">
        <div>
            <p>Total Data Siswa</p>
            <h2><?= $total_siswa ?></h2>
            <p style="margin-top:8px">
                <?= $cari !== '' ? 'Hasil pencarian siswa' : 'Data siswa yang ditampilkan' ?>
            </p>
        </div>

        <div class="stats-icon">â™™</div>
    </section>

    <!-- FORM TAMBAH / EDIT -->
    <section class="card">
        <div class="section-heading">
            <div>
                <h2><?= $data_edit ? 'Edit Data Siswa' : 'Tambah Data Siswa' ?></h2>
                <p>Lengkapi informasi siswa pada formulir berikut.</p>
            </div>
        </div>

        <form method="POST" action="menu1.php">
            <?php if ($data_edit): ?>
                <input type="hidden" name="id"
                       value="<?= e($data_edit['id']) ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group">
                    <label for="nis">NIS</label>
                    <input
                        type="text"
                        id="nis"
                        name="nis"
                        placeholder="Contoh: 2026001"
                        value="<?= e($data_edit['nis'] ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="nisn">NISN *</label>
                    <input
                        type="text"
                        id="nisn"
                        name="nisn"
                        placeholder="Masukkan NISN"
                        value="<?= e($data_edit['nisn'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="nama">Nama Siswa *</label>
                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        placeholder="Masukkan nama siswa"
                        value="<?= e($data_edit['nama'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="jenis_kelamin">Jenis Kelamin *</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="L"
                            <?= ($data_edit['jenis_kelamin'] ?? '') === 'L'
                                ? 'selected' : '' ?>>
                            Laki-laki
                        </option>
                        <option value="P"
                            <?= ($data_edit['jenis_kelamin'] ?? '') === 'P'
                                ? 'selected' : '' ?>>
                            Perempuan
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tanggal_lahir">Tanggal Lahir *</label>
                    <input
                        type="date"
                        id="tanggal_lahir"
                        name="tanggal_lahir"
                        value="<?= e($data_edit['tanggal_lahir'] ?? '') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="status_aktif">Status Siswa *</label>
                    <select id="status_aktif" name="status_aktif" required>
                        <option value="1"
                            <?= (string)($data_edit['status_aktif'] ?? '1') === '1'
                                ? 'selected' : '' ?>>
                            Aktif
                        </option>
                        <option value="0"
                            <?= (string)($data_edit['status_aktif'] ?? '1') === '0'
                                ? 'selected' : '' ?>>
                            Tidak Aktif
                        </option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label for="alamat">Alamat *</label>
                    <textarea
                        id="alamat"
                        name="alamat"
                        placeholder="Masukkan alamat siswa"
                        required
                    ><?= e($data_edit['alamat'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <?php if ($data_edit): ?>
                    <button class="btn btn-primary" type="submit" name="edit">
                        Simpan Perubahan
                    </button>

                    <a class="btn btn-secondary" href="menu1.php">
                        Batal
                    </a>
                <?php else: ?>
                    <button class="btn btn-primary" type="submit" name="tambah">
                        + Tambah Siswa
                    </button>

                    <button class="btn btn-secondary" type="reset">
                        Reset Form
                    </button>
                <?php endif; ?>
            </div>
        </form>
    </section>

    <!-- TABEL SISWA -->
    <section class="card">
        <div class="section-heading">
            <div>
                <h2>Data Siswa</h2>
                <p>Kelola seluruh data siswa yang tersimpan.</p>
            </div>
        </div>

        <form method="GET" action="menu1.php" class="search-form">
            <input
                class="search-input"
                type="text"
                name="cari"
                placeholder="Cari NIS, NISN, atau nama..."
                value="<?= e($cari) ?>"
            >

            <button class="btn btn-primary" type="submit">
                Cari
            </button>

            <a class="btn btn-secondary" href="menu1.php">
                Reset
            </a>
        </form>

        <br>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>ID</th>
                        <th>NIS</th>
                        <th>NISN</th>
                        <th>Nama Siswa</th>
                        <th>Jenis Kelamin</th>
                        <th>Tanggal Lahir</th>
                        <th>Alamat</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($total_siswa > 0): ?>
                        <?php $no = 1; ?>

                        <?php foreach ($data_siswa as $row): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= e($row['id'] ?? '') ?></td>
                                <td><?= e($row['nis'] ?? '') ?></td>
                                <td><?= e($row['nisn'] ?? '') ?></td>
                                <td><strong><?= e($row['nama'] ?? '') ?></strong></td>
                                <td>
                                    <span class="badge-gender">
                                        <?= ($row['jenis_kelamin'] ?? '') === 'L' ? 'Laki-laki' : (($row['jenis_kelamin'] ?? '') === 'P' ? 'Perempuan' : '-') ?>
                                    </span>
                                </td>
                                <td><?= e($row['tanggal_lahir'] ?? '') ?></td>
                                <td><?= e($row['alamat'] ?? '') ?></td>
                                <td><?= (int)($row['status_aktif'] ?? 0) === 1 ? 'Aktif' : 'Tidak Aktif' ?></td>
                                <td>
                                    <a class="btn btn-edit"
                                       href="menu1.php?edit=<?= (int)$row['id'] ?>">
                                        Edit
                                    </a>

                                    <a class="btn btn-delete"
                                       href="menu1.php?hapus=<?= (int)$row['id'] ?>"
                                       onclick="return confirm('Yakin ingin menghapus data siswa ini?')">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="empty">
                                <?php if ($cari !== ''): ?>
                                    Data siswa tidak ditemukan untuk pencarian
                                    "<?= e($cari) ?>".
                                <?php else: ?>
                                    Belum ada data siswa.
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <span>Jumlah data ditampilkan: <strong><?= $total_siswa ?></strong></span>
            <span>UKK 2026 &mdash; Sistem Pelanggaran Siswa</span>
        </div>
    </section>

</main>

</body>
</html>
