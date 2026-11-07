<?php
require 'C:/xampp/htdocs/UKK-2026/config/koneksi.php';
$r = mysqli_query($koneksi, "SELECT id, nip, nama, user_id FROM t_guru ORDER BY id");
while ($row = mysqli_fetch_assoc($r)) {
    echo implode(' | ', $row) . "\n";
}