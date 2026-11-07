<?php

include 'config/koneksi.php';

$name = 'guru';
$email = 'guru@gmail.com';
$password = password_hash('guru123', PASSWORD_DEFAULT);
$role = 'guru';

$sql = "INSERT INTO t_users (name, email, password, role)
        VALUES (?, ?, ?, ?)";

$stmt = mysqli_prepare($koneksi, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ssss",
    $name,
    $email,
    $password,
    $role
);

if (mysqli_stmt_execute($stmt)) {
    echo "User guru berhasil dibuat.";
    echo "<br>Email: guru@gmail.com";
    echo "<br>Password: guru123";
} else {
    echo "Gagal membuat user: " . mysqli_error($koneksi);
}

?>