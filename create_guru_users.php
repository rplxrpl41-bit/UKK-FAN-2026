<?php
require 'C:/xampp/htdocs/UKK-2026/config/koneksi.php';

// Password untuk semua guru: guru123
$password = password_hash('guru123', PASSWORD_DEFAULT);

$gurus = [
    ['nip' => '19850101001', 'nama' => 'Ahmad Fauzi - RPL', 'email' => 'ahmad.fauzi@sekolah.sch.id'],
    ['nip' => '19850202002', 'nama' => 'Dedi Kurniawan - RPL', 'email' => 'dedi.kurniawan@sekolah.sch.id'],
    ['nip' => '19850303003', 'nama' => 'Rizky Maulana - RPL', 'email' => 'rizky.maulana@sekolah.sch.id'],
    ['nip' => '19850404004', 'nama' => 'Fajar Nugraha - RPL', 'email' => 'fajar.nugraha@sekolah.sch.id'],
    ['nip' => '19850505005', 'nama' => 'Andi Setiawan - RPL', 'email' => 'andi.setiawan@sekolah.sch.id'],
    ['nip' => '19850606006', 'nama' => 'Siti Nurhaliza - RPL', 'email' => 'siti.nurhaliza@sekolah.sch.id'],
    ['nip' => '19850707007', 'nama' => 'Yudi Hermawan - RPL', 'email' => 'yudi.hermawan@sekolah.sch.id'],
    ['nip' => '19850808008', 'nama' => 'Dimas Saputra - RPL', 'email' => 'dimas.saputra@sekolah.sch.id'],
    ['nip' => '19850909009', 'nama' => 'Rina Marlina - RPL', 'email' => 'rina.marlina@sekolah.sch.id'],
    ['nip' => '19851010010', 'nama' => 'Agus Firmansyah - RPL', 'email' => 'agus.firmansyah@sekolah.sch.id'],
];

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
        // Insert user
        mysqli_stmt_bind_param($stmt, 'sss', $guru['nama'], $guru['email'], $password);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Gagal insert user {$guru['email']}: " . mysqli_stmt_error($stmt));
        }
        $user_id = mysqli_insert_id($koneksi);

        // Update guru
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