<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak!");
}

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['pesan'] = "ID kategori tidak valid!";
    header("Location: kelola_kategori_pelanggaran.php");
    exit;
}

$stmt = mysqli_prepare($koneksi, "DELETE FROM t_pelanggaran_kategori WHERE id = ?");

if (!$stmt) {
    die("Gagal menyiapkan query: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmt, "i", $id);

if (!mysqli_stmt_execute($stmt)) {
    $error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    $_SESSION['pesan'] = "Gagal menghapus kategori: " . $error;
    header("Location: kelola_kategori_pelanggaran.php");
    exit;
}

$terhapus = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

$_SESSION['pesan'] = $terhapus > 0
    ? "Data kategori berhasil dihapus!"
    : "Data kategori tidak ditemukan.";

header("Location: kelola_kategori_pelanggaran.php?pesan=hapus_berhasil");
exit;
?>
