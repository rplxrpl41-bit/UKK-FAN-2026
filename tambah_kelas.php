
<?php
session_start();

include 'includes/cek_session.php';
include 'config/koneksi.php';

// =====================================
// INISIALISASI VARIABEL
// =====================================
$error = '';
$nama = '';
$tingkat = 'XII';
$jurusan = 'RPL';
$status_aktif = '1';

// =====================================
// PROSES SIMPAN DATA KELAS
// =====================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama = trim($_POST['nama'] ?? '');
    $status_aktif = (string) ($_POST['status_aktif'] ?? '1');

    // Tingkat dan jurusan ditentukan otomatis
    $tingkat = 'XII';
    $jurusan = 'RPL';

    // Validasi nama kelas
    if (!in_array($nama, ['XII RPL1', 'XII RPL2'], true)) {

        $error = 'Silakan pilih kelas XII RPL1 atau XII RPL2.';

    } elseif (!in_array($status_aktif, ['0', '1'], true)) {

        $error = 'Status kelas tidak valid.';

    } else {

        // Periksa apakah nama kelas sudah tersedia
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_kelas WHERE nama = ? LIMIT 1"
        );

        if (!$cek) {

            $error = 'Terjadi kesalahan saat memeriksa data kelas.';

        } else {

            mysqli_stmt_bind_param($cek, 's', $nama);
            mysqli_stmt_execute($cek);

            $hasilCek = mysqli_stmt_get_result($cek);
            $sudahAda = mysqli_num_rows($hasilCek) > 0;

            mysqli_stmt_close($cek);

            if ($sudahAda) {

                $error = 'Kelas tersebut sudah tersedia. Tidak boleh menambahkan kelas yang sama dua kali.';

            } else {

                // Simpan data kelas
                $stmt = mysqli_prepare(
                    $koneksi,
                    "INSERT INTO t_kelas
                    (nama, tingkat, jurusan, status_aktif)
                    VALUES (?, ?, ?, ?)"
                );

                if ($stmt) {

                    $statusInt = (int) $status_aktif;

                    mysqli_stmt_bind_param(
                        $stmt,
                        'sssi',
                        $nama,
                        $tingkat,
                        $jurusan,
                        $statusInt
                    );

                    if (mysqli_stmt_execute($stmt)) {

                        mysqli_stmt_close($stmt);

                        header('Location: kelola_kelas.php');
                        exit;

                    } else {

                        $error = 'Gagal menyimpan data kelas: ' . mysqli_stmt_error($stmt);

                    }

                    mysqli_stmt_close($stmt);

                } else {

                    $error = 'Terjadi kesalahan saat menyiapkan query.';

                }
            }
        }
    }
}

// =====================================
// FUNGSI HTML ESCAPE
// =====================================
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Kelas - My Website</title>

    <!-- BOOTSTRAP -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
    >

    <style>

        * {
            box-sizing: border-box;
        }

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

        /* KONTEN UTAMA */
        .content {
            padding: 28px;
            min-width: 0;
        }

        .page-title {
            color: #172554;
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .page-description {
            color: #64748b;
            margin-bottom: 25px;
        }

        /* KARTU FORMULIR */
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
            border: none;
        }

        .form-card h5 {
            color: #172554;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .subtitle {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .required {
            color: #dc3545;
            font-weight: bold;
        }

        /* FORM */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            color: #334155;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control {
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            min-height: 42px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #3157d5;
            box-shadow: 0 0 0 0.15rem rgba(49, 87, 213, 0.15);
        }

        .form-control[readonly] {
            background: #e9eef5;
            color: #475569;
            cursor: not-allowed;
        }

        /* TOMBOL */
        .btn-primary {
            background: #3157d5;
            border-color: #3157d5;
            border-radius: 6px;
            padding: 9px 18px;
        }

        .btn-primary:hover {
            background: #2445b8;
            border-color: #2445b8;
        }

        .btn-secondary {
            border-radius: 6px;
            padding: 9px 18px;
        }

        /* FOOTER */
        footer {
            color: #94a3b8;
            font-size: 13px;
            text-align: center;
            margin-top: 25px;
            padding-bottom: 10px;
        }

        /* RESPONSIVE */
        @media (max-width: 767px) {

            .sidebar {
                min-height: auto;
            }

            .content {
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

        <!-- ================================= -->
        <!-- SIDEBAR -->
        <!-- ================================= -->

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
                    <a href="kelola_kelas.php" class="nav-link active">
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
                    <a href="kelola_penempatan_siswa.php" class="nav-link">
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


        <!-- ================================= -->
        <!-- KONTEN UTAMA -->
        <!-- ================================= -->

        <main class="col-md-9 col-lg-10 content">

            <h2 class="page-title">
                Tambah Kelas
            </h2>

            <p class="page-description">
                Tambahkan data kelas baru ke dalam sistem.
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


            <!-- ================================= -->
            <!-- FORMULIR DATA KELAS -->
            <!-- ================================= -->

            <div class="form-card">

                <h5>Formulir Data Kelas</h5>

                <p class="subtitle">
                    Lengkapi informasi kelas pada kolom berikut.
                    Kolom bertanda
                    <span class="required">*</span>
                    wajib diisi.
                </p>


                <form method="POST" action="">

                    <!-- NAMA KELAS -->

                    <div class="form-group">

                        <label for="nama">
                            Nama Kelas
                            <span class="required">*</span>
                        </label>

                        <select
                            name="nama"
                            id="nama"
                            class="form-control"
                            required
                        >

                            <option value="">
                                -- Pilih Kelas --
                            </option>

                            <option
                                value="XII RPL1"
                                <?= $nama === 'XII RPL1' ? 'selected' : '' ?>
                            >
                                XII RPL1
                            </option>

                            <option
                                value="XII RPL2"
                                <?= $nama === 'XII RPL2' ? 'selected' : '' ?>
                            >
                                XII RPL2
                            </option>

                        </select>

                    </div>


                    <!-- TINGKAT -->

                    <div class="form-group">

                        <label for="tingkat">
                            Tingkat
                        </label>

                        <input
                            type="text"
                            id="tingkat"
                            class="form-control"
                            value="XII"
                            readonly
                        >

                    </div>


                    <!-- JURUSAN -->

                    <div class="form-group">

                        <label for="jurusan">
                            Jurusan
                        </label>

                        <input
                            type="text"
                            id="jurusan"
                            class="form-control"
                            value="RPL"
                            readonly
                        >

                    </div>


                    <!-- STATUS KELAS -->

                    <div class="form-group">

                        <label for="status_aktif">
                            Status Kelas
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
                            href="kelola_kelas.php"
                            class="btn btn-secondary mb-2"
                        >
                            Kembali
                        </a>

                    </div>

                </form>

            </div>


            <!-- FOOTER -->

            <footer>
                &copy; <?= date('Y') ?> My Website. All rights reserved.
            </footer>

        </main>

    </div>

</div>


<!-- JAVASCRIPT BOOTSTRAP -->

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
