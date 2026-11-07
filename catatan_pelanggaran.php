<?php

session_start();

include 'includes/cek_session.php';
include 'config/koneksi.php';

// Ambil data pelanggaran + kategori
$sql = "SELECT 
            t_pelanggaran.*,
            t_pelanggaran_kategori.nama AS nama_kategori
        FROM t_pelanggaran
        LEFT JOIN t_pelanggaran_kategori
        ON t_pelanggaran.pelanggaran_kategori_id = t_pelanggaran_kategori.id
        ORDER BY t_pelanggaran.id DESC";

$query = mysqli_query($koneksi, $sql);


// Ambil data kategori
$sql_kategori = "SELECT * FROM t_pelanggaran_kategori
                 ORDER BY nama ASC";

$query_kategori = mysqli_query($koneksi, $sql_kategori);

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Pelanggaran</title>
</head>

<body>

    <h1>Kelola Pelanggaran</h1>

    <?php if (isset($_SESSION['pesan'])) { ?>

        <p>
            <?php
            echo $_SESSION['pesan'];
            unset($_SESSION['pesan']);
            ?>
        </p>

    <?php } ?>


    <hr>


    <h2>Tambah Pelanggaran</h2>

    <form action="proses_pelanggaran.php" method="POST">

        <input type="hidden" name="aksi" value="tambah">

        <table>

            <tr>

                <td>Kategori</td>

                <td>:</td>

                <td>

                    <select name="pelanggaran_kategori_id" required>

                        <option value="">
                            -- Pilih Kategori --
                        </option>

                        <?php while ($kategori = mysqli_fetch_assoc($query_kategori)) { ?>

                            <option value="<?php echo $kategori['id']; ?>">

                                <?php
                                echo htmlspecialchars($kategori['nama']);
                                ?>

                            </option>

                        <?php } ?>

                    </select>

                </td>

            </tr>


            <tr>

                <td>Kode</td>

                <td>:</td>

                <td>

                    <input
                        type="text"
                        name="kode"
                        placeholder="PG-001"
                        required
                    >

                </td>

            </tr>


            <tr>

                <td>Nama Pelanggaran</td>

                <td>:</td>

                <td>

                    <input
                        type="text"
                        name="nama"
                        required
                    >

                </td>

            </tr>


            <tr>

                <td>Poin</td>

                <td>:</td>

                <td>

                    <input
                        type="number"
                        name="poin"
                        min="0"
                        required
                    >

                </td>

            </tr>


            <tr>

                <td>Deskripsi</td>

                <td>:</td>

                <td>

                    <textarea
                        name="deskripsi"
                        rows="4"
                        required
                    ></textarea>

                </td>

            </tr>


            <tr>

                <td>Status Aktif</td>

                <td>:</td>

                <td>

                    <select name="status_aktif" required>

                        <option value="1">
                            Aktif
                        </option>

                        <option value="0">
                            Tidak Aktif
                        </option>

                    </select>

                </td>

            </tr>


            <tr>

                <td colspan="3">

                    <button type="submit">
                        Tambah Pelanggaran
                    </button>

                </td>

            </tr>

        </table>

    </form>


    <hr>
    <a href="dashboard.php">Kembali ke Dashboard</a>


    <h2>Data Pelanggaran</h2>


    <table border="1" cellpadding="8" cellspacing="0">

        <thead>

            <tr>

                <th>No</th>
                <th>Kategori</th>
                <th>Kode</th>
                <th>Nama Pelanggaran</th>
                <th>Poin</th>
                <th>Deskripsi</th>
                <th>Status</th>
                <th>Aksi</th>

            </tr>

        </thead>


        <tbody>

        <?php

        $no = 1;

        if (mysqli_num_rows($query) > 0) {

            while ($pelanggaran = mysqli_fetch_assoc($query)) {

        ?>

            <tr>

                <td>
                    <?php echo $no++; ?>
                </td>


                <td>

                    <?php
                    echo htmlspecialchars(
                        $pelanggaran['nama_kategori'] ?? '-'
                    );
                    ?>

                </td>


                <td>

                    <?php
                    echo htmlspecialchars($pelanggaran['kode']);
                    ?>

                </td>


                <td>

                    <?php
                    echo htmlspecialchars($pelanggaran['nama']);
                    ?>

                </td>


                <td>

                    <?php
                    echo htmlspecialchars($pelanggaran['poin']);
                    ?>

                </td>


                <td>

                    <?php
                    echo htmlspecialchars($pelanggaran['deskripsi']);
                    ?>

                </td>


                <td>

                    <?php if ($pelanggaran['status_aktif'] == 1) { ?>

                        Aktif

                    <?php } else { ?>

                        Tidak Aktif

                    <?php } ?>

                </td>


                <td>

                    <a href="edit_pelanggaran.php?id=<?php echo $pelanggaran['id']; ?>">
                        Edit
                    </a>

                    |

                    <a
                        href="proses_pelanggaran.php?aksi=hapus&id=<?php echo $pelanggaran['id']; ?>"
                        onclick="return confirm('Yakin ingin menghapus pelanggaran ini?');"
                    >
                        Hapus
                    </a>

                </td>

            </tr>

        <?php

            }

        } else {

        ?>

            <tr>

                <td colspan="8">
                    Belum ada data pelanggaran.
                </td>

            </tr>

        <?php } ?>

        </tbody>

    </table>

</body>

</html>