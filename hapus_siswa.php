<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Hanya admin yang boleh menghapus siswa
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak!");
}

// Ambil ID dari URL
$id = $_GET['id'] ?? null;

// Validasi ID
if ($id === null || !is_numeric($id) || (int) $id <= 0) {
    $_SESSION['pesan'] = "ID siswa tidak ditemukan atau tidak valid!";
    header("Location: kelola_siswa.php");
    exit;
}

$id = (int) $id;

// Proses hapus data
$sql = "DELETE FROM t_siswa WHERE id = ?";
$stmt = mysqli_prepare($koneksi, $sql);

if (!$stmt) {
    die("Query gagal disiapkan: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $id);

if (!mysqli_stmt_execute($stmt)) {
    die("Gagal menghapus data: " . mysqli_stmt_error($stmt));
}

if (mysqli_stmt_affected_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    header("Location: kelola_siswa.php?pesan=hapus_berhasil");
    exit;
}

mysqli_stmt_close($stmt);
$_SESSION['pesan'] = "Data siswa dengan ID $id tidak ditemukan.";
header("Location: kelola_siswa.php");
exit;
?>
