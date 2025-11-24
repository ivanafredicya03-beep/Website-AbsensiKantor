<?php
// Pages/logout.php
// Pastikan tidak ada output sebelum header() dipanggil

session_start();

// Bersihkan semua data session
$_SESSION = [];

// Hapus cookie session jika ada
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Hancurkan session di server
session_destroy();

// (Opsional) Catat logout untuk audit
// $user = isset($_SESSION['username']) ? $_SESSION['username'] : 'unknown';
// error_log(sprintf("[%s] User logged out: %s", date('Y-m-d H:i:s'), $user));

header('Location: login.php');
exit();
?>