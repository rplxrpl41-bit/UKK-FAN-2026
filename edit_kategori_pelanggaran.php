<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit("Akses ditolak!");
}

function e($nilai)
{
    return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['pesan'] = "ID kategori tidak valid!";
    header("Location: kelola_kategori_pelanggaran.php");
    exit;
}

$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_pelanggaran_kategori WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    $_SESSION['pesan'] = "Data kategori tidak ditemukan!";
    header("Location: kelola_kategori_pelanggaran.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $status_aktif = (string) ($_POST['status_aktif'] ?? '1');

    if ($nama === '') {
        $error = "Nama kategori wajib diisi.";
    } elseif (!in_array($status_aktif, ['0', '1'], true)) {
        $error = "Status kategori tidak valid.";
    } else {
        $cek = mysqli_prepare(
            $koneksi,
            "SELECT id FROM t_pelanggaran_kategori WHERE nama = ? AND id != ? LIMIT 1"
        );
        mysqli_stmt_bind_param($cek, "si", $nama, $id);
        mysqli_stmt_execute($cek);
        $hasil_cek = mysqli_stmt_get_result($cek);

        if (mysqli_num_rows($hasil_cek) > 0) {
            $error = "Nama kategori tersebut sudah digunakan.";
        }
        mysqli_stmt_close($cek);
    }

    if ($error === '') {
        $status = (int) $status_aktif;

        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE t_pelanggaran_kategori SET
                nama = ?,
                deskripsi = ?,
                status_aktif = ?,
                updated_at = NOW()
             WHERE id = ?"
        );

        mysqli_stmt_bind_param($stmt, "ssii", $nama, $deskripsi, $status, $id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            $_SESSION['pesan'] = "Data kategori berhasil diperbarui!";
            header("Location: kelola_kategori_pelanggaran.php?pesan=edit_berhasil");
            exit;
        }

        $error = "Gagal memperbarui kategori: " . mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
    }

    $data['nama'] = $nama;
    $data['deskripsi'] = $deskripsi;
    $data['status_aktif'] = $status_aktif;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Kategori Pelanggaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style><?php include 'includes/crud_style.php'; ?></style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php $menu_aktif = 'Kategori Pelanggaran'; include 'includes/crud_sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 main-content">
            <h1 class="page-title">Edit Kategori Pelanggaran</h1>
            <p class="page-description">Perbarui nama, deskripsi, dan status kategori.</p>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <div class="content-card">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label" for="nama">Nama Kategori</label>
                        <input type="text" name="nama" id="nama" class="form-control"
                               value="<?= e($data['nama']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="deskripsi">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" rows="4"
                                  class="form-control"><?= e($data['deskripsi']) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="status_aktif">Status</label>
                        <select name="status_aktif" id="status_aktif" class="form-select" required>
                            <option value="1" <?= (string) $data['status_aktif'] === '1' ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= (string) $data['status_aktif'] === '0' ? 'selected' : '' ?>>Tidak Aktif</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="kelola_kategori_pelanggaran.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </main>
    </div>
</div>
</body>
</html>
