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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $nama_admin = trim(filter_input(INPUT_POST, 'nama_admin', FILTER_SANITIZE_STRING));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Validasi dasar
    if (!$username || !$nama_admin || !$password || !$confirm) {
        $error = 'Semua field wajib diisi!';
    } elseif ($password !== $confirm) {
        $error = 'Konfirmasi password tidak sama!';
    } else {
        // Cek username sudah ada belum
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username sudah pernah didaftarkan!';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admin (username, password, nama_admin, role) VALUES (?, ?, ?, 'admin')");
            if ($stmt->execute([$username, $hash, $nama_admin])) {
                $success = 'Registrasi berhasil! Silakan login.';
            } else {
                $error = 'Gagal register, silakan coba lagi.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register Admin - Sistem Absensi QR Code</title>
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
            <img src="../assets/images/logo.png.webp" alt="Logo" class="w-full h-full object-contain">
            <h2 class="text-2xl font-bold brand-primary mb-2">Register Admin</h2>
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
        <?php endif; ?>
        <form method="POST" class="space-y-6" autocomplete="off">
            <input type="text" name="username" class="w-full border-2 rounded-xl px-4 py-4" placeholder="Username" required value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>">
            <input type="text" name="nama_admin" class="w-full border-2 rounded-xl px-4 py-4" placeholder="Nama Admin" required value="<?= isset($_POST['nama_admin']) ? htmlspecialchars($_POST['nama_admin']) : '' ?>">
            <input type="password" name="password" class="w-full border-2 rounded-xl px-4 py-4" placeholder="Password" required>
            <input type="password" name="confirm_password" class="w-full border-2 rounded-xl px-4 py-4" placeholder="Konfirmasi Password" required>
            <button type="submit" class="btn-brand w-full py-4 rounded-xl text-lg font-bold">Daftar</button>
        </form>
        <div class="text-center mt-6">
            <a href="login.php" class="text-brand-secondary font-semibold">Sudah punya akun? Login</a>
        </div>
    </div>
</body>
</html>