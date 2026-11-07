<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_siswa.php");
    exit;
}

$nis = trim($_POST['nis'] ?? '');
$nisn = trim($_POST['nisn'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
$tanggal_lahir = trim($_POST['tanggal_lahir'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$status = trim($_POST['status'] ?? 'Aktif');

if (
    $nis === '' ||
    $nama === '' ||
    $jenis_kelamin === '' ||
    $tanggal_lahir === '' ||
    $alamat === ''
) {
    echo "<script>
        alert('Data wajib harus diisi!');
        window.history.back();
    </script>";
    exit;
}

if (!in_array($jenis_kelamin, ['Laki-laki', 'Perempuan'], true)) {
    echo "<script>
        alert('Jenis kelamin tidak valid!');
        window.history.back();
    </script>";
    exit;
}

if (!in_array($status, ['Aktif', 'Tidak Aktif'], true)) {
    $status = 'Aktif';
}

$stmt = mysqli_prepare(
    $koneksi,
    "INSERT INTO t_siswa
    (nis, nisn, nama, jenis_kelamin, tanggal_lahir, alamat, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)"
);

if (!$stmt) {
    die("Gagal menyiapkan query: " . mysqli_error($koneksi));
}

mysqli_stmt_bind_param(
    $stmt,
    "sssssss",
    $nis,
    $nisn,
    $nama,
    $jenis_kelamin,
    $tanggal_lahir,
    $alamat,
    $status
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    echo "<script>
        alert('Data siswa berhasil ditambahkan!');
        window.location.href = 'kelola_siswa.php';
    </script>";
    exit;
}

if (mysqli_stmt_errno($stmt) == 1062) {
    echo "<script>
        alert('NIS atau NISN sudah terdaftar!');
        window.history.back();
    </script>";
} else {
    echo "Gagal menambahkan siswa: " .
        htmlspecialchars(mysqli_stmt_error($stmt));
}

mysqli_stmt_close($stmt);
?>