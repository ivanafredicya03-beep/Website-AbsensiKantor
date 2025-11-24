<?php
// Session cookie hardening (must be called BEFORE session_start)
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
require_once '../config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    if (!$username) {
        $error = 'Username wajib diisi!';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        if ($admin) {
            // Demo: generate token (production: email/wa/otp)
            $token = substr(sha1($admin['username'] . time()), 0, 8);
            $_SESSION['reset_token'] = $token;
            $_SESSION['reset_user'] = $username;
            $success = "Token reset Anda: <b>{$token}</b><br>Gunakan token ini di halaman reset password.";
        } else {
            $error = 'Username tidak ditemukan!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password - Sistem Absensi QR Code</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { box-sizing: border-box; }
        .brand-gradient { background: linear-gradient(135deg, #222660 0%, #F36E2B 100%);}
        .brand-primary { color: #222660; }
        .brand-secondary { color: #F36E2B;}
        .logo-container {
            width: 160px; height: 160px; background: white; border-radius: 16px; padding: 13px;
            box-shadow: 0 15px 40px rgba(34, 38, 96, 0.2); margin: 0 auto 18px;
        }
        .login-card {
            backdrop-filter: blur(15px); background: rgba(255,255,255,0.97);
            border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 20px 45px rgba(0,0,0,0.10);
            animation: fadeIn .7s;
        }
        @keyframes fadeIn {
            0% {opacity:0;transform:scale(.98) translateY(20px);}
            100%{opacity:1;transform:scale(1) translateY(0);}
        }
        .input-field {
            transition: all 0.3s; border: 2px solid #e5e7eb;
        }
        .input-field:focus {
            border-color: #F36E2B; box-shadow: 0 0 0 4px rgba(243,110,43,0.09);
            transform: translateY(-2px);
        }
        .btn-reset {
            background: linear-gradient(135deg,#222660 0%,#F36E2B 100%);
            color:#fff;
            font-weight: bold;
            border-radius: 1rem;
            transition: box-shadow .25s, transform .25s;
        }
        .btn-reset:hover { box-shadow:0 8px 30px rgba(243,110,43,0.13); transform: scale(1.03);}
        .error-message { animation: shake 0.5s;}
        @keyframes shake { 0%,100%{transform:translateX(0);}25%{transform:translateX(-10px);}75%{transform:translateX(10px);} }
        .shortcut-link {
            color: #222660; background: #f6f7fc; border-radius: 12px; padding: 7px 22px; font-weight: bold;
            transition: background .2s, color .2s; margin: 0 7px;
        }
        .shortcut-link:hover { background: #F36E2B; color:#fff;}
    </style>
</head>
<body class="min-h-screen flex items-center justify-center brand-gradient relative">

    <div class="login-card rounded-3xl p-10 w-full max-w-md relative z-10">
        <!-- Header -->
        <div class="text-center mb-7">
            <div class="logo-container">
                <img src="../assets/images/logo.png.webp" alt="Logo" class="w-full h-full object-contain">
            </div>
            <h2 class="text-2xl font-bold brand-primary mb-1">Lupa Password</h2>
            <p class="text-brand-secondary mb-1">Sistem Absensi QR Code</p>
        </div>
        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="bg-red-100 border-2 border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center error-message">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span><?= htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        <!-- Success Message -->
        <?php if ($success): ?>
            <div class="bg-green-100 border-2 border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-info-circle mr-2"></i>
                <span><?= $success ?></span>
            </div>
            <div class="text-center mb-2">
                <a href="reset_password.php" class="shortcut-link"><i class="fa fa-unlock"></i> Reset Password Sekarang</a>
            </div>
        <?php else: ?>
        <form method="POST" class="space-y-6" autocomplete="off">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-3">
                    <i class="fas fa-user brand-secondary mr-2"></i>Username Akun Admin
                </label>
                <input type="text" name="username" required autocomplete="username"
                       class="input-field w-full px-4 py-4 rounded-xl font-medium text-gray-700"
                       placeholder="Masukkan username Anda">
            </div>
            <button type="submit" class="btn-reset w-full py-4 text-lg mt-1">
                <i class="fa fa-key mr-2"></i>Kirim Token Reset
            </button>
        </form>
        <?php endif; ?>

        <div class="my-7 gap-3 flex justify-center flex-wrap">
            <a href="login.php" class="shortcut-link"><i class="fa fa-arrow-left mr-1"></i>Kembali ke Login</a>
        </div>

        <div class="text-center mt-4">
            <div class="flex items-center justify-center space-x-2 text-sm text-gray-500 mb-2">
                <i class="fas fa-shield-alt brand-secondary"></i>
                <span>Proteksi Sistem Aman</span>
            </div>
            <p class="text-xs text-gray-400">© 2025 Creed Creatives. All rights reserved.</p>
        </div>
    </div>
</body>
</html>