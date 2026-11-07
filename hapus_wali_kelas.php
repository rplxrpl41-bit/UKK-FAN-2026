<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Penghapusan wali kelas hanya dapat dilakukan oleh admin.
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Akses ditolak! Halaman ini hanya untuk admin.');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['pesan'] = 'ID wali kelas tidak valid.';
    header('Location: kelola_wali_kelas.php');
    exit;
}

// Pastikan data benar-benar tersedia sebelum dihapus.
$cek = mysqli_prepare($koneksi, 'SELECT id FROM t_wali_kelas WHERE id = ? LIMIT 1');
if (!$cek) {
    $_SESSION['pesan'] = 'Gagal memeriksa data wali kelas: ' . mysqli_error($koneksi);
    header('Location: kelola_wali_kelas.php');
    exit;
}
mysqli_stmt_bind_param($cek, 'i', $id);
mysqli_stmt_execute($cek);
$hasil_cek = mysqli_stmt_get_result($cek);
$ada = $hasil_cek && mysqli_num_rows($hasil_cek) > 0;
mysqli_stmt_close($cek);

if (!$ada) {
    $_SESSION['pesan'] = 'Data wali kelas tidak ditemukan atau sudah dihapus.';
    header('Location: kelola_wali_kelas.php');
    exit;
}

$hapus = mysqli_prepare($koneksi, 'DELETE FROM t_wali_kelas WHERE id = ?');
if (!$hapus) {
    $_SESSION['pesan'] = 'Gagal menyiapkan proses hapus: ' . mysqli_error($koneksi);
    header('Location: kelola_wali_kelas.php');
    exit;
}

mysqli_stmt_bind_param($hapus, 'i', $id);
if (mysqli_stmt_execute($hapus)) {
    $_SESSION['pesan'] = 'Data wali kelas berhasil dihapus.';
} else {
    $_SESSION['pesan'] = 'Gagal menghapus data wali kelas: ' . mysqli_stmt_error($hapus);
}
mysqli_stmt_close($hapus);

header('Location: kelola_wali_kelas.php');
exit;
?>
