<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$tabel = $_POST['tabel'] ?? '';
$redirect = $_POST['redirect'] ?? 'dashboard.php';

// Daftar tabel yang boleh dihapus
$daftar_tabel = [
    'siswa' => [
        'tabel' => 't_siswa',
        'halaman' => 'menu1.php'
    ],
    'kelas' => [
        'tabel' => 't_kelas',
        'halaman' => 'kelola_kelas.php'
    ],
    'tahun_ajaran' => [
        'tabel' => 't_tahun_ajaran',
        'halaman' => 'kelola_tahun_ajaran.php'
    ],
    'wali_kelas' => [
        'tabel' => 't_wali_kelas',
        'halaman' => 'kelola_wali_kelas.php'
    ],
    'penempatan_siswa' => [
        'tabel' => 't_kelas_siswa',
        'halaman' => 'kelola_penempatan_siswa.php'
    ],
    'pelanggaran' => [
        'tabel' => 't_pelanggaran',
        'halaman' => 'catatan_pelanggaran.php'
    ]
];

if ($id <= 0 || !isset($daftar_tabel[$tabel])) {
    die("Permintaan penghapusan tidak valid.");
}

$nama_tabel = $daftar_tabel[$tabel]['tabel'];
$halaman_tujuan = $daftar_tabel[$tabel]['halaman'];

// Abaikan alamat redirect dari pengguna agar tidak bisa
// mengarahkan proses ke alamat sembarangan.
$redirect = $halaman_tujuan;

$stmt = mysqli_prepare(
    $koneksi,
    "DELETE FROM `$nama_tabel` WHERE id = ?"
);

if (!$stmt) {
    die("Gagal menyiapkan query: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        mysqli_stmt_close($stmt);
        header("Location: $redirect?pesan=hapus_berhasil");
        exit;
    }

    mysqli_stmt_close($stmt);
    header("Location: $redirect?pesan=data_tidak_ditemukan");
    exit;
}

$error = mysqli_stmt_error($stmt);
mysqli_stmt_close($stmt);

if (mysqli_errno($koneksi) == 1451) {
    die("Data tidak bisa dihapus karena masih digunakan oleh data lain.");
}

die("Gagal menghapus data: " . htmlspecialchars($error));
?>