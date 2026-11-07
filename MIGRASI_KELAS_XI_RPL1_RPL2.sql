-- MIGRASI DATA KELAS: hanya XI RPL1 dan XI RPL2
-- Backup database terlebih dahulu sebelum menjalankan SQL ini.
-- Script mempertahankan ID kelas RPL 1 dan RPL 2 agar relasi data tetap tersambung.

START TRANSACTION;

SET @id_rpl1 := (SELECT id FROM t_kelas WHERE nama IN ('RPL 1','XI RPL1') AND tingkat='XI' AND jurusan='RPL' ORDER BY id LIMIT 1);
SET @id_rpl2 := (SELECT id FROM t_kelas WHERE nama IN ('RPL 2','XI RPL2') AND tingkat='XI' AND jurusan='RPL' ORDER BY id LIMIT 1);

-- Hentikan jika data kelas RPL 1 atau RPL 2 tidak ditemukan.
-- Jika salah satu variabel NULL, jangan lanjutkan; periksa isi t_kelas terlebih dahulu.

UPDATE t_kelas_siswa
SET kelas_id = CASE WHEN MOD(COALESCE(kelas_id, 1), 2) = 0 THEN @id_rpl2 ELSE @id_rpl1 END
WHERE kelas_id IS NOT NULL AND @id_rpl1 IS NOT NULL AND @id_rpl2 IS NOT NULL;

UPDATE t_wali_kelas
SET kelas_id = CASE WHEN MOD(COALESCE(kelas_id, 1), 2) = 0 THEN @id_rpl2 ELSE @id_rpl1 END
WHERE kelas_id IS NOT NULL AND @id_rpl1 IS NOT NULL AND @id_rpl2 IS NOT NULL;

UPDATE t_pelanggaran_siswa
SET kelas_id = CASE WHEN MOD(COALESCE(kelas_id, 1), 2) = 0 THEN @id_rpl2 ELSE @id_rpl1 END,
    nama_kelas = CASE WHEN MOD(COALESCE(kelas_id, 1), 2) = 0 THEN 'XI RPL2' ELSE 'XI RPL1' END
WHERE kelas_id IS NOT NULL AND @id_rpl1 IS NOT NULL AND @id_rpl2 IS NOT NULL;

UPDATE t_kelas SET nama='XI RPL1', tingkat='XI', jurusan='RPL', status_aktif=1 WHERE id=@id_rpl1;
UPDATE t_kelas SET nama='XI RPL2', tingkat='XI', jurusan='RPL', status_aktif=1 WHERE id=@id_rpl2;

DELETE FROM t_kelas WHERE id NOT IN (@id_rpl1, @id_rpl2) AND @id_rpl1 IS NOT NULL AND @id_rpl2 IS NOT NULL;

COMMIT;

-- Pemeriksaan hasil: seharusnya hanya menampilkan 2 baris.
SELECT id, nama, tingkat, jurusan, status_aktif FROM t_kelas ORDER BY id;
