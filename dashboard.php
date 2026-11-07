
<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Nama pengguna
$namaUser = $_SESSION['name']
    ?? $_SESSION['nama_user']
    ?? 'Administrator';

// Menghitung data dari database
function hitungData($koneksi, $tabel)
{
    // Hanya nama tabel yang ditentukan di bawah yang digunakan.
    $sql = "SELECT COUNT(*) AS total FROM `$tabel`";
    $hasil = mysqli_query($koneksi, $sql);

    if (!$hasil) {
        return 0;
    }

    $data = mysqli_fetch_assoc($hasil);
    mysqli_free_result($hasil);

    return (int) $data['total'];
}

$totalSiswa = hitungData($koneksi, 't_siswa');
$totalGuru  = hitungData($koneksi, 't_guru');
$totalKelas = hitungData($koneksi, 't_kelas');

// Hitung catatan pelanggaran jika tabel tersedia
$totalPelanggaran = hitungData($koneksi, 't_pelanggaran_siswa');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Dashboard - Sistem Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

 
    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
            margin: 0;
        }

        /* SIDEBAR - TETAP SEPERTI CONTOH */
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
            min-width: 0;
        }

        .page-title {
            color: #172554;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .page-description {
            color: #64748b;
        }

        /* KARTU */
        .card-main {
            background: white;
            border: none;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }

        /* TABEL */
        .table-responsive {
            overflow-x: auto;
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

        /* STATUS */
        .badge-status {
            padding: 7px 10px;
            border-radius: 5px;
            display: inline-block;
            white-space: nowrap;
        }

        .status-aktif {
            background: #d1fae5;
            color: #065f46;
        }

        .status-nonaktif {
            background: #fee2e2;
            color: #991b1b;
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

        @media (max-width: 767px) 
            .sidebar {
                min-height: auto;
            }

            .content {
                padding: 15px;
            }
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

        .welcome-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 25px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
            border-left: 5px solid #3157d5;
        }

        .welcome-card h4 {
            color: #172554;
            font-weight: bold;
        }

        .stat-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
            height: 100%;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-card .card-body {
            padding: 24px;
        }

        .stat-blue {
            background: #3157d5;
            color: white;
        }

        .stat-yellow {
            background: #facc15;
            color: #172554;
        }

        .stat-navy {
            background: #172554;
            color: white;
        }

        .stat-light {
            background: white;
            color: #172554;
            border-bottom: 4px solid #3157d5;
        }

        .stat-label {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .stat-number {
            font-size: 34px;
            font-weight: bold;
            margin: 0;
        }

        .stat-icon {
            font-size: 28px;
            opacity: 0.9;
        }

        .section-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-top: 25px;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.06);
        }

        .section-title {
            color: #172554;
            font-size: 19px;
            font-weight: bold;
            margin-bottom: 18px;
        }

        .quick-link {
            display: block;
            height: 100%;
            padding: 18px;
            border: 1px solid #e2e8f0;
            border-radius: 9px;
            text-decoration: none;
            color: #172554;
            transition: 0.2s;
        }

        .quick-link:hover {
            background: #eff6ff;
            border-color: #3157d5;
            color: #3157d5;
            transform: translateY(-2px);
        }

        .quick-link .quick-icon {
            font-size: 25px;
            margin-bottom: 10px;
        }

        .quick-link h6 {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .quick-link p {
            color: #64748b;
            font-size: 13px;
            margin: 0;
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

            .welcome-card,
            .section-card {
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

        <!-- SIDEBAR -->
        <aside class="col-md-3 col-lg-2 sidebar">

             <h4>My Website</h4>
            <nav class="nav flex-column">

                <a href="dashboard.php" class="nav-link active">
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

        <!-- KONTEN DASHBOARD -->
        <main class="col-md-9 col-lg-10 main-content">

            <h1 class="page-title">Dashboard</h1>

            <p class="page-description">
                Ringkasan informasi Sistem Pelanggaran Siswa.
            </p>

            <!-- UCAPAN SELAMAT DATANG -->
            <div class="welcome-card">
                <h4>
                    Selamat datang, <?= htmlspecialchars(
                        $namaUser,
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>!
                </h4>

                <p class="text-muted mb-0">
                    Anda berhasil login. Selamat bekerja dan mengelola
                    data Sistem Informasi Pelanggaran Siswa.
                </p>
            </div>

            <!-- KARTU STATISTIK -->
            <div class="row g-3">

                <div class="col-sm-6 col-xl-3">
                    <div class="card stat-card stat-blue">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Data Siswa</div>
                                    <h2 class="stat-number">
                                        <?= $totalSiswa; ?>
                                    </h2>
                                </div>
                                <div class="stat-icon">♟</div>
                            </div>
                            <small>Total siswa terdaftar</small>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card stat-card stat-yellow">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Data Guru</div>
                                    <h2 class="stat-number">
                                        <?= $totalGuru; ?>
                                    </h2>
                                </div>
                                <div class="stat-icon">♙</div>
                            </div>
                            <small>Total guru terdaftar</small>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card stat-card stat-navy">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Data Kelas</div>
                                    <h2 class="stat-number">
                                        <?= $totalKelas; ?>
                                    </h2>
                                </div>
                                <div class="stat-icon">▦</div>
                            </div>
                            <small>Total kelas tersedia</small>
                        </div>
                    </div>
                </div>

                <div class="col-sm-6 col-xl-3">
                    <div class="card stat-card stat-light">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-label">Catatan Pelanggaran</div>
                                    <h2 class="stat-number">
                                        <?= $totalPelanggaran; ?>
                                    </h2>
                                </div>
                                <div class="stat-icon">⚑</div>
                            </div>
                            <small>Total catatan tersimpan</small>
                        </div>
                    </div>
                </div>

            </div>

            <!-- MENU CEPAT -->
            <div class="section-card">
                <h5 class="section-title">Menu Pengelolaan</h5>

                <div class="row g-3">

                    <div class="col-md-6 col-xl-4">
                        <a href="kelola_siswa.php" class="quick-link">
                            <div class="quick-icon">♟</div>
                            <h6>Kelola Siswa</h6>
                            <p>Lihat dan kelola data siswa sekolah.</p>
                        </a>
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <a href="kelola_guru.php" class="quick-link">
                            <div class="quick-icon">♙</div>
                            <h6>Kelola Guru</h6>
                            <p>Lihat dan kelola data guru sekolah.</p>
                        </a>
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <a href="kelola_kelas.php" class="quick-link">
                            <div class="quick-icon">▦</div>
                            <h6>Kelola Kelas</h6>
                            <p>Atur data kelas yang tersedia.</p>
                        </a>
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <a href="kelola_tahun_ajaran.php" class="quick-link">
                            <div class="quick-icon">◷</div>
                            <h6>Tahun Ajaran</h6>
                            <p>Kelola periode tahun ajaran sekolah.</p>
                        </a>
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <a href="kelola_wali_kelas.php" class="quick-link">
                            <div class="quick-icon">♙</div>
                            <h6>Wali Kelas</h6>
                            <p>Kelola penugasan wali kelas.</p>
                        </a>
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <a href="kelola_penempatan_siswa.php" class="quick-link">
                            <div class="quick-icon">⇄</div>
                            <h6>Penempatan Siswa</h6>
                            <p>Atur penempatan siswa ke kelas.</p>
                        </a>
                    </div>

                    <div class="col-md-6 col-xl-4">
                        <a href="kelola_kategori_pelanggaran.php" class="quick-link">
                            <div class="quick-icon">☷</div>
                            <h6>Kategori Pelanggaran</h6>
                            <p>Kelola kategori pelanggaran siswa.</p>
                        </a>
                    </div>

                </div>
            </div>

            <p class="footer-text">
                Sistem Informasi Pelanggaran Siswa &copy; 2026
            </p>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
