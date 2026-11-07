<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

$tahun_ajaran_id = (int) ($_GET['tahun_ajaran_id'] ?? 0);
$kelas_id = (int) ($_GET['kelas_id'] ?? 0);

$query_tahun = mysqli_query(
    $koneksi,
    "SELECT id, nama FROM t_tahun_ajaran ORDER BY id DESC"
);

$query_kelas = mysqli_query(
    $koneksi,
    "SELECT id, nama, tingkat, jurusan
     FROM t_kelas
     ORDER BY tingkat, jurusan, nama"
);

$sql = "
    SELECT
        s.nis,
        s.nama AS nama_siswa,
        k.nama AS nama_kelas,
        k.tingkat,
        k.jurusan,
        ta.nama AS tahun_ajaran
    FROM t_kelas_siswa ks
    JOIN t_siswa s ON ks.siswa_id = s.id
    JOIN t_kelas k ON ks.kelas_id = k.id
    JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
    WHERE 1 = 1
";

if ($tahun_ajaran_id > 0) {
    $sql .= " AND ks.tahun_ajaran_id = $tahun_ajaran_id";
}

if ($kelas_id > 0) {
    $sql .= " AND ks.kelas_id = $kelas_id";
}

$sql .= " ORDER BY k.nama ASC, s.nama ASC";

$query_laporan = mysqli_query($koneksi, $sql);

if (!$query_laporan) {
    die("Gagal mengambil laporan: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penempatan Siswa</title>
</head>
<body>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

    <h2>Laporan Penempatan Siswa</h2>
    <p>Laporan data siswa berdasarkan kelas dan tahun ajaran.</p>

    <a href="dashboard.php">Kembali ke Dashboard</a>

    <br><br>

    <form method="GET" action="laporan.php">
        <label>Tahun Ajaran:</label>

        <select name="tahun_ajaran_id">
            <option value="0">Semua Tahun Ajaran</option>

            <?php while ($ta = mysqli_fetch_assoc($query_tahun)) { ?>
                <option
                    value="<?= (int) $ta['id'] ?>"
                    <?= $tahun_ajaran_id == $ta['id'] ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars($ta['nama']) ?>
                </option>
            <?php } ?>
        </select>

        <label>Kelas:</label>

        <select name="kelas_id">
            <option value="0">Semua Kelas</option>

            <?php while ($kelas = mysqli_fetch_assoc($query_kelas)) { ?>
                <option
                    value="<?= (int) $kelas['id'] ?>"
                    <?= $kelas_id == $kelas['id'] ? 'selected' : '' ?>
                >
                    <?= htmlspecialchars(
                        $kelas['nama'] . ' - ' .
                        $kelas['tingkat'] . ' - ' .
                        $kelas['jurusan']
                    ) ?>
                </option>
            <?php } ?>
        </select>

        <button type="submit">Tampilkan</button>
        <button type="button" onclick="window.print()">Cetak Laporan</button>
    </form>

    <br>

    <h3>Data Penempatan Siswa</h3>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>No</th>
                <th>NIS</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Tingkat</th>
                <th>Jurusan</th>
                <th>Tahun Ajaran</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $no = 1;

            if (mysqli_num_rows($query_laporan) > 0) {
                while ($row = mysqli_fetch_assoc($query_laporan)) {
            ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['nis']) ?></td>
                    <td><?= htmlspecialchars($row['nama_siswa']) ?></td>
                    <td><?= htmlspecialchars($row['nama_kelas']) ?></td>
                    <td><?= htmlspecialchars($row['tingkat']) ?></td>
                    <td><?= htmlspecialchars($row['jurusan']) ?></td>
                    <td><?= htmlspecialchars($row['tahun_ajaran']) ?></td>
                </tr>
            <?php
                }
            } else {
            ?>
                <tr>
                    <td colspan="7">Tidak ada data penempatan siswa.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

</body>
</html>