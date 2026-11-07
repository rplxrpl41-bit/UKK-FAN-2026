

<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

function e($nilai)
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}

// Cek ID siswa
$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['pesan'] = "ID siswa tidak valid!";
    header("Location: kelola_siswa.php");
    exit;
}

// Ambil data siswa
$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_siswa WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$query = mysqli_stmt_get_result($stmt);

$siswa = mysqli_fetch_assoc($query);

if (!$siswa) {
    $_SESSION['pesan'] = "Data siswa tidak ditemukan!";
    header("Location: kelola_siswa.php");
    exit;
}

$namaUser = $_SESSION['name'] ?? 'Pengguna';
$role = $_SESSION['role'] ?? '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Siswa</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

   
    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
            margin: 0;
        }

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

        .badge-status {
            padding: 7px 10px;
            border-radius: 5px;
            display: inline-block;
        }

        .status-aktif {
            background: #d1fae5;
            color: #065f46;
        }

        .status-nonaktif {
            background: #fee2e2;
            color: #991b1b;
        }

        .btn-primary {
            background: #3157d5;
            border-color: #3157d5;
        }

        .btn-primary:hover {
            background: #2445b8;
            border-color: #2445b8;
        }

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

        .action-buttons {
            display: flex;
            gap: 5px;
            white-space: nowrap;
        }

        @media (max-width: 767px) {
            .sidebar {
                min-height: auto;
            }

            .content {
                padding: 15px;
            }

            .sidebar .nav {
                display: flex;
                flex-direction: column;
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
                    <a href="kelola_siswa.php" class="nav-link active">
                         ♟ Data Siswa
                    </a>
                </li>

                <li class="nav-item">
                    <a href="kelola_guru.php" class="nav-link">
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
                    <a href="logout.php" class="nav-link logout"
                       onclick="return confirm('Yakin ingin logout?')">
                        &#10140; Logout
                    </a>
                </li>

            </ul>
        </aside>

<div class="content">
    <div class="page-header">
        <h2>Edit Data Siswa</h2>
        <p>Perbarui informasi siswa pada sistem.</p>
    </div>

    <div class="form-card">
        <h5>Form Edit Siswa</h5>

        <form action="proses_edit_siswa.php" method="POST">
            <input type="hidden" name="id" value="<?= e($siswa['id']) ?>">

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="nis">NIS</label>
                    <input type="text" class="form-control"
                           id="nis" name="nis"
                           value="<?= e($siswa['nis'] ?? '') ?>" required>
                </div>

                <div class="form-group col-md-6">
                    <label for="nisn">NISN</label>
                    <input type="text" class="form-control"
                           id="nisn" name="nisn"
                           value="<?= e($siswa['nisn'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="nama">Nama Siswa</label>
                <input type="text" class="form-control"
                       id="nama" name="nama"
                       value="<?= e($siswa['nama'] ?? '') ?>" required>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label for="jenis_kelamin">Jenis Kelamin</label>
                    <select class="form-control" id="jenis_kelamin"
                            name="jenis_kelamin" required>
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="L"
                            <?= ($siswa['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>
                            Laki-laki
                        </option>
                        <option value="P"
                            <?= ($siswa['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>
                            Perempuan
                        </option>
                    </select>
                </div>

                <div class="form-group col-md-6">
                    <label for="tanggal_lahir">Tanggal Lahir</label>
                    <input type="date" class="form-control"
                           id="tanggal_lahir" name="tanggal_lahir"
                           value="<?= e($siswa['tanggal_lahir'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea class="form-control" id="alamat"
                          name="alamat" required><?= e($siswa['alamat'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="status_aktif">Status Aktif</label>
                <select class="form-control" id="status_aktif"
                        name="status_aktif" required>
                    <option value="1"
                        <?= (string)($siswa['status_aktif'] ?? '') === '1' ? 'selected' : '' ?>>
                        Aktif
                    </option>
                    <option value="0"
                        <?= (string)($siswa['status_aktif'] ?? '') === '0' ? 'selected' : '' ?>>
                        Tidak Aktif
                    </option>
                </select>
            </div>

            <div class="mt-4">
                <button type="submit" name="update" class="btn btn-primary">
                    Simpan Perubahan
                </button>

                <a href="kelola_siswa.php" class="btn btn-secondary ml-2">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>

