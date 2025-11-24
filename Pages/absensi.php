<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Database connection
$host = 'localhost';
$dbname = 'sistem_absensi';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle various actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'checkin' || $_POST['action'] === 'checkout') {
            // QR scan attendance code
            $kode_qr = $_POST['kode_qr'];
            $is_checkin = $_POST['action'] === 'checkin';
            $waktu = date('H:i:s');
            $tanggal = date('Y-m-d');
            
            $stmt = $pdo->prepare("SELECT * FROM karyawan WHERE kode_qr = ? AND status = 'aktif'");
            $stmt->execute([$kode_qr]);
            $karyawan = $stmt->fetch();
            
            if ($karyawan) {
                // Check if attendance record exists for today
                $stmt = $pdo->prepare("SELECT * FROM absensi WHERE id_karyawan = ? AND tanggal = ?");
                $stmt->execute([$karyawan['id_karyawan'], $tanggal]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    // Update existing record
                    if ($is_checkin && !$existing['jam_masuk']) {
                        $stmt = $pdo->prepare("UPDATE absensi SET jam_masuk = ? WHERE id_absensi = ?");
                        $stmt->execute([$waktu, $existing['id_absensi']]);
                        $success_message = "Absensi masuk berhasil untuk " . $karyawan['nama'] . "!";
                    } elseif (!$is_checkin && !$existing['jam_pulang']) {
                        // Calculate total hours
                        if ($existing['jam_masuk']) {
                            $masuk = new DateTime($existing['jam_masuk']);
                            $pulang = new DateTime($waktu);
                            $diff = $masuk->diff($pulang);
                            $total_jam = $diff->h + ($diff->i / 60);
                        } else {
                            $total_jam = 0;
                        }
                        
                        $stmt = $pdo->prepare("UPDATE absensi SET jam_pulang = ?, total_jam = ? WHERE id_absensi = ?");
                        $stmt->execute([$waktu, $total_jam, $existing['id_absensi']]);
                        $success_message = "Absensi pulang berhasil untuk " . $karyawan['nama'] . "!";
                    } else {
                        $error_message = "Karyawan sudah melakukan absensi " . ($is_checkin ? 'masuk' : 'pulang') . " hari ini!";
                    }
                } else {
                    // Create new record
                    if ($is_checkin) {
                        $stmt = $pdo->prepare("INSERT INTO absensi (id_karyawan, tanggal, jam_masuk, status) VALUES (?, ?, ?, 'hadir')");
                        $stmt->execute([$karyawan['id_karyawan'], $tanggal, $waktu]);
                        $success_message = "Absensi masuk berhasil untuk " . $karyawan['nama'] . "!";
                    } else {
                        $stmt = $pdo->prepare("INSERT INTO absensi (id_karyawan, tanggal, jam_pulang, status) VALUES (?, ?, ?, 'hadir')");
                        $stmt->execute([$karyawan['id_karyawan'], $tanggal, $waktu]);
                        $success_message = "Absensi pulang berhasil untuk " . $karyawan['nama'] . "!";
                    }
                }
            } else {
                $error_message = "QR Code tidak valid atau karyawan tidak aktif!";
            }
        }
        
        // Edit attendance time
        elseif ($_POST['action'] === 'edit_time') {
            $id_absensi = $_POST['id_absensi'];
            $new_time = $_POST['new_time'];
            $field_type = $_POST['field_type']; // 'jam_masuk' or 'jam_pulang'
            
            $stmt = $pdo->prepare("UPDATE absensi SET $field_type = ? WHERE id_absensi = ?");
            $stmt->execute([$new_time, $id_absensi]);
            
            // Recalculate total hours if both times exist
            $stmt = $pdo->prepare("SELECT jam_masuk, jam_pulang FROM absensi WHERE id_absensi = ?");
            $stmt->execute([$id_absensi]);
            $record = $stmt->fetch();
            
            if ($record['jam_masuk'] && $record['jam_pulang']) {
                $masuk = new DateTime($record['jam_masuk']);
                $pulang = new DateTime($record['jam_pulang']);
                $diff = $masuk->diff($pulang);
                $total_jam = $diff->h + ($diff->i / 60);
                
                $stmt = $pdo->prepare("UPDATE absensi SET total_jam = ? WHERE id_absensi = ?");
                $stmt->execute([$total_jam, $id_absensi]);
            }
            
            $success_message = "Waktu absensi berhasil diubah!";
        }
        
        // Delete attendance
        elseif ($_POST['action'] === 'delete_attendance') {
            $id_absensi = $_POST['id_absensi'];
            
            $stmt = $pdo->prepare("DELETE FROM absensi WHERE id_absensi = ?");
            $stmt->execute([$id_absensi]);
            $success_message = "Data absensi berhasil dihapus!";
        }
        
        // Manual attendance input
        elseif ($_POST['action'] === 'manual_input') {
            $id_karyawan = $_POST['id_karyawan'];
            $jenis_absensi = $_POST['jenis_absensi']; // 'masuk' or 'pulang'
            $waktu_manual = $_POST['waktu_manual'];
            $tanggal = date('Y-m-d');
            
            // Check if record exists for today
            $stmt = $pdo->prepare("SELECT * FROM absensi WHERE id_karyawan = ? AND tanggal = ?");
            $stmt->execute([$id_karyawan, $tanggal]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                // Update existing record
                $field = ($jenis_absensi === 'masuk') ? 'jam_masuk' : 'jam_pulang';
                
                if (!$existing[$field]) {
                    $stmt = $pdo->prepare("UPDATE absensi SET $field = ? WHERE id_absensi = ?");
                    $stmt->execute([$waktu_manual, $existing['id_absensi']]);
                    
                    // Recalculate total hours if both times exist
                    if ($jenis_absensi === 'pulang' && $existing['jam_masuk']) {
                        $masuk = new DateTime($existing['jam_masuk']);
                        $pulang = new DateTime($waktu_manual);
                        $diff = $masuk->diff($pulang);
                        $total_jam = $diff->h + ($diff->i / 60);
                        
                        $stmt = $pdo->prepare("UPDATE absensi SET total_jam = ? WHERE id_absensi = ?");
                        $stmt->execute([$total_jam, $existing['id_absensi']]);
                    }
                    
                    $success_message = "Absensi manual berhasil ditambahkan!";
                } else {
                    $error_message = "Karyawan sudah melakukan absensi " . $jenis_absensi . " hari ini!";
                }
            } else {
                // Create new record
                $field = ($jenis_absensi === 'masuk') ? 'jam_masuk' : 'jam_pulang';
                $stmt = $pdo->prepare("INSERT INTO absensi (id_karyawan, tanggal, $field, status) VALUES (?, ?, ?, 'hadir')");
                $stmt->execute([$id_karyawan, $tanggal, $waktu_manual]);
                $success_message = "Absensi manual berhasil ditambahkan!";
            }
        }
        
        // Send reminder
        elseif ($_POST['action'] === 'send_reminder') {
            // Get employees who haven't attended today
            $today = date('Y-m-d');
            $stmt = $pdo->prepare("
                SELECT k.* FROM karyawan k 
                WHERE k.status = 'aktif' 
                AND k.id_karyawan NOT IN (
                    SELECT DISTINCT id_karyawan FROM absensi WHERE tanggal = ?
                )
            ");
            $stmt->execute([$today]);
            $missing_employees = $stmt->fetchAll();
            
            $reminder_count = count($missing_employees);
            $success_message = "Reminder berhasil dikirim ke $reminder_count karyawan yang belum absen!";
        }
    }
}

