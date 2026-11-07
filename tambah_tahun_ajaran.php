
<?php
session_start();

include 'includes/cek_session.php';
include 'config/koneksi.php';

$error = '';

$nama = '';
$tanggal_mulai = '';
$tanggal_selesai = '';
$status_aktif = '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $status_aktif = (string) ($_POST['status_aktif'] ?? '1');

    if (
        $nama === '' ||
        $tanggal_mulai === '' ||
        $tanggal_selesai === ''
    ) {
        $error = 'Semua kolom wajib diisi.';
    } elseif ($tanggal_mulai > $tanggal_selesai) {
        $error = 'Tanggal mulai tidak boleh melewati tanggal selesai.';
    } elseif (!in_array($status_aktif, ['0', '1'], true)) {
        $error = 'Status yang dipilih tidak valid.';
    } else {
        $stmt = mysqli_prepare(
            $koneksi,
            "INSERT INTO t_tahun_ajaran
            (nama, tanggal_mulai, tanggal_selesai, status_aktif)
            VALUES (?, ?, ?, ?)"
        );

        if ($stmt) {
            $status = (int) $status_aktif;

            mysqli_stmt_bind_param(
                $stmt,
                "sssi",
                $nama,
                $tanggal_mulai,
                $tanggal_selesai,
                $status
            );

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);

                header('Location: kelola_tahun_ajaran.php');
                exit;
            } else {
                $error = 'Gagal menyimpan data tahun ajaran.';
            }

            mysqli_stmt_close($stmt);
        } else {
            $error = 'Terjadi kesalahan saat menyiapkan query.';
        }
    }
}

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

    <title>Tambah Tahun Ajaran - My Website</title>

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
    >

   <style>
        body {
            margin: 0;
            background: #f4f6f9;
            font-family: Arial, sans-serif;
            color: #1e293b;
        }

        /* SIDEBAR */
        .sidebar {
            background: #172554;
            color: white;
            min-height: 100vh;
            padding: 22px 15px;
        }

        .sidebar-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 30px;
            padding: 0 10px;
            line-height: 1.5;
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

        .sidebar .nav-link.logout {
            color: #fca5a5;
        }

        .sidebar hr {
            border-color: #64748b;
        }

        /* KONTEN */
        .main-content {
            padding: 28px;
        }

        .page-title {
            color: #172554;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .page-description {
            color: #64748b;
            margin-bottom: 25px;
        }

        /* KARTU TABEL */
        .content-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }

        .section-title {
            color: #172554;
            font-size: 19px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .btn-primary {
            background: #3157d5;
            border-color: #3157d5;
        }

        .btn-primary:hover {
            background: #2546b8;
            border-color: #2546b8;
        }

        /* TABEL */
        #tabelTahunAjaran thead th {
            background: #172554;
            color: white;
            vertical-align: middle;
            white-space: nowrap;
        }

        #tabelTahunAjaran tbody td {
            vertical-align: middle;
        }

        .table-responsive {
            margin-top: 20px;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-aktif {
            background: #dcfce7;
            color: #166534;
        }

        .status-nonaktif {
            background: #fee2e2;
            color: #991b1b;
        }

        .footer-text {
            color: #94a3b8;
            font-size: 13px;
            margin-top: 25px;
        }

        @media (max-width: 767px) {
            .sidebar {
                min-height: auto;
            }

            .main-content {
                padding: 18px;
            }

            .content-card {
                padding: 15px;
            }

            .page-title {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR -->
        <aside class="col-md-3 col-lg-2 sidebar">

            <div class="sidebar-title">
                Sistem Pelanggaran
            </div>

            <nav class="nav flex-column">

                <a href="dashboard.php" class="nav-link">
                    ⌂ Dashboard
                </a>

                <a href="kelola_siswa.php" class="nav-link">
                    ♟ Data Siswa
                </a>

                <a href="kelola_guru.php" class="nav-link">
                    ♟ Data Guru
                </a>

                <a href="kelola_kelas.php" class="nav-link">
                    ▦ Data Kelas
                </a>

                <a href="kelola_tahun_ajaran.php" class="nav-link active">
                    ◷ Kelola Tahun Ajaran
                </a>

                <a href="kelola_wali_kelas.php" class="nav-link">
                    ♙ Kelola Wali Kelas
                </a>

                <a href="kelola_penempatan_siswa.php" class="nav-link">
                    ⇄ Penempatan Siswa
                </a>

                <a href="kelola_kategori_pelanggaran.php" class="nav-link">
                    ☷ Kategori Pelanggaran
                </a>

                <a href="tentang_saya.php" class="nav-link">
                    ℹ About Me
                </a>

                <hr>

                <a href="logout.php"
                   class="nav-link logout"
                   onclick="return confirm('Yakin ingin logout?')">
                    ⇥ Logout
                </a>

            </nav>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 content">

            <h2 class="page-title">Tambah Tahun Ajaran</h2>

            <p class="page-description">
                Tambahkan data tahun ajaran baru ke dalam sistem.
            </p>

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

            <div class="form-card">

                <h5>Formulir Tahun Ajaran</h5>

                <p class="subtitle">
                    Lengkapi informasi tahun ajaran pada kolom berikut.
                    Kolom bertanda <span class="required">*</span> wajib diisi.
                </p>

                <form method="POST" action="">

                    <div class="form-group">
                        <label for="nama">
                            Nama Tahun Ajaran
                            <span class="required">*</span>
                        </label>

                        <input
                            type="text"
                            name="nama"
                            id="nama"
                            class="form-control"
                            placeholder="Contoh: 2026/2027"
                            value="<?= e($nama) ?>"
                            maxlength="100"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="tanggal_mulai">
                            Tanggal Mulai
                            <span class="required">*</span>
                        </label>

                        <input
                            type="date"
                            name="tanggal_mulai"
                            id="tanggal_mulai"
                            class="form-control"
                            value="<?= e($tanggal_mulai) ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="tanggal_selesai">
                            Tanggal Selesai
                            <span class="required">*</span>
                        </label>

                        <input
                            type="date"
                            name="tanggal_selesai"
                            id="tanggal_selesai"
                            class="form-control"
                            value="<?= e($tanggal_selesai) ?>"
                            min="<?= e($tanggal_mulai) ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="status_aktif">Status Tahun Ajaran</label>

                        <select
                            name="status_aktif"
                            id="status_aktif"
                            class="form-control"
                            required
                        >
                            <option
                                value="1"
                                <?= $status_aktif === '1' ? 'selected' : '' ?>
                            >
                                Aktif
                            </option>

                            <option
                                value="0"
                                <?= $status_aktif === '0' ? 'selected' : '' ?>
                            >
                                Tidak Aktif
                            </option>
                        </select>
                    </div>

                    <div class="form-actions d-flex flex-wrap">

                        <button
                            type="submit"
                            class="btn btn-primary mr-2 mb-2"
                        >
                            &#10003; Simpan Data
                        </button>

                        <a
                            href="kelola_tahun_ajaran.php"
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

<script>
    const tanggalMulai = document.getElementById('tanggal_mulai');
    const tanggalSelesai = document.getElementById('tanggal_selesai');

    tanggalMulai.addEventListener('change', function () {
        tanggalSelesai.min = this.value;

        if (
            tanggalSelesai.value !== '' &&
            tanggalSelesai.value < this.value
        ) {
            tanggalSelesai.value = '';
        }
    });
</script>

</body>
</html>
