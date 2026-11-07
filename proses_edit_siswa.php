<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Hanya admin yang boleh mengubah data siswa
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak!");
}

if (isset($_POST['update'])) {
    $id = (int) ($_POST['id'] ?? 0);
    $nis = trim($_POST['nis'] ?? '');
    $nisn = trim($_POST['nisn'] ?? '');
    $nama = trim($_POST['nama'] ?? '');
    $jenis_kelamin = trim($_POST['jenis_kelamin'] ?? '');
    $tanggal_lahir = $_POST['tanggal_lahir'] ?? '';
    $alamat = trim($_POST['alamat'] ?? '');
    $status_aktif = (string) ($_POST['status_aktif'] ?? '1');

    if (
        $id <= 0 ||
        $nisn === '' ||
        $nama === '' ||
        !in_array($jenis_kelamin, ['L', 'P'], true) ||
        $tanggal_lahir === '' ||
        $alamat === '' ||
        !in_array($status_aktif, ['0', '1'], true)
    ) {
        $_SESSION['pesan'] = "Data siswa tidak valid!";
        header("Location: edit_siswa.php?id=" . $id);
        exit;
    }

    // Pastikan NISN tidak dipakai siswa lain
    $cek = mysqli_prepare(
        $koneksi,
        "SELECT id FROM t_siswa WHERE nisn = ? AND id != ? LIMIT 1"
    );
    mysqli_stmt_bind_param($cek, "si", $nisn, $id);
    mysqli_stmt_execute($cek);
    $hasil_cek = mysqli_stmt_get_result($cek);

    if (mysqli_num_rows($hasil_cek) > 0) {
        mysqli_stmt_close($cek);
        $_SESSION['pesan'] = "NISN sudah digunakan siswa lain!";
        header("Location: edit_siswa.php?id=" . $id);
        exit;
    }
    mysqli_stmt_close($cek);

    // Pastikan NIS tidak dipakai siswa lain (jika diisi)
    if ($nis !== '') {
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_siswa WHERE nis = ? AND id != ? LIMIT 1"
        );
        mysqli_stmt_bind_param($cek, "si", $nis, $id);
        mysqli_stmt_execute($cek);
        $hasil_cek = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil_cek) > 0) {
            mysqli_stmt_close($cek);
            $_SESSION['pesan'] = "NIS sudah digunakan siswa lain!";
            header("Location: edit_siswa.php?id=" . $id);
            exit;
        }
        mysqli_stmt_close($cek);
    }

    $status = (int) $status_aktif;

    $stmt = mysqli_prepare(
        $koneksi,
        "UPDATE t_siswa SET
            nis = ?,
            nisn = ?,
            nama = ?,
            jenis_kelamin = ?,
            tanggal_lahir = ?,
            alamat = ?,
            status_aktif = ?,
            updated_at = NOW()
         WHERE id = ?"
    );

    if (!$stmt) {
        die("Gagal menyiapkan query: " . mysqli_error($koneksi));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssii",
        $nis,
        $nisn,
        $nama,
        $jenis_kelamin,
        $tanggal_lahir,
        $alamat,
        $status,
        $id
    );

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        $_SESSION['pesan'] = "Data siswa berhasil diperbarui!";
        header("Location: kelola_siswa.php?pesan=edit_berhasil");
        exit;
    }

    $error = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    die("Gagal mengupdate data: " . htmlspecialchars($error));
}

header("Location: kelola_siswa.php");
exit;
?>
