<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Ambil ID guru dari URL
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['pesan'] = "ID guru tidak valid!";
    header("Location: kelola_guru.php");
    exit;
}

// Ambil data guru dan akun
$sql = "SELECT
            t_guru.*,
            t_users.name AS nama_akun,
            t_users.email AS email_user
        FROM t_guru
        LEFT JOIN t_users ON t_guru.user_id = t_users.id
        WHERE t_guru.id = ?";

$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$guru = mysqli_fetch_assoc($result);

if (!$guru) {
    $_SESSION['pesan'] = "Data guru tidak ditemukan!";
    header("Location: kelola_guru.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Guru</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

  
    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
            margin: 0;
        }

        /* SIDEBAR SAMA SEPERTI KELOLA SISWA */
        .sidebar {
            background: #172554;
            color: white;
            min-height: 100vh;
            padding: 22px 15px;
        }

        .sidebar h4 {
            font-weight: bold;
            margin-bottom: 30px;
        }

        .sidebar .nav-link {
            color: #e2e8f0;
            padding: 12px 14px;
            margin-bottom: 6px;
            border-radius: 6px;
            transition: 0.2s;
        }

        .sidebar .nav-link:hover {
            background: #263b70;
            color: white;
        }

        .sidebar .nav-link.active {
            background: #3157d5;
            color: white;
        }

        .sidebar .logout {
            color: #fca5a5;
        }

        /* KONTEN */
        .content {
            padding: 28px;
        }

        .page-title {
            color: #172554;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .card-main {
            background: white;
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }

        /* TABEL */
        .table thead th {
            background: #172554;
            color: white;
            white-space: nowrap;
            vertical-align: middle;
        }

        .table td {
            vertical-align: middle;
        }

        .table-responsive {
            overflow-x: auto;
        }

        /* TOMBOL */
        .btn-primary {
            background: #3157d5;
            border-color: #3157d5;
        }

        .btn-primary:hover {
            background: #2445b8;
            border-color: #2445b8;
        }

        .action-buttons {
            display: flex;
            gap: 5px;
            white-space: nowrap;
        }

        /* STATUS */
        .badge-status {
            padding: 7px 10px;
            border-radius: 5px;
            display: inline-block;
            font-size: 12px;
        }

        .status-aktif {
            background: #d1fae5;
            color: #065f46;
        }

        .status-nonaktif {
            background: #fee2e2;
            color: #991b1b;
        }

        /* DATATABLES */
        .dataTables_wrapper {
            padding-top: 8px;
        }

        .dataTables_filter input,
        .dataTables_length select {
            border: 1px solid #ced4da;
            border-radius: 5px;
            padding: 5px 8px;
        }

        .dataTables_info {
            color: #64748b;
            font-size: 13px;
        }

        .page-item.active .page-link {
            background: #3157d5;
            border-color: #3157d5;
        }

        .page-link {
            color: #3157d5;
        }

        @media (max-width: 767px) {
            .sidebar {
                min-height: auto;
            }

            .content {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <aside class="col-md-3 col-lg-2 sidebar">

            <h4>My Website</h4>

            <ul class="nav nav-pills flex-column">

                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        &#8962; Dashboard
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kelola_siswa.php" class="nav-link">
                       ♟ Data Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kelola_guru.php" class="nav-link active">
                     ♟ Data Guru
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kelola_kelas.php" class="nav-link">
                        &#9638; Data Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kelola_tahun_ajaran.php" class="nav-link">
                        &#9719; Kelola Tahun Ajaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kelola_wali_kelas.php" class="nav-link">
                        &#9817; Kelola Wali Kelas
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kelola_penempatan_siswa.php" class="nav-link">
                        &#8644; Penempatan Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kelola_kategori_pelanggaran.php" class="nav-link">
                        &#9776; Kategori Pelanggaran
                    </a>
                </li>

                <li class="nav-item">
                    <a href="tentang_saya.php" class="nav-link">
                        &#8505; About Me
                    </a>
                </li>

                <hr style="border-color: #475569;">

                <li class="nav-item">
                    <a href="logout.php"
                       class="nav-link logout"
                       onclick="return confirm('Yakin ingin logout?')">
                        &#10140; Logout
                    </a>
                </li>

            </ul>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 content">

            <div class="mb-4">
                <h2 class="page-title">Edit Data Guru</h2>
                <p class="text-muted">
                    Perbarui informasi guru dan akun pengguna.
                </p>
            </div>

            <div class="card card-main">

                <div class="card-header">
                    Form Edit Data Guru
                </div>

                <div class="card-body p-4">

                    <form action="proses_edit_guru.php" method="POST">

                        <input type="hidden" name="id"
                               value="<?= (int)$guru['id'] ?>">

                        <input type="hidden" name="user_id"
                               value="<?= (int)($guru['user_id'] ?? 0) ?>">

                        <h5 class="section-title">Informasi Guru</h5>

                        <div class="form-group">
                            <label>NIP</label>
                            <input type="text" name="nip"
                                   class="form-control"
                                   value="<?= htmlspecialchars($guru['nip'] ?? '') ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label>Nama Guru</label>
                            <input type="text" name="nama"
                                   class="form-control"
                                   value="<?= htmlspecialchars($guru['nama'] ?? '') ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label>Email Guru</label>
                            <input type="email" name="email"
                                   class="form-control"
                                   value="<?= htmlspecialchars($guru['email'] ?? $guru['email_user'] ?? '') ?>"
                                   required>
                        </div>

                        <hr>

                        <h5 class="section-title">Informasi Akun</h5>

                        <div class="form-group">
                            <label>Nama Akun</label>
                            <input type="text" name="name"
                                   class="form-control"
                                   value="<?= htmlspecialchars($guru['nama_akun'] ?? '') ?>"
                                   required>
                        </div>

                        <div class="form-group">
                            <label>Password Baru</label>
                            <input type="password" name="password"
                                   class="form-control"
                                   placeholder="Kosongkan jika tidak ingin mengganti password">
                            <small class="text-muted">
                                Isi hanya jika ingin mengubah password akun.
                            </small>
                        </div>

                        <div class="form-group">
                            <label>Status Aktif</label>
                            <select name="status_aktif"
                                    class="form-control" required>

                                <option value="1"
                                    <?= ($guru['status_aktif'] == 1) ? 'selected' : '' ?>>
                                    Aktif
                                </option>

                                <option value="0"
                                    <?= ($guru['status_aktif'] == 0) ? 'selected' : '' ?>>
                                    Tidak Aktif
                                </option>

                            </select>
                        </div>

                        <hr>

                        <button type="submit" name="update"
                                class="btn btn-primary">
                            Simpan Perubahan
                        </button>

                        <a href="kelola_guru.php"
                           class="btn btn-secondary">
                            Batal
                        </a>

                    </form>

                </div>
            </div>

            <p class="footer text-center mt-4">
                &copy; <?= date('Y') ?> Sistem Informasi Pelanggaran Siswa
            </p>

        </main>

    </div>
</div>

</body>
</html>
