<?php
// Session cookie hardening
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
$token_valid = isset($_SESSION['reset_token']) && isset($_SESSION['reset_user']);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $token_valid) {
    $token = $_POST['token'] ?? '';
    $newpass = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $username = $_SESSION['reset_user'];

    if (!$token || !$newpass || !$confirm) {
        $error = 'Semua field wajib diisi!';
    } elseif ($token != $_SESSION['reset_token']) {
        $error = 'Token salah!';
    } elseif ($newpass !== $confirm) {
        $error = 'Konfirmasi password tidak sama!';
    } elseif (strlen($newpass) < 6) {
        $error = 'Password minimal 6 karakter!';
    } else {
        $hash = password_hash($newpass, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE admin SET password=? WHERE username=?");
        if ($stmt->execute([$hash, $username])) {
            $success = 'Password berhasil di-reset! Silakan login.';
            unset($_SESSION['reset_token'], $_SESSION['reset_user']);
        } else {
            $error = 'Gagal mengupdate password!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - Sistem Absensi QR Code</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { box-sizing: border-box; }
        .brand-gradient { background: linear-gradient(135deg, #222660 0%, #F36E2B 100%); }
        .brand-primary { color: #222660; }
        .brand-secondary { color: #F36E2B; }
        .login-card { backdrop-filter: blur(12px); background: rgba(255,255,255,0.98); box-shadow: 0 25px 50px rgba(34,38,96,0.13); }
        .btn-brand { background: linear-gradient(135deg,#222660 0%,#F36E2B 100%); color: white; }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen brand-gradient">
    <div class="login-card rounded-3xl p-10 w-full max-w-md">
        <div class="text-center mb-8">
            <img src="../assets/images/logo.png.webp" alt="Logo" class="mx-auto w-24 h-24 mb-2">
            <h1 class="text-2xl font-bold brand-primary mb-2">Reset Password</h1>
            <p class="text-brand-secondary font-semibold">Sistem Absensi QR Code</p>
        </div>
        <?php if ($error): ?>
            <div class="bg-red-100 border-2 border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center error-message">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <span><?= htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-green-100 border-2 border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <span><?= htmlspecialchars($success); ?></span>
            </div>
            <div class="text-center mt-6">
                <a href="login.php" class="text-brand-secondary font-semibold">Login sekarang</a>
            </div>
        <?php elseif ($token_valid): ?>
        <form method="POST" class="space-y-6" autocomplete="off">
            <input type="text" name="token" class="w-full border-2 rounded-xl px-4 py-4" placeholder="Masukkan token" required>
            <input type="password" name="new_password" class="w-full border-2 rounded-xl px-4 py-4" placeholder="Password baru" required>
            <input type="password" name="confirm_password" class="w-full border-2 rounded-xl px-4 py-4" placeholder="Konfirmasi password baru" required>
            <button type="submit" class="btn-brand w-full py-4 rounded-xl text-lg font-bold">Reset Password</button>
        </form>
        <?php else: ?>
            <div class="bg-red-100 border-2 border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 text-center">
                Token sudah kadaluarsa atau tidak valid.
            </div>
        <?php endif; ?>
        <div class="text-center mt-6">
            <a href="login.php" class="text-brand-secondary font-semibold">Kembali ke Login</a>
        </div>
    </div>
</body>
</html>