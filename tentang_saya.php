<?php
include 'includes/cek_session.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Me | Zulfan</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
            font-family: Arial, sans-serif;
            color: #1e293b;
            margin: 0;
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

        .profile-card {
            background: white;
            border-radius: 25px;
            padding: 40px;
            max-width: 750px;
            width: 100%;
            margin: 0 auto;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .profile-icon {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2563eb, #06b6d4);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 45px;
            font-weight: bold;
            color: white;
            margin: 0 auto 20px;
        }

        .subtitle {
            color: #2563eb;
            font-weight: bold;
            letter-spacing: 3px;
            font-size: 13px;
        }

        .info-box {
            background: #f1f5f9;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 12px;
        }

        .btn-custom {
            background: #2563eb;
            color: white;
            border-radius: 10px;
            padding: 12px 25px;
            text-decoration: none;
            display: inline-block;
        }

        .btn-custom:hover {
            background: #1d4ed8;
            color: white;
        }

        @media (max-width: 767px) {
            .sidebar {
                min-height: auto;
            }

            .main-content {
                padding: 18px;
            }

            .profile-card {
                padding: 25px;
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

                <a href="kelola_kategori_pelanggaran.php" class="nav-link">
                    ☷ Kategori Pelanggaran
                </a>

                <a href="tentang_saya.php" class="nav-link active">
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
        <main class="col-md-9 col-lg-10 main-content">

            <div class="profile-card text-center">

                <div class="profile-icon">Z</div>

                <p class="subtitle mb-2">ABOUT ME</p>

                <h1 class="fw-bold">
                    Muhammad Zulfan Nuralfansyah
                </h1>

                <p class="text-secondary">
                    Student | XII RPL 1
                </p>

                <hr class="my-4">

                <h4 class="fw-bold text-start mb-3">
                    👋 Perkenalkan, Saya!
                </h4>

                <p class="text-secondary text-start"
                   style="line-height: 1.9;">
                    Halo semuanya! Perkenalkan, nama saya
                    <b>Muhammad Zulfan Nuralfansyah</b>,
                    siswa kelas XII RPL 1. Saya merupakan siswa
                    Rekayasa Perangkat Lunak (RPL) yang tertarik
                    dengan dunia teknologi, komputer, dan pembuatan
                    website.

                    <br><br>

                    Melalui pembelajaran di sekolah, saya terus
                    berusaha mengembangkan kemampuan dalam bidang
                    pemrograman, desain web, serta pengembangan
                    aplikasi. Saya percaya bahwa kerja keras,
                    kreativitas, dan kemauan untuk terus belajar
                    merupakan kunci untuk meraih masa depan yang lebih baik.
                </p>

                <div class="text-start mt-4">
                    <div class="info-box">
                        <strong>👤 Nama Lengkap</strong>
                        <div class="text-secondary">
                            Muhammad Zulfan Nuralfansyah
                        </div>
                    </div>

                    <div class="info-box">
                        <strong>🎓 Kelas</strong>
                        <div class="text-secondary">
                            XII RPL 1
                        </div>
                    </div>

                    <div class="info-box">
                        <strong>💻 Bidang yang Dipelajari</strong>
                        <div class="text-secondary">
                            Pemrograman, Web Development, dan Teknologi
                        </div>
                    </div>
                </div>

                <p class="fst-italic text-secondary mt-4">
                    "Terus belajar, berani mencoba, dan jangan pernah
                    berhenti berkembang."
                </p>

                <a href="dashboard.php" class="btn-custom mt-2">
                    ← Kembali ke Dashboard
                </a>

                <p class="text-secondary small mt-4 mb-0">
                    &copy; 2026 Muhammad Zulfan Nuralfansyah
                </p>

            </div>

        </main>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
