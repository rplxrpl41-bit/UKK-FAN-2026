<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak!");
}

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['pesan'] = "ID tahun ajaran tidak valid!";
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    // Lepaskan referensi dari catatan pelanggaran agar data riwayat tetap ada.
    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE t_pelanggaran_siswa SET tahun_ajaran_id = NULL WHERE tahun_ajaran_id = ?"
    );

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    $stmt = mysqli_prepare($koneksi, "DELETE FROM t_tahun_ajaran WHERE id = ?");

    if (!$stmt) {
        throw new Exception(mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    $terhapus = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    mysqli_commit($koneksi);

    $_SESSION['pesan'] = $terhapus > 0
        ? "Data tahun ajaran berhasil dihapus!"
        : "Data tahun ajaran tidak ditemukan.";

    header("Location: kelola_tahun_ajaran.php?pesan=hapus_berhasil");
    exit;
} catch (Throwable $ex) {
    mysqli_rollback($koneksi);
    $_SESSION['pesan'] = "Gagal menghapus tahun ajaran: " . $ex->getMessage();
    header("Location: kelola_tahun_ajaran.php");
    exit;
}
?>
