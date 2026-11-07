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
    $_SESSION['pesan'] = "ID tahun ajaran tidak valid!";
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_tahun_ajaran WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    $_SESSION['pesan'] = "Data tahun ajaran tidak ditemukan!";
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
    $status_aktif = (string) ($_POST['status_aktif'] ?? '1');

    if ($nama === '' || $tanggal_mulai === '' || $tanggal_selesai === '') {
        $error = "Semua kolom wajib diisi.";
    } elseif ($tanggal_mulai > $tanggal_selesai) {
        $error = "Tanggal mulai tidak boleh melewati tanggal selesai.";
    } elseif (!in_array($status_aktif, ['0', '1'], true)) {
        $error = "Status yang dipilih tidak valid.";
    } else {
        $status = (int) $status_aktif;

        $stmt = mysqli_prepare(
            $koneksi,
            "UPDATE t_tahun_ajaran SET
                nama = ?,
                tanggal_mulai = ?,
                tanggal_selesai = ?,
                status_aktif = ?,
                updated_at = NOW()
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssii",
            $nama,
            $tanggal_mulai,
            $tanggal_selesai,
            $status,
            $id
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            $_SESSION['pesan'] = "Data tahun ajaran berhasil diperbarui!";
            header("Location: kelola_tahun_ajaran.php?pesan=edit_berhasil");
            exit;
        }

        $error = "Gagal memperbarui data tahun ajaran: " . mysqli_stmt_error($stmt);
        mysqli_stmt_close($stmt);
    }

    $data['nama'] = $nama;
    $data['tanggal_mulai'] = $tanggal_mulai;
    $data['tanggal_selesai'] = $tanggal_selesai;
    $data['status_aktif'] = $status_aktif;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Tahun Ajaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style><?php include 'includes/crud_style.php'; ?></style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php $menu_aktif = 'Tahun Ajaran'; include 'includes/crud_sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 main-content">
            <h1 class="page-title">Edit Tahun Ajaran</h1>
            <p class="page-description">Perbarui data tahun ajaran sekolah.</p>

            <?php if ($error !== ''): ?>
                <div class="alert alert-danger"><?= e($error) ?></div>
            <?php endif; ?>

            <div class="content-card">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label" for="nama">Tahun Ajaran</label>
                        <input type="text" name="nama" id="nama" class="form-control"
                               value="<?= e($data['nama']) ?>" placeholder="Contoh: 2026/2027" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tanggal_mulai">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control"
                                   value="<?= e($data['tanggal_mulai']) ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tanggal_selesai">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control"
                                   value="<?= e($data['tanggal_selesai']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="status_aktif">Status</label>
                        <select name="status_aktif" id="status_aktif" class="form-select" required>
                            <option value="1" <?= (string) $data['status_aktif'] === '1' ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= (string) $data['status_aktif'] === '0' ? 'selected' : '' ?>>Tidak Aktif</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="kelola_tahun_ajaran.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </main>
    </div>
</div>
</body>
</html>