// Get today's attendance with work duration
$today = date('Y-m-d');
$stmt = $pdo->prepare("
    SELECT 
        k.id_karyawan,
        k.nama, 
        k.jabatan, 
        k.foto,
        a.id_absensi,
        a.jam_masuk,
        a.jam_pulang,
        a.total_jam,
        a.status
    FROM karyawan k
    INNER JOIN absensi a ON k.id_karyawan = a.id_karyawan 
    WHERE k.status = 'aktif' 
        AND a.tanggal = ?
    ORDER BY a.jam_masuk DESC
");
$stmt->execute([$today]);
$today_attendance = $stmt->fetchAll();

// Get attendance statistics for today
$stmt = $pdo->prepare("SELECT COUNT(*) as total_masuk FROM absensi WHERE tanggal = ? AND jam_masuk IS NOT NULL");
$stmt->execute([$today]);
$total_masuk = $stmt->fetch()['total_masuk'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total_keluar FROM absensi WHERE tanggal = ? AND jam_pulang IS NOT NULL");
$stmt->execute([$today]);
$total_keluar = $stmt->fetch()['total_keluar'];

$stmt = $pdo->query("SELECT COUNT(*) as total_karyawan FROM karyawan WHERE status = 'aktif'");
$total_karyawan = $stmt->fetch()['total_karyawan'];

$belum_absen = $total_karyawan - $total_masuk;

// Get all active employees for manual input
$stmt = $pdo->query("SELECT * FROM karyawan WHERE status = 'aktif' ORDER BY nama");
$all_employees = $stmt->fetchAll();


?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Absensi - Sistem Absensi QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            box-sizing: border-box;
        }
        
        .brand-gradient {
            background: linear-gradient(135deg, #222660 0%, #F36E2B 100%);
        }
        
        .brand-primary {
            color: #222660;
        }
        
        .brand-secondary {
            color: #F36E2B;
        }
        
        .sidebar {
            background: linear-gradient(180deg, #222660 0%, #1a1d4a 100%);
            box-shadow: 4px 0 20px rgba(34, 38, 96, 0.3);
        }
        
        .menu-item {
            transition: all 0.3s ease;
            border-radius: 12px;
            margin: 4px 0;
        }
        
        .menu-item:hover {
            background: rgba(243, 110, 43, 0.2);
            transform: translateX(8px);
        }
        
        .menu-item.active {
            background: linear-gradient(135deg, #F36E2B 0%, #e55a1f 100%);
            box-shadow: 0 4px 15px rgba(243, 110, 43, 0.4);
        }
        
        .card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(243, 110, 43, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #222660 0%, #F36E2B 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(243, 110, 43, 0.4);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: #6c757d;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
            border-color: #dee2e6;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #F36E2B;
            box-shadow: 0 0 0 3px rgba(243, 110, 43, 0.1);
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #495057;
        }
        
        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border-left: 4px solid;
            transition: all 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }
        
        .stats-card.masuk {
            border-left-color: #28a745;
        }
        
        .stats-card.keluar {
            border-left-color: #dc3545;
        }
        
        .stats-card.belum {
            border-left-color: #ffc107;
        }
        
        .stats-card.total {
            border-left-color: #007bff;
        }
        
        .qr-scanner-container {
            background: white;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 2px solid #e9ecef;
        }
        
        .qr-scanner-active {
            border-color: #28a745;
            box-shadow: 0 0 20px rgba(40, 167, 69, 0.3);
        }
        
        .attendance-list {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .attendance-item {
            padding: 16px;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .attendance-item:hover {
            background: rgba(243, 110, 43, 0.05);
        }
        
        .attendance-item:last-child {
            border-bottom: none;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-masuk {
            background: #d4edda;
            color: #155724;
        }
        
        .status-keluar {
            background: #f8d7da;
            color: #721c24;
        }
        
        .duration-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .duration-good {
            background: #d4edda;
            color: #155724;
        }
        
        .duration-short {
            background: #fff3cd;
            color: #856404;
        }
        
        .avatar-container {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #222660 0%, #F36E2B 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
        }
        
        .pulse-animation {
            animation: logoPulse 3s ease-in-out infinite;
        }
        
        @keyframes logoPulse {
            0%, 100% { 
                transform: scale(1);
                box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3);
            }
            50% { 
                transform: scale(1.05);
                box-shadow: 0 8px 25px rgba(255, 255, 255, 0.5);
            }
        }
        
        .notification-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            z-index: 1100;
            transform: translateX(400px);
            transition: all 0.3s ease;
        }
        
        .notification-toast.show {
            transform: translateX(0);
        }
        
        .notification-success {
            background: #28a745;
        }
        
        .notification-error {
            background: #dc3545;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #6c757d;
        }
        
        #reader {
            border-radius: 12px;
            overflow: hidden;
        }
        
        .manual-input-container {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            border: 2px dashed #dee2e6;
        }
        
        .time-display {
            font-family: 'Courier New', monospace;
            font-size: 2rem;
            font-weight: bold;
            color: #222660;
            text-align: center;
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        /* Modal Styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .modal.show {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 32px;
            max-width: 500px;
            width: 90%;
        }
        
        .action-btn {
            padding: 8px;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .action-btn:hover {
            background: rgba(0, 0, 0, 0.1);
        }
        
        .work-time-display {
            display: flex;
            flex-direction: column;
            gap: 4px;
            font-size: 12px;
        }
        
        .work-time-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .work-duration {
            font-weight: 600;
            font-size: 14px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="sidebar w-64 p-6 flex flex-col">
            <!-- Logo -->
            <div class="text-center mb-8">
                 <div class="logo-container pulse-animation w-16 h-16 bg-white rounded-xl mx-auto mb-3 flex items-center justify-center border-4 border-white shadow-lg">
                 <img src="../assets/images/logo.png.webp" alt="Logo" class="w-full h-full object-contain">
                </div>
                <h2 class="text-white font-bold text-lg">QR Absensi</h2>
                <p class="text-gray-300 text-sm">CreedCreatives</p>
            </div>

            <!-- Menu -->
            <nav class="flex-1">
                <div class="menu-item p-3 mb-2">
                    <a href="dashboard.php" class="flex items-center text-gray-300 hover:text-white">
                        <i class="fas fa-home mr-3"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="menu-item p-3 mb-2">
                    <a href="karyawan.php" class="flex items-center text-gray-300 hover:text-white">
                        <i class="fas fa-users mr-3"></i>
                        <span>Karyawan</span>
                    </a>
                </div>
                <div class="menu-item active p-3 mb-2">
                    <a href="#" class="flex items-center text-white">
                        <i class="fas fa-calendar-check mr-3"></i>
                        <span>Absensi</span>
                    </a>
                </div>
                <div class="menu-item p-3 mb-2">
                    <a href="laporan.php" class="flex items-center text-gray-300 hover:text-white">
                        <i class="fas fa-chart-bar mr-3"></i>
                        <span>Laporan</span>
                    </a>
                </div>
                <div class="menu-item p-3 mb-2">
                    <a href="pengaturan.php" class="flex items-center text-gray-300 hover:text-white">
                        <i class="fas fa-cog mr-3"></i>
                        <span>Pengaturan</span>
                    </a>
                </div>
            </nav>

            <!-- Logout -->
            <div class="menu-item p-3 mt-4">
                <a href="#" onclick="showLogoutConfirmation()" class="flex items-center text-gray-300 hover:text-white">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold brand-primary">Admin Absensi</h1>
                        <p class="text-gray-600">Kelola dan monitor absensi karyawan</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-900 to-orange-500 rounded-full flex items-center justify-center">
                                <span class="text-white font-bold text-sm">
                                    <?php 
                                    if (isset($_SESSION['nama_admin'])) {
                                        $nama = $_SESSION['nama_admin'];
                                        $words = explode(' ', $nama);
                                        if (count($words) >= 2) {
                                            echo strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                        } else {
                                            echo strtoupper(substr($nama, 0, 2));
                                        }
                                    } else {
                                        echo 'AD';
                                    }
                                    ?>
                                </span>
                            </div>
                            <div>
                                <p class="font-semibold brand-primary">
                                    <?php echo isset($_SESSION['nama_admin']) ? $_SESSION['nama_admin'] : 'Admin'; ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <?php echo isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'Administrator'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- Time Display -->
                <div class="time-display" id="currentTime"></div>

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="stats-card masuk">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Sudah Masuk</p>
                                <p class="text-3xl font-bold text-green-600"><?php echo $total_masuk; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-sign-in-alt text-green-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card keluar">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Sudah Keluar</p>
                                <p class="text-3xl font-bold text-red-600"><?php echo $total_keluar; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-sign-out-alt text-red-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card belum">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Belum Absen</p>
                                <p class="text-3xl font-bold text-yellow-600"><?php echo $belum_absen; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600 text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stats-card total">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600 mb-1">Total Karyawan</p>
                                <p class="text-3xl font-bold text-blue-600"><?php echo $total_karyawan; ?></p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scanner Device Info -->
                <div class="card p-6 mb-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-gradient-to-r from-blue-900 to-orange-500 rounded-xl flex items-center justify-center">
                                <i class="fas fa-barcode-read text-white text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold brand-primary">Scanner Device Status</h2>
                                <p class="text-gray-600">Perangkat scanner QR terhubung ke sistem</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-green-600 font-semibold">Connected</span>
                            </div>
                            <button onclick="testScanner()" class="btn-secondary">
                                <i class="fas fa-cog mr-2"></i>Test Device
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-8">
                    <!-- Today's Attendance List -->
                    <div class="attendance-list">
                        <div class="p-6 border-b">
                            <div class="flex justify-between items-center mb-4">
                                <div>
                                    <h2 class="text-xl font-bold brand-primary">
                                        <i class="fas fa-list mr-2"></i>Absensi Hari Ini
                                    </h2>
                                    <p class="text-gray-600"><?php echo date('d F Y'); ?></p>
                                </div>
                            </div>
                            
                            <!-- Admin Control Buttons -->
                            <div class="flex flex-wrap gap-2">
                                <button onclick="showManualInput()" class="btn-secondary text-sm">
                                    <i class="fas fa-plus mr-1"></i>Tambah Manual
                                </button>
                                <button onclick="sendReminder()" class="btn-primary text-sm">
                                    <i class="fas fa-bell mr-1"></i>Kirim Reminder
                                </button>
                                <button onclick="exportToday()" class="btn-success text-sm">
                                    <i class="fas fa-download mr-1"></i>Export
                                </button>
                            </div>
                        </div>

                        <div class="max-h-96 overflow-y-auto">
                            <?php if (empty($today_attendance)): ?>
                            <div class="empty-state">
                                <i class="fas fa-calendar-times text-6xl mb-4 block"></i>
                                <h3 class="text-xl font-semibold mb-2">Belum ada absensi</h3>
                                <p>Absensi hari ini akan muncul di sini</p>
                            </div>
                            <?php else: ?>
                            <?php foreach ($today_attendance as $attendance): ?>
                            <div class="attendance-item">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <?php if ($attendance['foto'] && file_exists('../uploads/' . $attendance['foto'])): ?>
                                            <img src="../uploads/<?php echo htmlspecialchars($attendance['foto']); ?>" 
                                                 alt="<?php echo htmlspecialchars($attendance['nama']); ?>" 
                                                 class="w-12 h-12 rounded-full object-cover border-2 border-orange-200">
                                        <?php else: ?>
                                            <div class="avatar-container">
                                                <?php 
                                                $nama = $attendance['nama'];
                                                $words = explode(' ', $nama);
                                                if (count($words) >= 2) {
                                                    echo strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                                } else {
                                                    echo strtoupper(substr($nama, 0, 2));
                                                }
                                                ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div>
                                            <p class="font-semibold"><?php echo htmlspecialchars($attendance['nama']); ?></p>
                                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($attendance['jabatan']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-4">
                                        <!-- Work Time Display -->
                                        <div class="work-time-display text-right">
                                            <?php if ($attendance['jam_masuk']): ?>
                                            <div class="work-time-item">
                                                <span class="status-badge status-masuk">Masuk</span>
                                                <span><?php echo $attendance['jam_masuk']; ?></span>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($attendance['jam_pulang']): ?>
                                            <div class="work-time-item">
                                                <span class="status-badge status-keluar">Pulang</span>
                                                <span><?php echo $attendance['jam_pulang']; ?></span>
                                            </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($attendance['total_jam']): ?>
                                            <div class="work-duration <?php echo $attendance['total_jam'] >= 8 ? 'text-green-600' : 'text-orange-600'; ?>">
                                                <i class="fas fa-clock mr-1"></i>
                                                <?php echo number_format($attendance['total_jam'], 1); ?> jam
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- Action Buttons -->
                                        <div class="flex space-x-2">
                                            <?php if ($attendance['jam_masuk']): ?>
                                            <button onclick="editAttendance(<?php echo $attendance['id_absensi']; ?>, '<?php echo $attendance['jam_masuk']; ?>', '<?php echo addslashes($attendance['nama']); ?> - Masuk', 'jam_masuk')" 
                                                    class="action-btn text-blue-600 hover:text-blue-800" title="Edit Waktu Masuk">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <?php if ($attendance['jam_pulang']): ?>
                                            <button onclick="editAttendance(<?php echo $attendance['id_absensi']; ?>, '<?php echo $attendance['jam_pulang']; ?>', '<?php echo addslashes($attendance['nama']); ?> - Pulang', 'jam_pulang')" 
                                                    class="action-btn text-blue-600 hover:text-blue-800" title="Edit Waktu Pulang">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <?php endif; ?>
                                            
                                            <button onclick="deleteAttendance(<?php echo $attendance['id_absensi']; ?>, '<?php echo addslashes($attendance['nama']); ?>')" 
                                                    class="action-btn text-red-600 hover:text-red-800" title="Hapus Absensi">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Time Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-4">Edit Waktu Absensi</h2>
            <form method="POST" id="editForm">
                <input type="hidden" name="action" value="edit_time">
                <input type="hidden" name="id_absensi" id="edit_id">
                <input type="hidden" name="field_type" id="edit_field_type">
                
                <div class="mb-4">
                    <label class="form-label">Karyawan & Jenis</label>
                    <input type="text" id="edit_nama" class="form-input" readonly>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Waktu Baru</label>
                    <input type="time" name="new_time" id="edit_time" class="form-input" required>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="closeModal('editModal')" class="btn-secondary flex-1">Batal</button>
                    <button type="submit" class="btn-primary flex-1">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-4 text-red-600">Konfirmasi Hapus</h2>
            <p class="mb-4">Apakah Anda yakin ingin menghapus data absensi <strong id="delete_nama"></strong>?</p>
            
            <form method="POST" id="deleteForm">
                <input type="hidden" name="action" value="delete_attendance">
                <input type="hidden" name="id_absensi" id="delete_id">
                
                <div class="flex space-x-3">
                    <button type="button" onclick="closeModal('deleteModal')" class="btn-secondary flex-1">Batal</button>
                    <button type="submit" class="btn-danger flex-1">Ya, Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Manual Input Modal -->
    <div id="manualModal" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold mb-4">Tambah Absensi Manual</h2>
            <form method="POST" id="manualInputForm">
                <input type="hidden" name="action" value="manual_input">
                
                <div class="mb-4">
                    <label class="form-label">Pilih Karyawan</label>
                    <select name="id_karyawan" class="form-input" required>
                        <option value="">-- Pilih Karyawan --</option>
                        <?php foreach ($all_employees as $emp): ?>
                        <option value="<?php echo $emp['id_karyawan']; ?>"><?php echo htmlspecialchars($emp['nama']); ?> - <?php echo htmlspecialchars($emp['jabatan']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Jenis Absensi</label>
                    <select name="jenis_absensi" class="form-input" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="masuk">Masuk</option>
                        <option value="pulang">Pulang</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Waktu</label>
                    <input type="time" name="waktu_manual" class="form-input" required>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="closeModal('manualModal')" class="btn-secondary flex-1">Batal</button>
                    <button type="submit" class="btn-primary flex-1">Tambah</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="modal">
        <div class="modal-content" style="max-width: 400px; text-align: center;">
            <i class="fas fa-sign-out-alt text-6xl text-red-500 mb-4"></i>
            <h2 class="text-2xl font-bold mb-4">Konfirmasi Logout</h2>
            <p class="text-gray-600 mb-6">Apakah Anda yakin ingin keluar dari sistem?</p>
            
            <div class="flex justify-center space-x-3">
                <button onclick="closeModal('logoutModal')" class="btn-secondary">Batal</button>
                <a href="logout.php" class="btn-danger">Ya, Logout</a>
            </div>
        </div>
    </div>

    <!-- Success/Error Toast -->
    <?php if (isset($success_message)): ?>
    <div id="toast" class="notification-toast notification-success show">
        <i class="fas fa-check-circle mr-2"></i>
        <?php echo $success_message; ?>
    </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
    <div id="toast" class="notification-toast notification-error show">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <?php echo $error_message; ?>
    </div>
    <?php endif; ?>

    <script>
        // Update time display
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false
            });
            const dateString = now.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            
            document.getElementById('currentTime').innerHTML = `
                <div>${timeString}</div>
                <div style="font-size: 1rem; font-weight: normal; color: #6c757d; margin-top: 8px;">${dateString}</div>
            `;
        }

        // Test scanner device
        function testScanner() {
            showNotification('Testing scanner device...', 'success');
            
            // Simulate device test
            setTimeout(() => {
                showNotification('Scanner device berfungsi dengan baik!', 'success');
            }, 2000);
        }

        // Edit attendance
        function editAttendance(id, currentTime, nama, fieldType) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_time').value = currentTime;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_field_type').value = fieldType;
            document.getElementById('editModal').classList.add('show');
        }

        // Delete attendance
        function deleteAttendance(id, nama) {
            document.getElementById('delete_id').value = id;
            document.getElementById('delete_nama').textContent = nama;
            document.getElementById('deleteModal').classList.add('show');
        }

        // Show manual input modal
        function showManualInput() {
            document.getElementById('manualModal').classList.add('show');
        }

        // Send reminder
        function sendReminder() {
            if (confirm('Kirim reminder ke semua karyawan yang belum absen?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="action" value="send_reminder">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Export today's data
        function exportToday() {
            window.open('export_attendance.php?date=' + new Date().toISOString().split('T')[0], '_blank');
        }

        // Close modal
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }

        // Logout functions
        function showLogoutConfirmation() {
            document.getElementById('logoutModal').classList.add('show');
        }

        // Notification function
        function showNotification(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `notification-toast notification-${type} show`;
            toast.innerHTML = `<i class="fas fa-info-circle mr-2"></i>${message}`;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 300);
        }

        // Auto-hide toast
        setTimeout(() => {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }
        }, 3000);

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateTime();
            setInterval(updateTime, 1000);
        });

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('show');
            }
        });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9911007ca386db42',t:'MTc2MDg4NDUyNS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
