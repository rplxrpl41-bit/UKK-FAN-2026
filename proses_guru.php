
<?php

include 'includes/cek_session.php';
include 'config/koneksi.php';

// =====================================
// PROSES TAMBAH GURU
// =====================================

if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['aksi'] ?? '') == 'tambah') {

    $nip = trim($_POST['nip'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $user_id = (int) ($_POST['user_id'] ?? 0);
    $status_aktif = (int) ($_POST['status_aktif'] ?? 0);

    // Validasi input
    if ($nip == '' || $nama == '' || $email == '' || $user_id <= 0) {
        $_SESSION['pesan'] = "Semua data wajib diisi!";
        header("Location: kelola_guru.php");
        exit;
    }

    // Cek NIP duplikat
    $cek = mysqli_prepare($koneksi, "SELECT id FROM t_guru WHERE nip = ?");
    mysqli_stmt_bind_param($cek, "s", $nip);
    mysqli_stmt_execute($cek);
    $hasil = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($hasil) > 0) {
        $_SESSION['pesan'] = "NIP sudah terdaftar!";
        header("Location: kelola_guru.php");
        exit;
    }

    // Pastikan akun benar-benar memiliki role guru
    $cek_user = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_users WHERE id = ? AND role = 'guru'"
    );

    mysqli_stmt_bind_param($cek_user, "i", $user_id);
    mysqli_stmt_execute($cek_user);
    $hasil_user = mysqli_stmt_get_result($cek_user);

    if (mysqli_num_rows($hasil_user) == 0) {
        $_SESSION['pesan'] = "Akun guru tidak ditemukan!";
        header("Location: kelola_guru.php");
        exit;
    }

    // Cek apakah akun sudah terhubung dengan guru lain
    $cek_relasi = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_guru WHERE user_id = ?"
    );

    mysqli_stmt_bind_param($cek_relasi, "i", $user_id);
    mysqli_stmt_execute($cek_relasi);
    $hasil_relasi = mysqli_stmt_get_result($cek_relasi);

    if (mysqli_num_rows($hasil_relasi) > 0) {
        $_SESSION['pesan'] = "Akun guru tersebut sudah digunakan!";
        header("Location: kelola_guru.php");
        exit;
    }

    // Simpan data guru
    $sql = "INSERT INTO t_guru
            (nip, nama, email, status_aktif, user_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())";

    $stmt = mysqli_prepare($koneksi, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sssii",
        $nip,
        $nama,
        $email,
        $status_aktif,
        $user_id
    );

    if (mysqli_stmt_execute($stmt)) {

        $_SESSION['pesan'] = "Data guru berhasil ditambahkan!";

    } else {

        $_SESSION['pesan'] = "Gagal menambahkan guru: " . mysqli_stmt_error($stmt);

    }

    header("Location: kelola_guru.php");
    exit;
}


// =====================================
// PROSES HAPUS GURU
// =====================================

if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus') {

    $id = (int) ($_GET['id'] ?? 0);

    if ($id <= 0) {
        $_SESSION['pesan'] = "ID guru tidak valid!";
        header("Location: kelola_guru.php");
        exit;
    }

    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_guru WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $_SESSION['pesan'] = "Data guru berhasil dihapus!";
        } else {
            $_SESSION['pesan'] = "Data guru tidak ditemukan!";
        }

    } else {

        $_SESSION['pesan'] = "Gagal menghapus guru: " . mysqli_stmt_error($stmt);

    }

    header("Location: kelola_guru.php");
    exit;
}


// =====================================
// AKSI TIDAK DIKENALI
// =====================================

$_SESSION['pesan'] = "Aksi tidak dikenali!";
header("Location: kelola_guru.php");
exit;

?>

