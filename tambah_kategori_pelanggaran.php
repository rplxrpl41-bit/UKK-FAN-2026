<?php
session_start();

include 'includes/cek_session.php';
include 'config/koneksi.php';

$pesan = '';
$jenisPesan = '';

function e($data) {
    return htmlspecialchars((string)$data, ENT_QUOTES, 'UTF-8');
}

$nama = '';
$deskripsi = '';
$status_aktif = '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status_aktif = $_POST['status_aktif'] ?? '1';

    if ($nama === '') {
        $pesan = 'Nama kategori wajib diisi!';
        $jenisPesan = 'danger';
    } elseif (!in_array($status_aktif, ['0', '1'], true)) {
        $pesan = 'Status kategori tidak valid!';
        $jenisPesan = 'danger';
    } else {
        // Cek apakah kategori sudah ada
        $stmt = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_kategori_pelanggaran WHERE nama = ? LIMIT 1"
        );

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 's', $nama);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);

            $sudahAda = mysqli_stmt_num_rows($stmt) > 0;
            mysqli_stmt_close($stmt);

            if ($sudahAda) {
                $pesan = 'Kategori pelanggaran tersebut sudah ada!';
                $jenisPesan = 'danger';
            } else {
                $status = (int)$status_aktif;

                $stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO t_kategori_pelanggaran
                    (nama, deskripsi, status_aktif)
                    VALUES (?, ?, ?)"
                );

                if ($stmt) {
                    mysqli_stmt_bind_param(
                        $stmt,
                        'ssi',
                        $nama,
                        $deskripsi,
                        $status
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        header('Location: kelola_kategori_pelanggaran.php?pesan=tambah_berhasil');
                        exit;
                    } else {
                        $pesan = 'Gagal menyimpan kategori. Periksa struktur tabel database.';
                        $jenisPesan = 'danger';
                        mysqli_stmt_close($stmt);
                    }
                } else {
                    $pesan = 'Query gagal disiapkan. Periksa nama kolom pada database.';
                    $jenisPesan = 'danger';
                }
            }
        } else {
            $pesan = 'Tabel atau kolom kategori tidak sesuai dengan kode.';
            $jenisPesan = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tambah Kategori Pelanggaran</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

  
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

        /* KARTU */
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
        #tabelKategori thead th {
            background: #172554;
            color: white;
            vertical-align: middle;
            white-space: nowrap;
        }

        #tabelKategori tbody td {
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

                <a href="kelola_tahun_ajaran.php" class="nav-link">
                    ◷ Kelola Tahun Ajaran
                </a>

                <a href="kelola_wali_kelas.php" class="nav-link">
                    ♙ Kelola Wali Kelas
                </a>

                <a href="kelola_penempatan_siswa.php" class="nav-link">
                    ⇄ Penempatan Siswa
                </a>

                <a href="kelola_kategori_pelanggaran.php"
                   class="nav-link active">
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

<main class="content">
    <h1 class="page-title">Tambah Kategori Pelanggaran</h1>
    <p class="page-subtitle">
        Tambahkan kategori baru untuk mengelompokkan jenis pelanggaran siswa.
    </p>

    <div class="form-card">
        <h5 class="mb-4">Formulir Kategori Pelanggaran</h5>

        <?php if ($pesan !== ''): ?>
            <div class="alert alert-<?php echo e($jenisPesan); ?>">
                <?php echo e($pesan); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="nama">
                    Nama Kategori <span class="required">*</span>
                </label>
                <input
                    type="text"
                    name="nama"
                    id="nama"
                    class="form-control"
                    placeholder="Contoh: Kedisiplinan"
                    maxlength="100"
                    value="<?php echo e($nama); ?>"
                    required
                >
                <small class="form-text text-muted">
                    Masukkan nama kategori pelanggaran.
                </small>
            </div>

            <div class="form-group">
                <label for="deskripsi">Deskripsi Kategori</label>
                <textarea
                    name="deskripsi"
                    id="deskripsi"
                    class="form-control"
                    placeholder="Tuliskan keterangan kategori (opsional)"
                ><?php echo e($deskripsi); ?></textarea>
            </div>

            <div class="form-group">
                <label for="status_aktif">Status Kategori</label>
                <select
                    name="status_aktif"
                    id="status_aktif"
                    class="form-control"
                    required
                >
                    <option value="1" <?php echo $status_aktif === '1' ? 'selected' : ''; ?>>
                        Aktif
                    </option>
                    <option value="0" <?php echo $status_aktif === '0' ? 'selected' : ''; ?>>
                        Tidak Aktif
                    </option>
                </select>
            </div>

            <hr>

            <div class="d-flex flex-wrap">
                <button type="submit" class="btn btn-primary mr-2 mb-2">
                    ðŸ’¾ Simpan Kategori
                </button>

                <a href="kelola_kategori_pelanggaran.php"
                   class="btn btn-secondary mb-2">
                    â†© Kembali
                </a>
            </div>
        </form>
    </div>
</main>

</body>
</html>
