<?php

include 'config/koneksi.php';

$name = 'administrator';
$email = 'admin@gmail.com';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$role = 'admin';

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
    echo "User admin berhasil dibuat.";
    echo "<br>Email: admin@gmail.com";
    echo "<br>Password: admin123";
} else {
    echo "Gagal membuat user: " . mysqli_error($koneksi);
}

?>