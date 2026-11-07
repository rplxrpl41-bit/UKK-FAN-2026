<?php
session_start();

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Hanya admin yang boleh mengelola guru
if (($_SESSION['role'] ?? '') !== 'admin') {
    die('Akses ditolak!');
}

$error = '';

$nip = '';
$nama = '';
$email = '';
$user_id = '';
$status_aktif = '1';

// Ambil akun guru
$users = mysqli_query(
    $koneksi,
    "SELECT id, name, email, role
     FROM t_users
     WHERE role = 'guru'
     ORDER BY name ASC"
);

if (!$users) {
    die('Gagal mengambil akun pengguna.');
}

// Proses simpan data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $user_id = (int) ($_POST['user_id'] ?? 0);
    $status_aktif = (string) ($_POST['status_aktif'] ?? '1');

    if ($nip === '' || $nama === '' || $email === '') {
        $error = 'NIP, nama, dan email wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (!in_array($status_aktif, ['0', '1'], true)) {
        $error = 'Status guru tidak valid.';
    } elseif ($user_id <= 0) {
        $error = 'Silakan pilih akun pengguna.';
    } else {
        // Periksa akun pengguna
        $cek_user = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_users
             WHERE id = ? AND role = 'guru'
             LIMIT 1"
        );

        if (!$cek_user) {
            $error = 'Gagal memeriksa akun pengguna.';
        } else {
            mysqli_stmt_bind_param(
                $cek_user,
                'i',
                $user_id
            );

            mysqli_stmt_execute($cek_user);
            mysqli_stmt_store_result($cek_user);

            $user_ada = mysqli_stmt_num_rows($cek_user) > 0;

            mysqli_stmt_close($cek_user);

            if (!$user_ada) {
                $error = 'Akun guru tidak ditemukan.';
            }
        }

        // Simpan data guru
        if ($error === '') {
            $status = (int) $status_aktif;

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO t_guru
                 (nip, nama, email, status_aktif, user_id)
                 VALUES (?, ?, ?, ?, ?)"
            );

            if (!$stmt) {
                $error = 'Gagal menyiapkan query.';
            } else {
                mysqli_stmt_bind_param(
                    $stmt,
                    'sssii',
                    $nip,
                    $nama,
                    $email,
                    $status,
                    $user_id
                );

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);

                    header(
                        'Location: kelola_guru.php?pesan=sukses'
                    );
                    exit;
                } else {
                    if (mysqli_stmt_errno($stmt) === 1062) {
                        $error = 'NIP, email, atau akun guru sudah digunakan.';
                    } else {
                        $error = 'Gagal menyimpan data guru.';
                    }
                }

                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Fungsi keamanan output
function e($nilai)
{
    return htmlspecialchars(
        (string) $nilai,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Guru - My Website</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
    >

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

            <h2 class="page-title">Tambah Guru</h2>

            <p class="page-description">
                Tambahkan data guru baru ke dalam sistem.
            </p>

            <!-- PESAN ERROR -->
            <?php if ($error !== '') { ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= e($error) ?>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Tutup"
                    >
                        <span>&times;</span>
                    </button>
                </div>
            <?php } ?>

            <!-- FORM CARD -->
            <div class="form-card">

                <h5>Formulir Data Guru</h5>

                <p class="subtitle">
                    Lengkapi informasi guru pada kolom berikut.
                    Kolom bertanda
                    <span class="required">*</span>
                    wajib diisi.
                </p>

                <form method="POST" action="">

                    <!-- NIP -->
                    <div class="form-group">
                        <label for="nip">
                            NIP <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="nip"
                            id="nip"
                            class="form-control"
                            placeholder="Masukkan NIP guru"
                            value="<?= e($nip) ?>"
                            maxlength="18"
                            required
                        >
                    </div>

                    <!-- NAMA -->
                    <div class="form-group">
                        <label for="nama">
                            Nama Lengkap
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="nama"
                            id="nama"
                            class="form-control"
                            placeholder="Masukkan nama lengkap guru"
                            value="<?= e($nama) ?>"
                            maxlength="150"
                            required
                        >
                    </div>

                    <!-- EMAIL -->
                    <div class="form-group">
                        <label for="email">
                            Email <span class="required">*</span>
                        </label>

                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control"
                            placeholder="contoh@email.com"
                            value="<?= e($email) ?>"
                            maxlength="150"
                            required
                        >
                    </div>

                    <!-- AKUN PENGGUNA -->
                    <div class="form-group">
                        <label for="user_id">
                            Akun Pengguna
                            <span class="required">*</span>
                        </label>

                        <select
                            name="user_id"
                            id="user_id"
                            class="form-control"
                            required
                        >
                            <option value="">
                                -- Pilih akun pengguna --
                            </option>

                            <?php
                            mysqli_data_seek($users, 0);

                            while ($user = mysqli_fetch_assoc($users)) {
                            ?>
                                <option
                                    value="<?= (int) $user['id'] ?>"
                                    <?= (string) $user_id ===
                                        (string) $user['id']
                                        ? 'selected' : '' ?>
                                >
                                    <?= e($user['name']) ?>
                                    (<?= e($user['email'] ?? '-') ?>)
                                </option>
                            <?php } ?>
                        </select>

                        <small class="form-text">
                            Pilih akun pengguna yang memiliki role guru.
                        </small>
                    </div>

                    <!-- STATUS -->
                    <div class="form-group">
                        <label for="status_aktif">
                            Status Guru
                        </label>

                        <select
                            name="status_aktif"
                            id="status_aktif"
                            class="form-control"
                            required
                        >
                            <option
                                value="1"
                                <?= $status_aktif === '1'
                                    ? 'selected' : '' ?>
                            >
                                Aktif
                            </option>

                            <option
                                value="0"
                                <?= $status_aktif === '0'
                                    ? 'selected' : '' ?>
                            >
                                Tidak Aktif
                            </option>
                        </select>
                    </div>

                    <!-- TOMBOL -->
                    <div class="form-actions d-flex flex-wrap">

                        <button
                            type="submit"
                            class="btn btn-primary mr-2 mb-2"
                        >
                            &#10003; Simpan Data
                        </button>

                        <a
                            href="kelola_guru.php"
                            class="btn btn-secondary mb-2"
                        >
                            Kembali
                        </a>

                    </div>

                </form>

            </div>

            <footer>
                &copy; <?= date('Y') ?> My Website. All rights reserved.
            </footer>

        </main>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
