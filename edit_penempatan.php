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
    $_SESSION['pesan'] = "ID penempatan tidak valid!";
    header("Location: kelola_penempatan_siswa.php");
    exit;
}

$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_kelas_siswa WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    $_SESSION['pesan'] = "Data penempatan tidak ditemukan!";
    header("Location: kelola_penempatan_siswa.php");
    exit;
}

$querySiswa = mysqli_query($koneksi, "SELECT id, nis, nama FROM t_siswa ORDER BY nama ASC");
$queryKelas = mysqli_query($koneksi, "SELECT id, nama, tingkat, jurusan FROM t_kelas WHERE tingkat = 'XI' AND jurusan = 'RPL' AND nama IN ('XI RPL1', 'XI RPL2') ORDER BY nama ASC");
$queryTahun = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran ORDER BY id DESC");

if (!$querySiswa || !$queryKelas || !$queryTahun) {
    die("Gagal mengambil data pilihan: " . mysqli_error($koneksi));
}

$tanggalSelesai = $data['tanggal_selesai'] ?? '';
if ($tanggalSelesai === '0000-00-00' || $tanggalSelesai === '9999-12-31') {
    $tanggalSelesai = '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Penempatan Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style><?php include 'includes/crud_style.php'; ?></style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php $menu_aktif = 'Penempatan Siswa'; include 'includes/crud_sidebar.php'; ?>

        <main class="col-md-9 col-lg-10 main-content">
            <h1 class="page-title">Edit Penempatan Siswa</h1>
            <p class="page-description">Perbarui kelas dan tahun ajaran siswa.</p>

            <?php if (!empty($_SESSION['pesan'])): ?>
                <div class="alert alert-danger"><?= e($_SESSION['pesan']) ?></div>
                <?php unset($_SESSION['pesan']); ?>
            <?php endif; ?>

            <div class="content-card">
                <form action="proses_edit_kelas.php" method="POST">
                    <input type="hidden" name="id" value="<?= (int) $data['id'] ?>">
                    <input type="hidden" name="redirect" value="kelola_penempatan_siswa.php">

                    <div class="mb-3">
                        <label class="form-label" for="siswa_id">Nama Siswa</label>
                        <select name="siswa_id" id="siswa_id" class="form-select" required>
                            <option value="">-- Pilih Siswa --</option>
                            <?php while ($siswa = mysqli_fetch_assoc($querySiswa)): ?>
                                <option value="<?= (int) $siswa['id'] ?>"
                                    <?= (int) $data['siswa_id'] === (int) $siswa['id'] ? 'selected' : '' ?>>
                                    <?= e(trim(($siswa['nis'] ?? '') . ' - ' . ($siswa['nama'] ?? ''), ' -')) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="kelas_id">Kelas</label>
                        <select name="kelas_id" id="kelas_id" class="form-select" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php while ($kelas = mysqli_fetch_assoc($queryKelas)): ?>
                                <option value="<?= (int) $kelas['id'] ?>"
                                    <?= (int) $data['kelas_id'] === (int) $kelas['id'] ? 'selected' : '' ?>>
                                    <?= e(trim(($kelas['nama'] ?? '') . ' ' . ($kelas['tingkat'] ?? '') . ' ' . ($kelas['jurusan'] ?? ''))) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="tahun_ajaran_id">Tahun Ajaran</label>
                        <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select" required>
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            <?php while ($tahun = mysqli_fetch_assoc($queryTahun)): ?>
                                <option value="<?= (int) $tahun['id'] ?>"
                                    <?= (int) $data['tahun_ajaran_id'] === (int) $tahun['id'] ? 'selected' : '' ?>>
                                    <?= e($tahun['nama']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tanggal_mulai">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                                   class="form-control" value="<?= e($data['tanggal_mulai']) ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tanggal_selesai">Tanggal Selesai</label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai"
                                   class="form-control" value="<?= e($tanggalSelesai) ?>">
                            <small class="text-muted">Kosongkan jika masih berlangsung.</small>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="status_aktif">Status</label>
                        <select name="status_aktif" id="status_aktif" class="form-select" required>
                            <option value="1" <?= (int) $data['status_aktif'] === 1 ? 'selected' : '' ?>>Aktif</option>
                            <option value="0" <?= (int) $data['status_aktif'] === 0 ? 'selected' : '' ?>>Tidak Aktif</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="kelola_penempatan_siswa.php" class="btn btn-secondary">Batal</a>
                </form>
            </div>
        </main>
    </div>
</div>
</body>
</html>
