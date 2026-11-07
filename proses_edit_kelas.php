<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak!");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_penempatan_siswa.php");
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$siswa_id = (int) ($_POST['siswa_id'] ?? 0);
$kelas_id = (int) ($_POST['kelas_id'] ?? 0);
$tahun_ajaran_id = (int) ($_POST['tahun_ajaran_id'] ?? 0);
$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
$status_aktif = (string) ($_POST['status_aktif'] ?? '1');

$redirect = $_POST['redirect'] ?? 'kelola_penempatan_siswa.php';
$halaman_diizinkan = ['kelola_kelas.php', 'kelola_penempatan_siswa.php'];

if (!in_array($redirect, $halaman_diizinkan, true)) {
    $redirect = 'kelola_penempatan_siswa.php';
}

if (
    $id <= 0 ||
    $siswa_id <= 0 ||
    $kelas_id <= 0 ||
    $tahun_ajaran_id <= 0 ||
    $tanggal_mulai === '' ||
    !in_array($status_aktif, ['0', '1'], true)
) {
    $_SESSION['pesan'] = "Data penempatan tidak lengkap atau tidak valid!";
    header("Location: " . $redirect);
    exit;
}

if ($tanggal_selesai === '') {
    // Kolom tanggal_selesai NOT NULL. Gunakan tanggal sentinel
    // untuk penempatan yang masih berlangsung.
    $tanggal_selesai = '9999-12-31';
}

$status = (int) $status_aktif;

$stmt = mysqli_prepare(
    $koneksi,
    "UPDATE t_kelas_siswa SET
        siswa_id = ?,
        kelas_id = ?,
        tahun_ajaran_id = ?,
        tanggal_mulai = ?,
        tanggal_selesai = ?,
        status_aktif = ?,
        updated_at = NOW()
     WHERE id = ?"
);

if (!$stmt) {
    die("Gagal menyiapkan query: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param(
    $stmt,
    "iiissii",
    $siswa_id,
    $kelas_id,
    $tahun_ajaran_id,
    $tanggal_mulai,
    $tanggal_selesai,
    $status,
    $id
);

if (!mysqli_stmt_execute($stmt)) {
    $error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    $_SESSION['pesan'] = "Gagal memperbarui data: " . $error;
    header("Location: edit_penempatan.php?id=" . $id);
    exit;
}

mysqli_stmt_close($stmt);
$_SESSION['pesan'] = "Data penempatan berhasil diperbarui!";
header("Location: " . $redirect . "?pesan=edit_berhasil");
exit;
?>
