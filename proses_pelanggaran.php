<?php
session_start();
include 'includes/cek_session.php';
include 'config/koneksi.php';

$aksi = $_GET['aksi'] ?? ($_POST['aksi'] ?? '');

function e($nilai)
{
    return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

if ($aksi === 'hapus') {
    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        $_SESSION['pesan'] = "ID pelanggaran tidak valid!";
        header("Location: catatan_pelanggaran.php");
        exit;
    }

    $stmt = mysqli_prepare($koneksi, "DELETE FROM t_pelanggaran WHERE id = ?");

    if (!$stmt) {
        die("Gagal menyiapkan query: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (!mysqli_stmt_execute($stmt)) {
        $error = mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['pesan'] = "Gagal menghapus pelanggaran: " . $error;
        header("Location: catatan_pelanggaran.php");
        exit;
    }

    $terhapus = mysqli_stmt_affected_rows($stmt);
    mysqli_stmt_close($stmt);

    $_SESSION['pesan'] = $terhapus > 0
        ? "Data pelanggaran berhasil dihapus!"
        : "Data pelanggaran tidak ditemukan.";

    header("Location: catatan_pelanggaran.php?pesan=hapus_berhasil");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: catatan_pelanggaran.php");
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$kategori_id = (int) ($_POST['pelanggaran_kategori_id'] ?? 0);
$kode = trim($_POST['kode'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$poin = (int) ($_POST['poin'] ?? 0);
$deskripsi = trim($_POST['deskripsi'] ?? '');
$status_aktif = (string) ($_POST['status_aktif'] ?? '1');

if (
    $kategori_id <= 0 ||
    $kode === '' ||
    $nama === '' ||
    $poin < 0 ||
    !in_array($status_aktif, ['0', '1'], true)
) {
    $_SESSION['pesan'] = "Data pelanggaran tidak lengkap atau tidak valid!";
    header("Location: " . ($aksi === 'edit' ? "edit_pelanggaran.php?id=$id" : "catatan_pelanggaran.php"));
    exit;
}

$status = (int) $status_aktif;

if ($aksi === 'edit') {
    if ($id <= 0) {
        $_SESSION['pesan'] = "ID pelanggaran tidak valid!";
        header("Location: catatan_pelanggaran.php");
        exit;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE t_pelanggaran SET
            pelanggaran_kategori_id = ?,
            kode = ?,
            nama = ?,
            poin = ?,
            deskripsi = ?,
            status_aktif = ?,
            updated_at = NOW()
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "issiisi",
        $kategori_id,
        $kode,
        $nama,
        $poin,
        $deskripsi,
        $status,
        $id
    );
} else {
    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO t_pelanggaran
            (pelanggaran_kategori_id, kode, nama, poin, deskripsi, status_aktif)
         VALUES (?, ?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "issisi",
        $kategori_id,
        $kode,
        $nama,
        $poin,
        $deskripsi,
        $status
    );
}

if (!$stmt) {
    die("Gagal menyiapkan query: " . mysqli_error($koneksi));
}

if (!mysqli_stmt_execute($stmt)) {
    $error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    $_SESSION['pesan'] = "Gagal menyimpan pelanggaran: " . $error;
    header("Location: " . ($aksi === 'edit' ? "edit_pelanggaran.php?id=$id" : "catatan_pelanggaran.php"));
    exit;
}

mysqli_stmt_close($stmt);
$_SESSION['pesan'] = $aksi === 'edit'
    ? "Data pelanggaran berhasil diperbarui!"
    : "Data pelanggaran berhasil ditambahkan!";

header("Location: catatan_pelanggaran.php?pesan=berhasil");
exit;
?>
