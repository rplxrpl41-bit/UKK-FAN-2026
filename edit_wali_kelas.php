<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Halaman ini hanya boleh dibuka oleh admin.
if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Akses ditolak! Halaman ini hanya untuk admin.');
}

function e($nilai)
{
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
}

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['pesan'] = 'ID wali kelas tidak valid.';
    header('Location: kelola_wali_kelas.php');
    exit;
}

// Ambil data wali kelas berdasarkan ID.
$stmt = mysqli_prepare($koneksi, "SELECT id, tahun_ajaran_id, kelas_id, guru_id, tanggal_mulai, tanggal_selesai, status_aktif FROM t_wali_kelas WHERE id = ? LIMIT 1");
if (!$stmt) {
    die('Gagal menyiapkan query data wali kelas: ' . e(mysqli_error($koneksi)));
}
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    $_SESSION['pesan'] = 'Data wali kelas tidak ditemukan.';
    header('Location: kelola_wali_kelas.php');
    exit;
}

$error = '';
$tahun_ajaran_id = (string)$data['tahun_ajaran_id'];
$kelas_id = (string)$data['kelas_id'];
$guru_id = (string)$data['guru_id'];
$tanggal_mulai = $data['tanggal_mulai'];
$tanggal_selesai = $data['tanggal_selesai'];
$status_aktif = (string)$data['status_aktif'];

// Nilai 0000-00-00 dan 9999-12-31 dianggap belum memiliki tanggal selesai.
if ($tanggal_selesai === '0000-00-00' || $tanggal_selesai === '9999-12-31') {
    $tanggal_selesai = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tahun_ajaran_id = (string)(int)($_POST['tahun_ajaran_id'] ?? 0);
    $kelas_id = (string)(int)($_POST['kelas_id'] ?? 0);
    $guru_id = (string)(int)($_POST['guru_id'] ?? 0);
    $tanggal_mulai = trim($_POST['tanggal_mulai'] ?? '');
    $tanggal_selesai = trim($_POST['tanggal_selesai'] ?? '');
    $status_aktif = (string)($_POST['status_aktif'] ?? '');

    if ((int)$tahun_ajaran_id <= 0 || (int)$kelas_id <= 0 || (int)$guru_id <= 0 || $tanggal_mulai === '') {
        $error = 'Tahun ajaran, kelas, guru, dan tanggal mulai wajib diisi.';
    } elseif (!in_array($status_aktif, ['0', '1'], true)) {
        $error = 'Status wali kelas tidak valid.';
    } elseif ($tanggal_selesai !== '' && $tanggal_mulai > $tanggal_selesai) {
        $error = 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.';
    } else {
        // Pastikan kombinasi kelas dan tahun ajaran tidak dipakai data lain.
        $cek = mysqli_prepare($koneksi, "SELECT id FROM t_wali_kelas WHERE tahun_ajaran_id = ? AND kelas_id = ? AND id <> ? LIMIT 1");
        if (!$cek) {
            $error = 'Gagal memeriksa duplikasi data wali kelas.';
        } else {
            $tahun_int = (int)$tahun_ajaran_id;
            $kelas_int = (int)$kelas_id;
            mysqli_stmt_bind_param($cek, 'iii', $tahun_int, $kelas_int, $id);
            mysqli_stmt_execute($cek);
            $cek_result = mysqli_stmt_get_result($cek);
            if ($cek_result && mysqli_num_rows($cek_result) > 0) {
                $error = 'Kelas tersebut sudah memiliki wali kelas pada tahun ajaran yang dipilih.';
            }
            mysqli_stmt_close($cek);
        }

        if ($error === '') {
            // Kolom tanggal_selesai pada database NOT NULL.
            $tanggal_selesai_db = ($tanggal_selesai === '') ? '9999-12-31' : $tanggal_selesai;
            $tahun_int = (int)$tahun_ajaran_id;
            $kelas_int = (int)$kelas_id;
            $guru_int = (int)$guru_id;
            $status_int = (int)$status_aktif;

            $update = mysqli_prepare($koneksi, "UPDATE t_wali_kelas SET tahun_ajaran_id = ?, kelas_id = ?, guru_id = ?, tanggal_mulai = ?, tanggal_selesai = ?, status_aktif = ?, updated_at = NOW() WHERE id = ?");
            if (!$update) {
                $error = 'Gagal menyiapkan proses perubahan: ' . mysqli_error($koneksi);
            } else {
                mysqli_stmt_bind_param($update, 'iiissii', $tahun_int, $kelas_int, $guru_int, $tanggal_mulai, $tanggal_selesai_db, $status_int, $id);
                if (mysqli_stmt_execute($update)) {
                    mysqli_stmt_close($update);
                    $_SESSION['pesan'] = 'Data wali kelas berhasil diperbarui.';
                    header('Location: kelola_wali_kelas.php');
                    exit;
                }
                $error = 'Gagal memperbarui data wali kelas: ' . mysqli_stmt_error($update);
                mysqli_stmt_close($update);
            }
        }
    }
}

