
<?php
session_start();

include 'includes/cek_session.php';
include 'config/koneksi.php';

// =====================================
// CEK HAK AKSES ADMIN
// =====================================
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Akses ditolak!');
}

// =====================================
// AMBIL PESAN NOTIFIKASI
// =====================================
$pesan = $_SESSION['pesan'] ?? '';
unset($_SESSION['pesan']);

// =====================================
// CSRF TOKEN
// =====================================
if (empty($_SESSION['csrf_kelas'])) {
    $_SESSION['csrf_kelas'] = bin2hex(random_bytes(32));
}

// =====================================
// AMBIL DATA KELAS
// HANYA XII RPL1 DAN XII RPL2
// =====================================
$sql = "SELECT
            k.id,
            k.nama,
            k.tingkat,
            k.jurusan,
            k.status_aktif,
            COUNT(
                CASE
                    WHEN ks.status_aktif = 1 THEN 1
                END
            ) AS jumlah_siswa
        FROM t_kelas k
        LEFT JOIN t_kelas_siswa ks
            ON ks.kelas_id = k.id
        WHERE k.nama IN ('XII RPL1', 'XII RPL2')
        GROUP BY
            k.id,
            k.nama,
            k.tingkat,
            k.jurusan,
            k.status_aktif
        ORDER BY k.nama ASC";

$result = mysqli_query($koneksi, $sql);

if (!$result) {
    die('Gagal mengambil data kelas: ' . mysqli_error($koneksi));
}

$dataKelas = [];

while ($row = mysqli_fetch_assoc($result)) {
    $dataKelas[] = $row;
}

// =====================================
// CEK KELAS YANG BELUM TERSEDIA
// =====================================
$kelasTersedia = [];

foreach ($dataKelas as $row) {
    $kelasTersedia[] = $row['nama'];
}

$kelasTetap = ['XII RPL1', 'XII RPL2'];
$bolehTambah = count(array_diff($kelasTetap, $kelasTersedia)) > 0;

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

    <title>Kelola Kelas - My Website</title>

    <!-- BOOTSTRAP -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
    >

    <!-- FONT AWESOME -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
    >

    <!-- DATATABLES -->
    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css"
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

        /* KARTU TABEL */
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

        /* TABEL */
        .table thead th {
            background: #f1f5f9;
            color: #334155;
            font-size: 14px;
            vertical-align: middle;
            white-space: nowrap;
        }

        .table tbody td {
            font-size: 14px;
            vertical-align: middle;
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

        .btn-warning,
        .btn-danger {
            border-radius: 5px;
        }

        .btn-icon {
            width: 34px;
            height: 32px;
            padding: 5px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }

        /* BADGE STATUS */
        .badge-aktif {
            background: #d1fae5;
            color: #047857;
            padding: 6px 10px;
            border-radius: 20px;
        }

        .badge-nonaktif {
            background: #fee2e2;
            color: #b91c1c;
            padding: 6px 10px;
            border-radius: 20px;
        }

        /* INFORMASI */
        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3157d5;
            padding: 13px 16px;
            border-radius: 6px;
            color: #1e40af;
            margin-bottom: 20px;
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

            <div class="d-flex justify-content-between align-items-center flex-wrap">

                <div>
                    <h2 class="page-title">
                        Kelola Kelas
                    </h2>

                    <p class="page-description">
                        Kelola data kelas yang tersedia dalam sistem.
                    </p>
                </div>

                <?php if ($bolehTambah) { ?>

                    <a href="tambah_kelas.php" class="btn btn-primary mb-3">
                        <i class="fas fa-plus"></i>
                        Tambah Kelas
                    </a>

                <?php } ?>

            </div>


            <!-- PESAN NOTIFIKASI -->

            <?php if ($pesan !== '') { ?>

                <div class="alert alert-info alert-dismissible fade show">

                    <?= e($pesan) ?>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                    >
                        <span>&times;</span>
                    </button>

                </div>

            <?php } ?>


            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                Kelas yang ditampilkan hanya
                <strong>XII RPL1</strong> dan
                <strong>XII RPL2</strong>.
            </div>


            <!-- ================================= -->
            <!-- TABEL DATA KELAS -->
            <!-- ================================= -->

            <div class="form-card">

                <h5>
                    <i class="fas fa-school"></i>
                    Daftar Data Kelas
                </h5>

                <p class="subtitle">
                    Informasi kelas, jumlah siswa, status, dan pengelolaan data kelas.
                </p>


                <div class="table-responsive">

                    <table
                        id="tabelKelas"
                        class="table table-bordered table-hover"
                    >

                        <thead>

                            <tr>
                                <th width="50">No</th>
                                <th>Nama Kelas</th>
                                <th>Tingkat</th>
                                <th>Jurusan</th>
                                <th>Jumlah Siswa</th>
                                <th>Status</th>
                                <th width="110">Aksi</th>
                            </tr>

                        </thead>

                        <tbody>

                            <?php if (count($dataKelas) > 0) { ?>

                                <?php $no = 1; ?>

                                <?php foreach ($dataKelas as $row) { ?>

                                    <tr>

                                        <td><?= $no++ ?></td>

                                        <td>
                                            <strong><?= e($row['nama']) ?></strong>
                                        </td>

                                        <td><?= e($row['tingkat']) ?></td>

                                        <td><?= e($row['jurusan']) ?></td>

                                        <td>
                                            <?= (int) $row['jumlah_siswa'] ?> siswa
                                        </td>

                                        <td>

                                            <?php if ((int) $row['status_aktif'] === 1) { ?>

                                                <span class="badge-aktif">
                                                    Aktif
                                                </span>

                                            <?php } else { ?>

                                                <span class="badge-nonaktif">
                                                    Tidak Aktif
                                                </span>

                                            <?php } ?>

                                        </td>

                                        <td>

                                            <div class="d-flex">

                                                <!-- EDIT -->
                                                <a
                                                    href="edit_kelas.php?id=<?= (int) $row['id'] ?>"
                                                    class="btn btn-warning btn-sm btn-icon mr-1"
                                                    title="Edit Kelas"
                                                >
                                                    <i class="fas fa-edit"></i>
                                                </a>

                                                <!-- HAPUS -->
                                                <form
                                                    method="POST"
                                                    action="hapus_kelas.php"
                                                    onsubmit="return confirm('Yakin ingin menghapus kelas ini?');"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="id"
                                                        value="<?= (int) $row['id'] ?>"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="csrf_token"
                                                        value="<?= e($_SESSION['csrf_kelas']) ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-danger btn-sm btn-icon"
                                                        title="Hapus Kelas"
                                                    >
                                                        <i class="fas fa-trash"></i>
                                                    </button>

                                                </form>

                                            </div>

                                        </td>

                                    </tr>

                                <?php } ?>

                            <?php } ?>

                        </tbody>

                    </table>

                </div>

                <?php if (count($dataKelas) === 0) { ?>

                    <div class="text-center text-muted py-4">

                        <i class="fas fa-school fa-3x mb-3"></i>

                        <p>
                            Belum ada data kelas XII RPL1 atau XII RPL2.
                        </p>

                    </div>

                <?php } ?>

            </div>


            <!-- FOOTER -->

            <footer>
                &copy; <?= date('Y') ?> My Website. All rights reserved.
            </footer>

        </main>

    </div>

</div>


<!-- JAVASCRIPT -->

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<script>

$(document).ready(function () {

    $('#tabelKelas').DataTable({

        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: "Data tidak ditemukan",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total data)",

            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },

        pageLength: 10,
        ordering: true

    });

});

</script>

</body>
</html>
