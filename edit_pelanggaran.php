<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

function e($nilai)
{
    return htmlspecialchars((string) ($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    $_SESSION['pesan'] = "ID pelanggaran tidak valid!";
    header("Location: catatan_pelanggaran.php");
    exit;
}

$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_pelanggaran WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    $_SESSION['pesan'] = "Data pelanggaran tidak ditemukan!";
    header("Location: catatan_pelanggaran.php");
    exit;
}

$queryKategori = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_pelanggaran_kategori ORDER BY nama ASC"
);

if (!$queryKategori) {
    die("Gagal mengambil data kategori: " . mysqli_error($koneksi));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Pelanggaran</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style><?php include 'includes/crud_style.php'; ?></style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php $menu_aktif = 'Kategori Pelanggaran'; include 'includes/crud_sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 main-content">
            <h1 class="page-title">Edit Pelanggaran</h1>
            <p class="page-description">Perbarui data jenis pelanggaran.</p>

            <?php if (!empty($_SESSION['pesan'])): ?>
                <div class="alert alert-danger"><?= e($_SESSION['pesan']) ?></div>
                <?php unset($_SESSION['pesan']); ?>
            <?php endif; ?>

            <div class="content-card">
                <form action="proses_pelanggaran.php" method="POST">
                    <input type="hidden" name="aksi" value="edit">
                    <input type="hidden" name="id" value="<?= (int) $data['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label" for="pelanggaran_kategori_id">Kategori</label>
                        <select name="pelanggaran_kategori_id" id="pelanggaran_kategori_id"
                                class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php while ($kategori = mysqli_fetch_assoc($queryKategori)): ?>
                                <option value="<?= (int) $kategori['id'] ?>"
                                    <?= (int) $data['pelanggaran_kategori_id'] === (int) $kategori['id'] ? 'selected' : '' ?>>
                                    <?= e($kategori['nama']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="kode">Kode</label>
                        <input type="text" name="kode" id="kode" class="form-control"
                               value="<?= e($data['kode']) ?>" placeholder="PG-001" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="nama">Nama Pelanggaran</label>
                        <input type="text" name="nama" id="nama" class="form-control"
                               value="<?= e($data['nama']) ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="poin">Poin</label>
                        <input type="number" name="poin" id="poin" class="form-control"
                               value="<?= (int) $data['poin'] ?>" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="deskripsi">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" rows="4"
                                  class="form-control" required><?= e($data['deskripsi']) ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="status_aktif">Status</label>
                        <select name="status_aktif" id="status_aktif" class="form-select" required>
                            <option value="1" <?= (string) $data['status_aktif'] === '1' ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= (string) $data['status_aktif'] === '0' ? 'selected' : '' ?>>Tidak Aktif</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="catatan_pelanggaran.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </main>
    </div>
</div>
</body>
</html>
