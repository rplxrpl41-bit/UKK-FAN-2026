<?php
session_start();

include 'includes/cek_session.php';
include 'config/koneksi.php';

// ==========================================
// INISIALISASI VARIABEL
// ==========================================

$error = '';

$tahun_ajaran_id = '';
$kelas_id = '';
$guru_id = '';
$tanggal_mulai = '';
$tanggal_selesai = '';
$status_aktif = '1';

// ==========================================
// PROSES SIMPAN DATA
// ==========================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $tahun_ajaran_id = (int) ($_POST['tahun_ajaran_id'] ?? 0);
    $kelas_id = (int) ($_POST['kelas_id'] ?? 0);
    $guru_id = (int) ($_POST['guru_id'] ?? 0);

    $tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
    $tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');

    $status_aktif = (string) ($_POST['status_aktif'] ?? '1');

    // Validasi kolom wajib
    if (
        $tahun_ajaran_id <= 0 ||
        $kelas_id <= 0 ||
        $guru_id <= 0 ||
        $tanggal_mulai === '' ||
        $tanggal_selesai === ''
    ) {
        $error = 'Semua kolom wajib diisi.';
    }

    // Validasi status
    elseif (!in_array($status_aktif, ['0', '1'], true)) {
        $error = 'Status penugasan tidak valid.';
    }

    // Validasi format tanggal
    elseif (
        !DateTime::createFromFormat('Y-m-d', $tanggal_mulai) ||
        !DateTime::createFromFormat('Y-m-d', $tanggal_selesai)
    ) {
        $error = 'Format tanggal tidak valid.';
    }

    // Tanggal mulai tidak boleh melewati tanggal selesai
    elseif ($tanggal_mulai > $tanggal_selesai) {
        $error = 'Tanggal mulai tidak boleh melewati tanggal selesai.';
    }

    else {

        // ==========================================
        // CEK TAHUN AJARAN
        // ==========================================

        $cek_tahun = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_tahun_ajaran WHERE id = ? LIMIT 1"
        );

        if (!$cek_tahun) {
            $error = 'Gagal memeriksa tahun ajaran.';
        } else {

            mysqli_stmt_bind_param(
                $cek_tahun,
                "i",
                $tahun_ajaran_id
            );

            mysqli_stmt_execute($cek_tahun);
            mysqli_stmt_store_result($cek_tahun);

            if (mysqli_stmt_num_rows($cek_tahun) === 0) {
                $error = 'Tahun ajaran yang dipilih tidak ditemukan.';
            }

            mysqli_stmt_close($cek_tahun);
        }

        // ==========================================
        // CEK KELAS AKTIF
        // ==========================================

        if ($error === '') {

            $cek_kelas = mysqli_prepare(
                $koneksi,
                "SELECT id
                 FROM t_kelas
                 WHERE id = ? AND status_aktif = 1
                 LIMIT 1"
            );

            if (!$cek_kelas) {
                $error = 'Gagal memeriksa data kelas.';
            } else {

                mysqli_stmt_bind_param(
                    $cek_kelas,
                    "i",
                    $kelas_id
                );

                mysqli_stmt_execute($cek_kelas);
                mysqli_stmt_store_result($cek_kelas);

                if (mysqli_stmt_num_rows($cek_kelas) === 0) {
                    $error = 'Kelas tidak ditemukan atau sudah tidak aktif.';
                }

                mysqli_stmt_close($cek_kelas);
            }
        }

        // ==========================================
        // CEK GURU
        // ==========================================

        if ($error === '') {

            $cek_guru = mysqli_prepare(
                $koneksi,
                "SELECT id FROM t_guru WHERE id = ? LIMIT 1"
            );

            if (!$cek_guru) {
                $error = 'Gagal memeriksa data guru.';
            } else {

                mysqli_stmt_bind_param(
                    $cek_guru,
                    "i",
                    $guru_id
                );

                mysqli_stmt_execute($cek_guru);
                mysqli_stmt_store_result($cek_guru);

                if (mysqli_stmt_num_rows($cek_guru) === 0) {
                    $error = 'Guru yang dipilih tidak ditemukan.';
                }

                mysqli_stmt_close($cek_guru);
            }
        }

        // ==========================================
        // CEK DUPLIKASI WALI KELAS
        // ==========================================

        if ($error === '') {

            $cek = mysqli_prepare(
                $koneksi,
                "SELECT id
                 FROM t_wali_kelas
                 WHERE tahun_ajaran_id = ?
                   AND kelas_id = ?
                 LIMIT 1"
            );

            if (!$cek) {
                $error = 'Gagal memeriksa data wali kelas.';
            } else {

                mysqli_stmt_bind_param(
                    $cek,
                    "ii",
                    $tahun_ajaran_id,
                    $kelas_id
                );

                mysqli_stmt_execute($cek);
                mysqli_stmt_store_result($cek);

                if (mysqli_stmt_num_rows($cek) > 0) {
                    $error = 'Kelas tersebut sudah memiliki data wali kelas pada tahun ajaran yang dipilih.';
                }

                mysqli_stmt_close($cek);
            }
        }

        // ==========================================
        // SIMPAN DATA WALI KELAS
        // ==========================================

        if ($error === '') {

            $stmt = mysqli_prepare(
                $koneksi,
                "INSERT INTO t_wali_kelas
                (
                    tahun_ajaran_id,
                    kelas_id,
                    guru_id,
                    tanggal_mulai,
                    tanggal_selesai,
                    status_aktif
                )
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            if ($stmt) {

                $status = (int) $status_aktif;

                mysqli_stmt_bind_param(
                    $stmt,
                    "iiissi",
                    $tahun_ajaran_id,
                    $kelas_id,
                    $guru_id,
                    $tanggal_mulai,
                    $tanggal_selesai,
                    $status
                );

                if (mysqli_stmt_execute($stmt)) {

                    mysqli_stmt_close($stmt);

                    $_SESSION['pesan'] = 'Data wali kelas berhasil ditambahkan!';

                    header('Location: kelola_wali_kelas.php');
                    exit;

                } else {

                    $error = 'Gagal menyimpan data wali kelas: ' .
                             mysqli_stmt_error($stmt);
                }

                mysqli_stmt_close($stmt);

            } else {

                $error = 'Terjadi kesalahan saat menyiapkan query penyimpanan.';
            }
        }
    }
}

