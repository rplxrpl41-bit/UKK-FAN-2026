
<?php
session_start();
include 'config/koneksi.php';

// Cek login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Cek hak akses admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    exit("Akses ditolak!");
}

// Ambil data siswa
$sql = "SELECT * FROM t_siswa ORDER BY id DESC";
$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die("Gagal mengambil data siswa: " . mysqli_error($koneksi));
}

// Fungsi untuk mengamankan output HTML
function e($nilai) {
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Kelola Siswa</title>

    <!-- Bootstrap 4 -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <!-- DataTables Bootstrap 4 -->
    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">

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

        <!-- KONTEN UTAMA -->
        <main class="col-md-9 col-lg-10 content">

            <div class="d-flex flex-wrap justify-content-between
                        align-items-center mb-4">

                <div>
                    <h2 class="page-title">Kelola Siswa</h2>
                    <p class="text-muted mb-0">
                        Kelola dan cari data siswa sekolah.
                    </p>
                </div>

                <a href="tambah_siswa.php"
                   class="btn btn-primary mt-3 mt-md-0">
                    + Tambah Siswa
                </a>

            </div>

            <!-- TABEL DATA SISWA -->
            <div class="card card-main">
                <div class="card-body">

                    <h5 class="font-weight-bold mb-3">
                        Daftar Data Siswa
                    </h5>

                    <div class="table-responsive">

                        <table id="tabelSiswa"
                               class="table table-bordered table-striped
                                      table-hover table-sm"
                               style="width:100%">

                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>NIS</th>
                                    <th>NISN</th>
                                    <th>Nama</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Alamat</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                            <?php
                            $no = 1;

                            while ($siswa = mysqli_fetch_assoc($hasil)) {
                            ?>
                                <tr>
                                    <td><?= $no++ ?></td>

                                    <td><?= e($siswa['nis']) ?></td>

                                    <td><?= e($siswa['nisn']) ?></td>

                                    <td><?= e($siswa['nama']) ?></td>

                                    <td>
                                        <?php
                                        if ($siswa['jenis_kelamin'] == 'L') {
                                            echo 'Laki-laki';
                                        } elseif ($siswa['jenis_kelamin'] == 'P') {
                                            echo 'Perempuan';
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>

                                    <td>
                                        <?= e($siswa['tanggal_lahir']) ?>
                                    </td>

                                    <td><?= e($siswa['alamat']) ?></td>

                                    <td>
                                        <?php if ((int)$siswa['status_aktif'] === 1) { ?>
                                            <span class="badge-status status-aktif">
                                                Aktif
                                            </span>
                                        <?php } else { ?>
                                            <span class="badge-status status-nonaktif">
                                                Tidak Aktif
                                            </span>
                                        <?php } ?>
                                    </td>

                                    <td><?= e($siswa['created_at'] ?? '') ?></td>

                                    <td><?= e($siswa['updated_at'] ?? '') ?></td>

                                    <td>
                                        <div class="action-buttons">

                                            <a
                                                href="edit_siswa.php?id=<?= (int)$siswa['id'] ?>"
                                                class="btn btn-warning btn-sm">
                                                Edit
                                            </a>

                                            <a
                                                href="hapus_siswa.php?id=<?= (int)$siswa['id'] ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('Yakin ingin menghapus siswa ini?')">
                                                Hapus
                                            </a>

                                        </div>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                            </tbody>

                        </table>

                    </div>

                    <a href="dashboard.php" class="btn btn-secondary btn-sm mt-3">
                        Kembali ke Dashboard
                    </a>

                </div>
            </div>

            <p class="text-muted text-center mt-4">
                &copy; <?= date('Y') ?> Sistem Informasi Pelanggaran Siswa
            </p>

        </main>

    </div>
</div>

<!-- jQuery: harus dimuat sebelum DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables Core -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<!-- DataTables Bootstrap 4 -->
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<!-- Konfigurasi DataTables -->
<script>
$(document).ready(function () {

    $('#tabelSiswa').DataTable({
        processing: true,

        // Urutkan berdasarkan ID terbaru (kolom ID tidak ditampilkan)
        order: [[0, 'asc']],

        // Pilihan jumlah data per halaman
        pageLength: 10,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, 'Semua']
        ],

        // Kolom tanggal dan teks mengikuti urutan standar
        autoWidth: false,

        // Pengaturan bahasa Indonesia
        language: {
            search: 'Cari:',
            searchPlaceholder: 'Cari data siswa...',
            lengthMenu: 'Tampilkan _MENU_ data',
            info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
            infoEmpty: 'Tidak ada data siswa',
            infoFiltered: '(disaring dari _MAX_ seluruh data)',
            zeroRecords: 'Data yang dicari tidak ditemukan',
            emptyTable: 'Belum ada data siswa',
            processing: 'Memproses data...',
            paginate: {
                first: 'Pertama',
                last: 'Terakhir',
                next: 'Berikutnya',
                previous: 'Sebelumnya'
            }
        },

        // Jangan mengurutkan kolom tombol aksi
        columnDefs: [
            {
                targets: [10],
                orderable: false,
                searchable: false
            }
        ]
    });

});
</script>

</body>
</html>

<?php
mysqli_free_result($hasil);
?>
