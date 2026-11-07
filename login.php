<?php
session_start();
include 'config/koneksi.php';

// Lamun geus login, arahkeun ka dashboard
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: dashboard.php");
    exit;
}

$error = '';

if (isset($_SESSION['pesan_error'])) {
    $error = $_SESSION['pesan_error'];
    unset($_SESSION['pesan_error']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Sistem Pelanggaran Siswa</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            min-height: 100vh;
            background: #f1f5fb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #172554;
        }

        .login-wrapper {
            width: 100%;
            max-width: 960px;
            min-height: 560px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #fff;
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(23, 37, 84, .13);
        }

        .login-info {
            background: linear-gradient(145deg, #172554, #2448a0);
            color: white;
            padding: 55px 42px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .login-info::before {
            content: "";
            position: absolute;
            width: 280px;
            height: 280px;
            border: 50px solid rgba(255,255,255,.06);
            border-radius: 50%;
            right: -110px;
            top: 80px;
        }

        .brand {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 17px;
            font-weight: 700;
        }

        .brand-icon {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            background: #facc15;
            color: #172554;
            font-size: 25px;
        }

        .welcome {
            position: relative;
            z-index: 1;
            margin: 45px 0;
        }

        .welcome .label {
            display: inline-block;
            color: #fde68a;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 18px;
        }

        .welcome h1 {
            font-size: 35px;
            line-height: 1.3;
            margin-bottom: 18px;
        }

        .welcome p {
            color: #dbeafe;
            font-size: 14px;
            line-height: 1.9;
        }

        .info-footer {
            position: relative;
            z-index: 1;
            color: #cbd5e1;
            font-size: 12px;
        }

        .login-form {
            padding: 55px 45px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-heading {
            margin-bottom: 32px;
        }

        .form-heading h2 {
            font-size: 28px;
            margin-bottom: 9px;
            color: #172554;
        }

        .form-heading p {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
            border-radius: 9px;
            padding: 12px 14px;
            font-size: 13px;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 21px;
        }

        .form-group label {
            display: block;
            margin-bottom: 9px;
            font-size: 13px;
            font-weight: 600;
            color: #334155;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap input {
            width: 100%;
            height: 49px;
            padding: 0 14px;
            border: 1px solid #dbe3ef;
            border-radius: 10px;
            outline: none;
            font-size: 14px;
            color: #172554;
            background: #fbfdff;
            transition: .2s;
        }

        .input-wrap input:focus {
            border-color: #3157d5;
            box-shadow: 0 0 0 3px rgba(49, 87, 213, .10);
            background: #fff;
        }

        .password-input {
            padding-right: 65px !important;
        }

        .show-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: none;
            color: #3157d5;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .login-button {
            width: 100%;
            height: 49px;
            border: none;
            border-radius: 10px;
            background: #3157d5;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: .2s;
            margin-top: 5px;
        }

        .login-button:hover {
            background: #2445b5;
            box-shadow: 0 7px 18px rgba(49, 87, 213, .2);
        }

        .secure-note {
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            margin-top: 24px;
            line-height: 1.7;
        }

        @media (max-width: 700px) {
            body {
                padding: 14px;
            }

            .login-wrapper {
                max-width: 430px;
                grid-template-columns: 1fr;
                min-height: auto;
                border-radius: 17px;
            }

            .login-info {
                padding: 25px;
            }

            .welcome {
                margin: 30px 0 10px;
            }

            .welcome h1 {
                font-size: 27px;
            }

            .welcome p {
                font-size: 13px;
            }

            .info-footer {
                display: none;
            }

            .login-form {
                padding: 32px 25px;
            }

            .form-heading h2 {
                font-size: 25px;
            }
        }
    </style>
</head>

<body>

<div class="login-wrapper">

    <section class="login-info">
        <div class="brand">
            <div class="brand-icon">&#9878;</div>
            <span>SIPES<br><small>Sistem Pelanggaran Siswa</small></span>
        </div>

        <div class="welcome">
            <span class="label">SISTEM INFORMASI SEKOLAH</span>
            <h1>Disiplin Hari Ini,<br>Masa Depan Lebih Baik.</h1>
            <p>
                Kelola data siswa, catatan pelanggaran, poin,
                serta laporan sekolah dalam satu sistem
                yang terintegrasi.
            </p>
        </div>

        <div class="info-footer">
            &copy; <?php echo date('Y'); ?>
            Sistem Informasi Pelanggaran Siswa
            <br>UKK RPL
        </div>
    </section>

    <section class="login-form">
        <div class="form-heading">
            <h2>Wilujeng Sumping!</h2>
            <p>Silakan login menggunakan email dan password akun Anda.</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert-error">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form action="proses_login.php" method="POST">
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <div class="input-wrap">
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="nama@sekolah.test"
                        autocomplete="username"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="password-input"
                        placeholder="Masukkan password"
                        autocomplete="current-password"
                        required
                    >
                    <button
                        type="button"
                        class="show-password"
                        id="togglePassword"
                        aria-label="Tampilkan password"
                    >Lihat</button>
                </div>
            </div>

            <button type="submit" name="login" value="login" class="login-button">
                Masuk ke Sistem &rarr;
            </button>
        </form>

        <div class="secure-note">
            Halaman ini khusus untuk admin dan guru yang memiliki akun terdaftar.
            <br>Pastikan akun Anda digunakan dengan aman.
        </div>
    </section>

</div>

<script>
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    togglePassword.addEventListener('click', function () {
        const sedangDisembunyikan = passwordInput.type === 'password';

        passwordInput.type = sedangDisembunyikan ? 'text' : 'password';
        togglePassword.textContent = sedangDisembunyikan ? 'Sembunyikan' : 'Lihat';
        togglePassword.setAttribute(
            'aria-label',
            sedangDisembunyikan ? 'Sembunyikan password' : 'Tampilkan password'
        );
    });
</script>

</body>
</html>
