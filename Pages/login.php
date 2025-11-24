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

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit();
}

// Konfigurasi lockout
$maxAttempts = 3; // maksimal percobaan login
$lockoutTime = 15 * 60; // waktu terkunci dalam detik (15 menit)

$error = '';

// Cek apakah saat ini sedang terkunci
$isLocked = false;
if (isset($_SESSION['lockout_time'])) {
    $elapsed = time() - $_SESSION['lockout_time'];
    if ($elapsed < $lockoutTime) {
        $remaining = $lockoutTime - $elapsed;
        $minutes = floor($remaining / 60);
        $seconds = $remaining % 60;
        $error = "Terlalu banyak percobaan login. Coba lagi dalam {$minutes} menit {$seconds} detik.";
        $isLocked = true;
    } else {
        // lockout selesai, reset counters
        unset($_SESSION['lockout_time']);
        unset($_SESSION['login_attempts']);
    }
}

// Proses login jika form dikirim dan tidak sedang terkunci
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$isLocked) {
    // Sanitasi input dasar
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING));
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Login berhasil: regenerasi session id untuk mencegah session fixation
                session_regenerate_id(true);
                
                // Simpan data session
                $_SESSION['admin_id'] = $admin['id_admin'];
                $_SESSION['username'] = $admin['username'];
                $_SESSION['nama_admin'] = $admin['nama_admin'];
                $_SESSION['role'] = $admin['role'];
                $_SESSION['show_welcome'] = true;
                
                // Reset percobaan login jika ada
                if (isset($_SESSION['login_attempts'])) {
                    unset($_SESSION['login_attempts']);
                }
                if (isset($_SESSION['lockout_time'])) {
                    unset($_SESSION['lockout_time']);
                }
                
                header('Location: dashboard.php');
                exit();
            } else {
                // Gagal: increment percobaan
                $_SESSION['login_attempts'] = isset($_SESSION['login_attempts']) ? $_SESSION['login_attempts'] + 1 : 1;
                
                // Log kegagalan (waktu, username, IP) — dapat dibaca di error_log/php error log
                $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                error_log(sprintf("[%s] Login gagal untuk username='%s' dari IP=%s (attempt %d)", date('Y-m-d H:i:s'), $username, $ip, $_SESSION['login_attempts']));
                
                if ($_SESSION['login_attempts'] >= $maxAttempts) {
                    $_SESSION['lockout_time'] = time();
                    $error = 'Terlalu banyak percobaan. Akun terkunci sementara selama 15 menit.';
                } else {
                    $remainingAttempts = $maxAttempts - $_SESSION['login_attempts'];
                    $error = 'Username atau password salah! Sisa percobaan: ' . $remainingAttempts;
                }
            }
        } catch (Exception $e) {
            // Jangan tunjukkan detail error ke user di production
            error_log("Login error: " . $e->getMessage());
            $error = 'Terjadi kesalahan sistem!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { box-sizing: border-box; }
        .brand-gradient { background: linear-gradient(135deg, #222660 0%, #F36E2B 100%);}
        .brand-primary { color: #222660; }
        .brand-secondary { color: #F36E2B; }
        .logo-container {
            width: 180px; height: 180px; background: white; border-radius: 20px; padding: 15px;
            box-shadow: 0 15px 40px rgba(34, 38, 96, 0.3); margin: 0 auto 20px;
        }
        .floating-shapes { position: absolute; top: 0; left: 0; width: 100%; height: 100%; overflow: hidden; pointer-events: none; }
        .shape {
            position: absolute; background: rgba(255,255,255,0.1); border-radius: 50%;
            animation: float 8s ease-in-out infinite; display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(5px); border: 1px solid rgba(255,255,255,0.2);
        }
        .shape i { color: rgba(255,255,255,0.6); animation: iconSpin 12s linear infinite; }
        .shape:nth-child(1){width:100px;height:100px;top:15%;left:8%;animation-delay:0s;}
        .shape:nth-child(1) i{font-size:40px;}
        .shape:nth-child(2){width:150px;height:150px;top:50%;right:5%;animation-delay:3s;}
        .shape:nth-child(2) i{font-size:60px;}
        .shape:nth-child(3){width:80px;height:80px;bottom:15%;left:15%;animation-delay:6s;}
        .shape:nth-child(3) i{font-size:32px;}
        .shape:nth-child(4){width:60px;height:60px;top:30%;right:25%;animation-delay:2s;}
        .shape:nth-child(4) i{font-size:24px;}
        .shape:nth-child(5){width:90px;height:90px;top:70%;right:40%;animation-delay:4s;}
        .shape:nth-child(5) i{font-size:36px;}
        .shape:nth-child(6){width:70px;height:70px;top:10%;right:15%;animation-delay:7s;}
        .shape:nth-child(6) i{font-size:28px;}
        @keyframes float {
            0%,100%{transform:translateY(0) rotate(0deg) scale(1);opacity:0.7;}
            33%{transform:translateY(-30px) rotate(120deg) scale(1.1);opacity:0.9;}
            66%{transform:translateY(15px) rotate(240deg) scale(0.9);opacity:0.5;}
        }
        @keyframes iconSpin { 0%{transform:rotate(0);}100%{transform:rotate(360deg);} }
        .login-card {
            backdrop-filter: blur(15px); background: rgba(255,255,255,0.95);
            border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            animation: fadeIn .8s;
        }
        @keyframes fadeIn {
            0% { opacity: 0; transform: scale(0.98) translateY(16px);}
            100% { opacity: 1; transform: scale(1) translateY(0);}
        }
        .input-group { position: relative; }
        .input-field {
            transition: all 0.3s ease; border: 2px solid #e5e7eb;
        }
        .input-field:focus {
            border-color: #222660; box-shadow: 0 0 0 4px rgba(34,38,96,0.1);
            transform: translateY(-2px);
        }
        .btn-login {
            background: linear-gradient(135deg,#222660 0%,#F36E2B 100%);
            transition: all 0.3s ease; position: relative; overflow: hidden;
            box-shadow: 0 6px 26px rgba(243,110,43,0.08);
        }
        .btn-login::before {
            content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;
            background:linear-gradient(90deg,transparent,rgba(255,255,255,0.2),transparent);
            transition:left 0.5s;
        }
        .btn-login:hover { transform:translateY(-3px);box-shadow:0 15px 35px rgba(34,38,96,0.2);}
        .btn-login:hover::before { left:100%; }
        .error-message { animation: shake 0.5s ease-in-out; }
        @keyframes shake { 0%,100%{transform:translateX(0);}25%{transform:translateX(-8px);}75%{transform:translateX(8px);} }
        .pulse-animation { animation: pulse 2s infinite; }
        @keyframes pulse {
            0% { box-shadow:0 0 0 0 rgba(243,110,43,0.7); }
            70% { box-shadow:0 0 0 10px rgba(243,110,43,0); }
            100% { box-shadow:0 0 0 0 rgba(243,110,43,0); }
        }
        .shortcut-link {
            color: #222660;
            background: #f5f6fc;
            border-radius: 11px;
            padding: 7px 22px;
            margin: 0 7px;
            font-weight: bold;
            text-decoration: none;
            transition: background .20s, color .20s;
            box-shadow: 0 3px 12px rgba(34,38,96,0.06);
        }
        .shortcut-link:hover {
            background: #F36E2B;
            color: #fff;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center brand-gradient relative overflow-hidden">

    <div class="floating-shapes">
        <div class="shape"><i class="fas fa-lock"></i></div>
        <div class="shape"><i class="fas fa-qrcode"></i></div>
        <div class="shape"><i class="fas fa-user-clock"></i></div>
        <div class="shape"><i class="fas fa-shield-alt"></i></div>
        <div class="shape"><i class="fas fa-fingerprint"></i></div>
        <div class="shape"><i class="fas fa-mobile-alt"></i></div>
    </div>

    <div class="login-card rounded-3xl p-10 w-full max-w-md relative z-10">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="logo-container pulse-animation">
                <img src="../assets/images/logo.png.webp" alt="Logo" class="w-full h-full object-contain">
            </div>
            <h1 class="text-3xl font-bold brand-primary mb-2">Sistem Absensi</h1>
            <p class="text-gray-600 mb-1 font-medium">QR Code Scanner</p>
            <p class="text-lg font-bold brand-secondary">Creed Creatives</p>
        </div>

        <!-- Error Message -->
        <?php if ($error): ?>
        <div class="bg-red-100 border-2 border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center error-message">
            <i class="fas fa-exclamation-triangle mr-3 text-lg"></i>
            <span><?= htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>

          <!-- Login Form -->
        <form method="POST" class="space-y-6" autocomplete="off">
            <div class="input-group">
                <label class="block text-gray-700 text-sm font-bold mb-3">
                    <i class="fas fa-user mr-2 brand-secondary"></i>Username
                </label>
                <input type="text" name="username" required autocomplete="username"
                       class="input-field w-full px-4 py-4 rounded-xl font-medium text-gray-700"
                       placeholder="Masukkan username Anda"
                       value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>

            <div class="input-group">
                <label class="block text-gray-700 text-sm font-bold mb-3">
                    <i class="fas fa-lock mr-2 brand-secondary"></i>Password
                </label>
                <div class="relative">
                    <input type="password" id="password" name="password" required autocomplete="current-password"
                           class="input-field w-full px-4 py-4 rounded-xl font-medium text-gray-700 pr-12"
                           placeholder="Masukkan password Anda">
                    <button type="button" id="togglePassword" 
                            class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>

            <!-- Shortcut Lupa Password, ditaruh sebelum button Masuk -->
            <div class="w-full text-right mb-2">
                <a href="forgot_password.php" class="text-sm text-brand-secondary hover:underline font-bold">
                    <i class="fa fa-key mr-1"></i>Lupa Password?
                </a>
            </div>

            <button type="submit" class="btn-login w-full text-white font-bold py-4 px-6 rounded-xl text-lg">
                <i class="fas fa-sign-in-alt mr-3"></i>
                Login
            </button>

            <!-- Shortcut Register, ditaruh setelah button Masuk -->
            <div class="w-full mt-4 text-center">
                <span class="text-sm text-gray-500">Belum punya akun? </span>
                <a href="register.php" class="text-sm text-brand-secondary hover:underline font-bold">
                    <i class="fa fa-user-plus mr-1"></i>Daftar di sini
                </a>
            </div>
        </form>

        <!-- Footer -->
        <div class="text-center mt-4">
            <div class="flex items-center justify-center space-x-2 text-sm text-gray-500 mb-2">
                <i class="fas fa-shield-alt brand-secondary"></i>
                <span>Sistem Keamanan Terjamin</span>
            </div>
            <p class="text-xs text-gray-400">© 2025 Creed Creatives. All rights reserved.</p>
        </div>
    </div>

    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const icon = this.querySelector('i');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    </script>
   
</body>
</html>