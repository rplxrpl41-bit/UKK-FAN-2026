<?php
require 'C:/xampp/htdocs/UKK-2026/config/koneksi.php';

// Password untuk semua guru: guru123
$password = password_hash('guru123', PASSWORD_DEFAULT);

$gurus = [];
$r = mysqli_query($koneksi, "SELECT nip, nama, email FROM t_guru WHERE user_id = 0");
while ($row = mysqli_fetch_assoc($r)) {
    $gurus[] = $row;
}

if (empty($gurus)) {
    echo "Semua guru sudah punya user_id\n";
    exit;
}

mysqli_begin_transaction($koneksi);

try {
    $stmt = mysqli_prepare($koneksi, "
        INSERT INTO t_users (name, email, password, role, created_at, updated_at)
        VALUES (?, ?, ?, 'guru', NOW(), NOW())
    ");

    $updateStmt = mysqli_prepare($koneksi, "
        UPDATE t_guru SET user_id = ? WHERE nip = ?
    ");

    foreach ($gurus as $guru) {
        mysqli_stmt_bind_param($stmt, 'sss', $guru['nama'], $guru['email'], $password);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal insert user {$guru['email']}: " . mysqli_stmt_error($stmt));
        }
        $user_id = mysqli_insert_id($koneksi);

        mysqli_stmt_bind_param($updateStmt, 'is', $user_id, $guru['nip']);
        if (!mysqli_stmt_execute($updateStmt)) {
            throw new Exception("Gagal update guru {$guru['nip']}: " . mysqli_stmt_error($updateStmt));
        }

        echo "OK: {$guru['nip']} -> user_id=$user_id\n";
    }

    mysqli_commit($koneksi);
    echo "\nSelesai! Password guru: guru123\n";
} catch (Throwable $ex) {
    mysqli_rollback($koneksi);
    echo "ERROR: " . $ex->getMessage() . "\n";
}