// ==========================================
// MENGAMBIL DATA TAHUN AJARAN
// ==========================================

$query_tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_tahun_ajaran
     ORDER BY id DESC"
);

// ==========================================
// MENGAMBIL DATA KELAS AKTIF
// ==========================================

$query_kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama, tingkat, jurusan
     FROM t_kelas
     WHERE status_aktif = 1
     ORDER BY nama ASC"
);

// ==========================================
// MENGAMBIL DATA GURU
// ==========================================

$query_guru = mysqli_query(
    $koneksi,
    "SELECT id, nama
     FROM t_guru
     ORDER BY nama ASC"
);

// Periksa query
if (!$query_tahun || !$query_kelas || !$query_guru) {
    die(
        'Gagal mengambil data pilihan: ' .
        mysqli_error($koneksi)
    );
}

// ==========================================
// FUNGSI KEAMANAN OUTPUT
// ==========================================

function e($nilai)
{
    return htmlspecialchars(
        (string) ($nilai ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Tambah Wali Kelas - My Website</title>

    <!-- BOOTSTRAP 4 -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
    >

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f4f6f9;
            font-family: Arial, sans-serif;
            color: #1e293b;
        }

        /* ==================================
           SIDEBAR
        ================================== */

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

        /* ==================================
           KONTEN UTAMA
        ================================== */

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
            line-height: 1.6;
        }

        /* ==================================
           FORM CARD
        ================================== */

        .form-card {
            background: white;
            border-radius: 12px;
            padding: 26px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
            max-width: 100%;
        }

        .form-card h5 {
            color: #172554;
            font-size: 19px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .required {
            color: #dc2626;
            font-weight: bold;
        }

        /* ==================================
           FORM INPUT
        ================================== */

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
        }

        .form-control {
            height: 44px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-size: 14px;
            color: #334155;
        }

        .form-control:focus {
            border-color: #3157d5;
            box-shadow: 0 0 0 3px rgba(49, 87, 213, 0.12);
        }

        select.form-control {
            cursor: pointer;
        }

        /* ==================================
           TOMBOL
        ================================== */

        .btn-primary {
            background: #3157d5;
            border-color: #3157d5;
            border-radius: 6px;
            padding: 9px 18px;
            font-weight: 600;
        }

        .btn-primary:hover {
            background: #2546b8;
            border-color: #2546b8;
        }

        .btn-secondary {
            border-radius: 6px;
            padding: 9px 18px;
        }

        .form-actions {
            margin-top: 25px;
            padding-top: 18px;
            border-top: 1px solid #e2e8f0;
        }

        /* ==================================
           FOOTER
        ================================== */

        footer {
            color: #94a3b8;
            font-size: 13px;
            text-align: center;
            margin-top: 25px;
            padding-bottom: 10px;
        }

        /* ==================================
           RESPONSIVE
        ================================== */

        @media (max-width: 767px) {

            .sidebar {
                min-height: auto;
            }

            .main-content {
                padding: 18px;
            }

            .form-card {
                padding: 18px;
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

        <!-- ==================================
             SIDEBAR
        ================================== -->

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

                <a href="kelola_wali_kelas.php"
                   class="nav-link active">
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

        <!-- ==================================
             KONTEN UTAMA
        ================================== -->

        <main class="col-md-9 col-lg-10 main-content">

            <h2 class="page-title">
                Tambah Wali Kelas
            </h2>

            <p class="page-description">
                Tambahkan penugasan guru sebagai wali kelas berdasarkan
                tahun ajaran dan kelas.
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

            <!-- ==================================
                 FORMULIR WALI KELAS
            ================================== -->

            <div class="form-card">

                <h5>
                    Formulir Data Wali Kelas
                </h5>

                <p class="subtitle">
                    Lengkapi informasi penugasan wali kelas.
                    Kolom bertanda
                    <span class="required">*</span>
                    wajib diisi.
                </p>

                <form method="POST" action="">

                    <!-- TAHUN AJARAN -->

                    <div class="form-group">

                        <label for="tahun_ajaran_id">

                            Tahun Ajaran
                            <span class="required">*</span>

                        </label>

                        <select
                            name="tahun_ajaran_id"
                            id="tahun_ajaran_id"
                            class="form-control"
                            required
                        >

                            <option value="">
                                -- Pilih Tahun Ajaran --
                            </option>

                            <?php while ($ta = mysqli_fetch_assoc($query_tahun)) { ?>

                                <option
                                    value="<?= (int) $ta['id'] ?>"
                                    <?= (string) $tahun_ajaran_id === (string) $ta['id']
                                        ? 'selected' : '' ?>
                                >

                                    <?= e($ta['nama']) ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <!-- KELAS -->

                    <div class="form-group">

                        <label for="kelas_id">

                            Kelas
                            <span class="required">*</span>

                        </label>

                        <select
                            name="kelas_id"
                            id="kelas_id"
                            class="form-control"
                            required
                        >

                            <option value="">
                                -- Pilih Kelas --
                            </option>

                            <?php while ($kelas = mysqli_fetch_assoc($query_kelas)) { ?>

                                <option
                                    value="<?= (int) $kelas['id'] ?>"
                                    <?= (string) $kelas_id === (string) $kelas['id']
                                        ? 'selected' : '' ?>
                                >

                                    <?= e(
                                        $kelas['nama'] . ' - ' .
                                        $kelas['tingkat'] . ' - ' .
                                        $kelas['jurusan']
                                    ) ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <!-- GURU -->

                    <div class="form-group">

                        <label for="guru_id">

                            Guru Wali Kelas
                            <span class="required">*</span>

                        </label>

                        <select
                            name="guru_id"
                            id="guru_id"
                            class="form-control"
                            required
                        >

                            <option value="">
                                -- Pilih Guru --
                            </option>

                            <?php while ($guru = mysqli_fetch_assoc($query_guru)) { ?>

                                <option
                                    value="<?= (int) $guru['id'] ?>"
                                    <?= (string) $guru_id === (string) $guru['id']
                                        ? 'selected' : '' ?>
                                >

                                    <?= e($guru['nama']) ?>

                                </option>

                            <?php } ?>

                        </select>

                    </div>

                    <!-- TANGGAL MULAI -->

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

                    <!-- TANGGAL SELESAI -->

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

                    <!-- STATUS PENUGASAN -->

                    <div class="form-group">

                        <label for="status_aktif">
                            Status Penugasan
                        </label>

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

                    <!-- TOMBOL -->

                    <div class="form-actions d-flex flex-wrap">

                        <button
                            type="submit"
                            class="btn btn-primary mr-2 mb-2"
                        >

                            &#10003; Simpan Data

                        </button>

                        <a
                            href="kelola_wali_kelas.php"
                            class="btn btn-secondary mb-2"
                        >

                            Kembali

                        </a>

                    </div>

                </form>

            </div>

            <!-- FOOTER -->

            <footer>

                &copy; <?= date('Y') ?>
                My Website. All rights reserved.

            </footer>

        </main>

    </div>

</div>

<!-- JAVASCRIPT -->

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>

    // ==================================
    // VALIDASI TANGGAL
    // ==================================

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