$query_tahun = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran ORDER BY id DESC");
$query_kelas = mysqli_query($koneksi, "SELECT id, nama, tingkat, jurusan FROM t_kelas WHERE tingkat = 'XI' AND jurusan = 'RPL' AND nama IN ('XI RPL1', 'XI RPL2') ORDER BY nama ASC");
$query_guru = mysqli_query($koneksi, "SELECT id, nama, nip FROM t_guru ORDER BY nama ASC");
if (!$query_tahun || !$query_kelas || !$query_guru) {
    die('Gagal mengambil pilihan tahun ajaran, kelas, atau guru: ' . e(mysqli_error($koneksi)));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Wali Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body{margin:0;background:#f4f6f9;font-family:Arial,sans-serif;color:#1e293b}
        .sidebar{background:#172554;color:white;min-height:100vh;padding:22px 15px}
        .sidebar-title{font-size:20px;font-weight:bold;margin-bottom:30px;padding:0 10px;line-height:1.5}
        .sidebar .nav-link{color:#e2e8f0;padding:12px 14px;margin-bottom:6px;border-radius:6px;transition:.2s}
        .sidebar .nav-link:hover{background:#263b70;color:white}
        .sidebar .nav-link.active{background:#3157d5;color:white}
        .sidebar .nav-link.logout{color:#fca5a5}
        .sidebar hr{border-color:#64748b}
        .main-content{padding:28px}
        .page-title{color:#172554;font-size:28px;font-weight:bold;margin-bottom:6px}
        .page-description{color:#64748b;margin-bottom:25px}
        .content-card{background:white;border-radius:12px;padding:24px;box-shadow:0 3px 12px rgba(0,0,0,.06);max-width:900px}
        .section-title{color:#172554;font-size:19px;font-weight:bold;margin-bottom:5px}
        .form-label{font-weight:600;color:#172554}
        .btn-primary{background:#3157d5;border-color:#3157d5}
        .btn-primary:hover{background:#2546b8;border-color:#2546b8}
        @media(max-width:767px){.sidebar{min-height:auto}.main-content{padding:18px}.content-card{padding:16px}.page-title{font-size:24px}}
    </style>
</head>
<body>
<div class="container-fluid"><div class="row">
    <?php $menu_aktif = 'Wali Kelas'; include 'includes/crud_sidebar.php'; ?>
    <main class="col-md-9 col-lg-10 main-content">
        <h1 class="page-title">Edit Wali Kelas</h1>
        <p class="page-description">Ubah informasi guru, kelas, tahun ajaran, dan masa tugas wali kelas.</p>
        <div class="content-card">
            <h5 class="section-title mb-4">Form Edit Wali Kelas</h5>
            <?php if ($error !== ''): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <form method="POST" action="edit_wali_kelas.php?id=<?= $id ?>">
                <input type="hidden" name="id" value="<?= $id ?>">
                <div class="mb-3">
                    <label for="tahun_ajaran_id" class="form-label">Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" id="tahun_ajaran_id" class="form-select" required>
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        <?php while ($ta = mysqli_fetch_assoc($query_tahun)): ?>
                            <option value="<?= (int)$ta['id'] ?>" <?= $tahun_ajaran_id == $ta['id'] ? 'selected' : '' ?>><?= e($ta['nama']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="kelas_id" class="form-label">Kelas</label>
                    <select name="kelas_id" id="kelas_id" class="form-select" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php while ($kelas = mysqli_fetch_assoc($query_kelas)): ?>
                            <option value="<?= (int)$kelas['id'] ?>" <?= $kelas_id == $kelas['id'] ? 'selected' : '' ?>><?= e($kelas['nama']) ?> - <?= e($kelas['tingkat']) ?> <?= e($kelas['jurusan']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="guru_id" class="form-label">Guru / Wali Kelas</label>
                    <select name="guru_id" id="guru_id" class="form-select" required>
                        <option value="">-- Pilih Guru --</option>
                        <?php while ($guru = mysqli_fetch_assoc($query_guru)): ?>
                            <option value="<?= (int)$guru['id'] ?>" <?= $guru_id == $guru['id'] ? 'selected' : '' ?>><?= e($guru['nama']) ?><?= !empty($guru['nip']) ? ' - ' . e($guru['nip']) : '' ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_mulai" class="form-label">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control" value="<?= e($tanggal_mulai) ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="tanggal_selesai" class="form-label">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control" value="<?= e($tanggal_selesai) ?>">
                        <small class="text-muted">Kosongkan jika masa tugas masih berlangsung.</small>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="status_aktif" class="form-label">Status</label>
                    <select name="status_aktif" id="status_aktif" class="form-select" required>
                        <option value="1" <?= $status_aktif === '1' ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= $status_aktif === '0' ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="kelola_wali_kelas.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </main>
</div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
