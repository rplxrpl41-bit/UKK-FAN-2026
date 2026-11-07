<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Pastikan hanya admin yang boleh mengubah data guru
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak!");
}

// Pastikan data dikirim menggunakan POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_guru.php");
    exit;
}

// Ambil data dari form
$id = (int) ($_POST['id'] ?? 0);
$nip = trim($_POST['nip'] ?? '');
$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$user_id = (int) ($_POST['user_id'] ?? 0);
$status_aktif = (int) ($_POST['status_aktif'] ?? 0);
$nama_akun = trim($_POST['name'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi data
if (
    $id <= 0 ||
    $nip === '' ||
    $nama === '' ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    $user_id < 0 ||
    !in_array($status_aktif, [0, 1], true)
) {
    $_SESSION['pesan'] = "Data guru tidak valid!";
    header("Location: edit_guru.php?id=" . $id);
    exit;
}

// Periksa apakah data guru tersedia
$stmt = mysqli_prepare($koneksi, "SELECT id FROM t_guru WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    $_SESSION['pesan'] = "Data guru tidak ditemukan!";
    header("Location: kelola_guru.php");
    exit;
}
mysqli_stmt_close($stmt);

// Periksa NIP agar tidak duplikat
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_guru WHERE nip = ? AND id != ?"
);
mysqli_stmt_bind_param($stmt, "si", $nip, $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    mysqli_stmt_close($stmt);
    $_SESSION['pesan'] = "NIP sudah digunakan guru lain!";
    header("Location: edit_guru.php?id=" . $id);
    exit;
}
mysqli_stmt_close($stmt);

if ($user_id > 0) {
    // Pastikan akun yang dipilih memiliki role guru
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_users WHERE id = ? AND role = 'guru'"
    );
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) === 0) {
        mysqli_stmt_close($stmt);
        $_SESSION['pesan'] = "Akun guru tidak valid!";
        header("Location: edit_guru.php?id=" . $id);
        exit;
    }
    mysqli_stmt_close($stmt);

    // Periksa apakah akun sudah digunakan guru lain
    $stmt = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_guru WHERE user_id = ? AND id != ?"
    );
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        mysqli_stmt_close($stmt);
        $_SESSION['pesan'] = "Akun tersebut sudah digunakan guru lain!";
        header("Location: edit_guru.php?id=" . $id);
        exit;
    }
    mysqli_stmt_close($stmt);
}

mysqli_begin_transaction($koneksi);

try {
    // Update data guru
    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE t_guru SET
            nip = ?,
            nama = ?,
            email = ?,
            user_id = ?,
            status_aktif = ?,
            updated_at = NOW()
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sssiii",
        $nip,
        $nama,
        $email,
        $user_id,
        $status_aktif,
        $id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }
    mysqli_stmt_close($stmt);

    // Update akun pengguna terkait
    if ($user_id > 0 && $nama_akun !== '') {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmtUser = mysqli_prepare(
                $koneksi,
                "UPDATE t_users
                 SET name = ?, email = ?, password = ?, updated_at = NOW()
                 WHERE id = ? AND role = 'guru'"
            );
            mysqli_stmt_bind_param(
                $stmtUser,
                "sssi",
                $nama_akun,
                $email,
                $hash,
                $user_id
            );
        } else {
            $stmtUser = mysqli_prepare(
                $koneksi,
                "UPDATE t_users
                 SET name = ?, email = ?, updated_at = NOW()
                 WHERE id = ? AND role = 'guru'"
            );
            mysqli_stmt_bind_param(
                $stmtUser,
                "ssi",
                $nama_akun,
                $email,
                $user_id
            );
        }

        if (!mysqli_stmt_execute($stmtUser)) {
            throw new Exception(mysqli_stmt_error($stmtUser));
        }
        mysqli_stmt_close($stmtUser);
    }

    mysqli_commit($koneksi);
    $_SESSION['pesan'] = "Data guru berhasil diperbarui!";
    header("Location: kelola_guru.php?pesan=edit_berhasil");
    exit;
} catch (Throwable $ex) {
    mysqli_rollback($koneksi);
    $_SESSION['pesan'] = "Gagal memperbarui data guru: " . $ex->getMessage();
    header("Location: edit_guru.php?id=" . $id);
    exit;
}
?>
