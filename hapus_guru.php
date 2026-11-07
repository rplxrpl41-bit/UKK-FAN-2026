<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Pastikan hanya admin yang bisa menghapus guru
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak!");
}

// Ambil ID guru dari URL
$id = $_GET['id'] ?? null;

// Validasi ID
if ($id === null || !is_numeric($id) || (int) $id <= 0) {
    $_SESSION['pesan'] = "ID guru tidak ditemukan atau tidak valid!";
    header("Location: kelola_guru.php");
    exit;
}

$id = (int) $id;

// Hapus data guru. Relasi user_id memakai ON DELETE CASCADE,
// sehingga akun pengguna terkait ikut terhapus otomatis.
$sqlHapus = "DELETE FROM t_guru WHERE id = ?";
$stmtHapus = mysqli_prepare($koneksi, $sqlHapus);

if (!$stmtHapus) {
    die("Query gagal disiapkan: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param($stmtHapus, "i", $id);

if (!mysqli_stmt_execute($stmtHapus)) {
    $error = mysqli_stmt_error($stmtHapus);
    mysqli_stmt_close($stmtHapus);
    die("Gagal menghapus data guru: " . htmlspecialchars($error));
}

if (mysqli_stmt_affected_rows($stmtHapus) > 0) {
    mysqli_stmt_close($stmtHapus);
    header("Location: kelola_guru.php?pesan=hapus_berhasil");
    exit;
}

mysqli_stmt_close($stmtHapus);
$_SESSION['pesan'] = "Data guru dengan ID $id tidak ditemukan.";
header("Location: kelola_guru.php");
exit;
?>
