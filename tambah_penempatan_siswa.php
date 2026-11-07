
<?php
session_start();

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Khusus admin
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak!");
}

$error = '';

$siswa_id = '';
$kelas_id = '';
$tahun_ajaran_id = '';
$tanggal_mulai = date('Y-m-d');
$tanggal_selesai = '';
$status_aktif = '1';

// Fungsi keamanan output
function e($nilai)
{
    return htmlspecialchars(
        (string) ($nilai ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

// Ambil data pilihan dari database
$querySiswa = mysqli_query(
    $koneksi,
    "SELECT id, nis, nama
     FROM t_siswa
     ORDER BY nama ASC"
);

$queryKelas = mysqli_query(
    $koneksi,
    "SELECT id, nama, tingkat, jurusan
     FROM t_kelas
     WHERE tingkat = 'XI' AND jurusan = 'RPL'
       AND nama IN ('XI RPL1', 'XI RPL2')
     ORDER BY nama ASC"
);

$queryTahun = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_tahun_ajaran
     ORDER BY id DESC"
);

if (!$querySiswa || !$queryKelas || !$queryTahun) {
    die("Gagal mengambil data pilihan: " . mysqli_error($koneksi));
}

// Proses tambah penempatan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $siswa_id = (int) ($_POST['siswa_id'] ?? 0);
    $kelas_id = (int) ($_POST['kelas_id'] ?? 0);
    $tahun_ajaran_id = (int) ($_POST['tahun_ajaran_id'] ?? 0);

    $tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
    $tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');

    $status_aktif = (int) ($_POST['status_aktif'] ?? 1);

    // Validasi input
    if (
        $siswa_id <= 0 ||
        $kelas_id <= 0 ||
        $tahun_ajaran_id <= 0 ||
        $tanggal_mulai === ''
    ) {
        $error = "Semua data wajib diisi kecuali tanggal selesai.";
    } elseif (!in_array($status_aktif, [0, 1], true)) {
        $error = "Status penempatan tidak valid.";
    } elseif (
        $tanggal_selesai !== '' &&
        $tanggal_selesai < $tanggal_mulai
    ) {
        $error = "Tanggal selesai tidak boleh sebelum tanggal mulai.";
    } else {

        // Periksa apakah siswa, kelas, dan tahun ajaran tersedia
        $stmtValidasi = mysqli_prepare(
            $koneksi,
            "SELECT
                (SELECT COUNT(*) FROM t_siswa WHERE id = ?) AS siswa,
                (SELECT COUNT(*) FROM t_kelas WHERE id = ?) AS kelas,
                (SELECT COUNT(*) FROM t_tahun_ajaran WHERE id = ?) AS tahun"
        );

        mysqli_stmt_bind_param(
            $stmtValidasi,
            "iii",
            $siswa_id,
            $kelas_id,
            $tahun_ajaran_id
        );

        mysqli_stmt_execute($stmtValidasi);
        $validasi = mysqli_fetch_assoc(
            mysqli_stmt_get_result($stmtValidasi)
        );
        mysqli_stmt_close($stmtValidasi);

        if (
            !$validasi ||
            (int) $validasi['siswa'] === 0 ||
            (int) $validasi['kelas'] === 0 ||
            (int) $validasi['tahun'] === 0
        ) {
            $error = "Data siswa, kelas, atau tahun ajaran tidak ditemukan.";
        } else {

            // Cegah penempatan aktif ganda pada tahun ajaran yang sama
            if ($status_aktif === 1) {

                $stmtCek = mysqli_prepare(
                    $koneksi,
                    "SELECT id
                     FROM t_kelas_siswa
                     WHERE siswa_id = ?
                       AND tahun_ajaran_id = ?
                       AND status_aktif = 1
                     LIMIT 1"
                );

                mysqli_stmt_bind_param(
                    $stmtCek,
                    "ii",
                    $siswa_id,
                    $tahun_ajaran_id
                );

                mysqli_stmt_execute($stmtCek);

                $dataCek = mysqli_fetch_assoc(
                    mysqli_stmt_get_result($stmtCek)
                );

                mysqli_stmt_close($stmtCek);

                if ($dataCek) {
                    $error = "Siswa sudah memiliki penempatan aktif pada tahun ajaran ini.";
                }
            }

            // Simpan data jika tidak ada error
            if ($error === '') {

                if ($tanggal_selesai === '') {
                    $tanggal_selesai = null;
                }

                $stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO t_kelas_siswa
                    (
                        siswa_id,
                        kelas_id,
                        tahun_ajaran_id,
                        tanggal_mulai,
                        tanggal_selesai,
                        status_aktif
                    )
                    VALUES (?, ?, ?, ?, ?, ?)"
                );

                if ($stmt) {

                    mysqli_stmt_bind_param(
                        $stmt,
                        "iiissi",
                        $siswa_id,
                        $kelas_id,
                        $tahun_ajaran_id,
                        $tanggal_mulai,
                        $tanggal_selesai,
                        $status_aktif
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);

                        $_SESSION['pesan'] = "Data penempatan siswa berhasil ditambahkan!";

                        header("Location: kelola_penempatan_siswa.php");
                        exit;
                    } else {
                        $error = "Gagal menyimpan data penempatan siswa.";
                    }

                    mysqli_stmt_close($stmt);

                } else {
                    $error = "Terjadi kesalahan saat menyiapkan query.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Penempatan Siswa</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
            margin: 0;
        }

        /* SIDEBAR */
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
        .main-content {
            padding: 28px;
            min-width: 0;
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

        /* FORM CARD */
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }

        .form-card h5 {
            color: #172554;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .form-label {
            color: #172554;
            font-weight: 600;
        }

        .required {
            color: #dc3545;
        }

        .form-control,
        .form-select {
            border-radius: 7px;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #3157d5;
            box-shadow: 0 0 0 0.2rem rgba(49, 87, 213, 0.15);
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

        .form-actions {
            margin-top: 25px;
        }

        footer {
            color: #94a3b8;
            font-size: 13px;
            margin-top: 25px;
            text-align: center;
        }

        /* RESPONSIVE */
        @media (max-width: 767px) {
            .sidebar {
                min-height: auto;
            }

            .main-content {
                padding: 18px;
            }

            .form-card {
                padding: 20px;
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

            <h4>My Website</h4>

            <ul class="nav nav-pills flex-column">

                <li>
                    <a href="dashboard.php" class="nav-link">
                        &#8962; Dashboard
                    </a>
                </li>

                <li>
                    <a href="kelola_siswa.php" class="nav-link">
                        &#9817; Data Siswa
                    </a>
                </li>

                <li>
                    <a href="kelola_guru.php" class="nav-link">
                        &#9817; Data Guru
                    </a>
                </li>

                <li>
                    <a href="kelola_kelas.php" class="nav-link">
                        &#9638; Data Kelas
                    </a>
                </li>

                <li>
                    <a href="kelola_tahun_ajaran.php" class="nav-link">
                        &#9719; Kelola Tahun Ajaran
                    </a>
                </li>

                <li>
                    <a href="kelola_wali_kelas.php" class="nav-link">
                        &#9817; Kelola Wali Kelas
                    </a>
                </li>

                <li>
                    <a href="kelola_penempatan_siswa.php" class="nav-link active">
                        &#8644; Penempatan Siswa
                    </a>
                </li>

                <li>
                    <a href="kelola_kategori_pelanggaran.php" class="nav-link">
                        &#9776; Kategori Pelanggaran
                    </a>
                </li>

                <li>
                    <a href="tentang_saya.php" class="nav-link">
                        &#8505; About Me
                    </a>
                </li>

                <hr style="border-color: #475569;">

                <li>
                    <a
                        href="logout.php"
                        class="nav-link logout"
                        onclick="return confirm('Yakin ingin logout?')"
                    >
                        &#10140; Logout
                    </a>
                </li>

            </ul>
        </aside>

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 main-content">

            <h2 class="page-title">Tambah Penempatan Siswa</h2>

            <p class="page-description">
                Tambahkan penempatan siswa ke kelas dan tahun ajaran tertentu.
            </p>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= e($error) ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Tutup"
                    ></button>
                </div>
            <?php endif; ?>

            <div class="form-card">

                <h5>Formulir Penempatan Siswa</h5>

                <p class="subtitle">
                    Lengkapi informasi penempatan siswa.
                    Kolom bertanda <span class="required">*</span> wajib diisi.
                </p>

                <form method="POST" action="">

                    <!-- SISWA -->
                    <div class="mb-3">
                        <label for="siswa_id" class="form-label">
                            Nama Siswa <span class="required">*</span>
                        </label>

                        <select
                            name="siswa_id"
                            id="siswa_id"
                            class="form-select"
                            required
                        >
                            <option value="">-- Pilih Siswa --</option>

                            <?php while ($siswa = mysqli_fetch_assoc($querySiswa)): ?>
                                <option
                                    value="<?= (int) $siswa['id'] ?>"
                                    <?= (int) $siswa_id === (int) $siswa['id'] ? 'selected' : '' ?>
                                >
                                    <?= e(trim(($siswa['nis'] ?? '') . ' - ' . ($siswa['nama'] ?? ''), ' -')) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- KELAS -->
                    <div class="mb-3">
                        <label for="kelas_id" class="form-label">
                            Kelas <span class="required">*</span>
                        </label>

                        <select
                            name="kelas_id"
                            id="kelas_id"
                            class="form-select"
                            required
                        >
                            <option value="">-- Pilih Kelas --</option>

                            <?php while ($kelas = mysqli_fetch_assoc($queryKelas)): ?>
                                <option
                                    value="<?= (int) $kelas['id'] ?>"
                                    <?= (int) $kelas_id === (int) $kelas['id'] ? 'selected' : '' ?>
                                >
                                    <?= e(trim(($kelas['nama'] ?? '') . ' ' . ($kelas['tingkat'] ?? '') . ' ' . ($kelas['jurusan'] ?? ''))) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- TAHUN AJARAN -->
                    <div class="mb-3">
                        <label for="tahun_ajaran_id" class="form-label">
                            Tahun Ajaran <span class="required">*</span>
                        </label>

                        <select
                            name="tahun_ajaran_id"
                            id="tahun_ajaran_id"
                            class="form-select"
                            required
                        >
                            <option value="">-- Pilih Tahun Ajaran --</option>

                            <?php while ($tahun = mysqli_fetch_assoc($queryTahun)): ?>
                                <option
                                    value="<?= (int) $tahun['id'] ?>"
                                    <?= (int) $tahun_ajaran_id === (int) $tahun['id'] ? 'selected' : '' ?>
                                >
                                    <?= e($tahun['nama']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- TANGGAL -->
                    <div class="row">

                        <div class="col-md-6 mb-3">
                            <label for="tanggal_mulai" class="form-label">
                                Tanggal Mulai <span class="required">*</span>
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

                        <div class="col-md-6 mb-3">
                            <label for="tanggal_selesai" class="form-label">
                                Tanggal Selesai
                            </label>

                            <input
                                type="date"
                                name="tanggal_selesai"
                                id="tanggal_selesai"
                                class="form-control"
                                value="<?= e($tanggal_selesai) ?>"
                            >

                            <small class="text-muted">
                                Kosongkan jika penempatan masih berlangsung.
                            </small>
                        </div>

                    </div>

                    <!-- STATUS -->
                    <div class="mb-3">
                        <label for="status_aktif" class="form-label">
                            Status Penempatan
                        </label>

                        <select
                            name="status_aktif"
                            id="status_aktif"
                            class="form-select"
                            required
                        >
                            <option
                                value="1"
                                <?= $status_aktif === 1 ? 'selected' : '' ?>
                            >
                                Aktif
                            </option>

                            <option
                                value="0"
                                <?= $status_aktif === 0 ? 'selected' : '' ?>
                            >
                                Tidak Aktif
                            </option>
                        </select>
                    </div>

                    <!-- TOMBOL -->
                    <div class="form-actions d-flex flex-wrap">

                        <button
                            type="submit"
                            class="btn btn-primary me-2 mb-2"
                        >
                            &#10003; Simpan Data
                        </button>

                        <a
                            href="kelola_penempatan_siswa.php"
                            class="btn btn-secondary mb-2"
                        >
                            Kembali
                        </a>

                    </div>

                </form>

            </div>

            <footer>
                &copy; <?= date('Y') ?> Sistem Pelanggaran Siswa - UKK 2026
            </footer>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
