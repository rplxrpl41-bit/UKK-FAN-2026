<?php
session_start();
require 'C:/xampp/htdocs/UKK-2026/config/koneksi.php';

$email = 'ahmad.fauzi@sekolah.sch.id';
$password = 'guru123';

$sql = "SELECT * FROM t_users WHERE email=?";
$stmt = mysqli_prepare($koneksi, $sql);
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if ($user && password_verify($password, $user['password'])) {
    echo "LOGIN BERHASIL\n";
    print_r($user);
} else {
    echo "LOGIN GAGAL\n";
}