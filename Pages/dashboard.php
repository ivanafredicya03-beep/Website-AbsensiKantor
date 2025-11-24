<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Absensi QR Code</title>

    <!-- Utilities / Frameworks -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- Centralized app CSS -->
    <link rel="stylesheet" href="../assets/css/app.css">

    <!-- Small animation CSS kept local to ensure animations work even if app.css missing some rules -->
    <style>
        /* Floating shapes & icons animation (fallback if not present in app.css) */
        .floating-shapes .shape {
            position: absolute;
            background: rgba(243,110,43,0.05);
            border-radius: 50%;
            animation: float-shape 15s ease-in-out infinite;
            pointer-events: none;
        }
        .floating-icons .floating-icon {
            position: absolute;
            color: rgba(243,110,43,0.28);
            font-size: 20px;
            animation: float-icon 8s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes float-shape {
            0%,100% { transform: translateY(0) rotate(0deg); opacity: .9; }
            33% { transform: translateY(-20px) rotate(120deg); opacity: .7; }
            66% { transform: translateY(10px) rotate(240deg); opacity: .8; }
        }
        @keyframes float-icon {
            0%,100% { transform: translateY(0) translateX(0) rotate(0deg); opacity:.3; }
            25% { transform: translateY(-30px) translateX(20px) rotate(90deg); opacity:.6; }
            50% { transform: translateY(-10px) translateX(-15px) rotate(180deg); opacity:.4; }
            75% { transform: translateY(20px) translateX(10px) rotate(270deg); opacity:.5; }
        }

        /* Notification bell animation */
        .notify-bell-active {
            animation: bell-ring 1s ease-in-out 0s 3;
            color: #F36E2B;
        }
        @keyframes bell-ring {
            0% { transform: rotate(0deg); }
            15% { transform: rotate(15deg); }
            30% { transform: rotate(-10deg); }
            45% { transform: rotate(8deg); }
            60% { transform: rotate(-6deg); }
            75% { transform: rotate(4deg); }
            100% { transform: rotate(0deg); }
        }

        /* Notification badge pop */
        .notification-badge.pop {
            animation: pop-badge 0.9s ease-in-out 0s 1;
        }
        @keyframes pop-badge {
            0% { transform: scale(0.6); opacity: 0; }
            40% { transform: scale(1.15); opacity: 1; }
            70% { transform: scale(0.95); }
            100% { transform: scale(1); }
        }

        /* Ensure bell is clickable & accessible */
        #notificationBell { cursor: pointer; }
    </style>
</head>

<body class="bg-gray-50">
    <!-- Floating Background Shapes -->
    <div class="floating-shapes">
        <div class="shape" style="width:200px;height:200px;top:10%;right:10%;"></div>
        <div class="shape" style="width:150px;height:150px;bottom:20%;left:15%;"></div>
        <div class="shape" style="width:100px;height:100px;top:60%;right:20%;"></div>
    </div>

    <!-- Floating Animated Icons -->
    <div class="floating-icons">
        <i class="floating-icon fas fa-qrcode" style="top:20%;left:10%;"></i>
        <i class="floating-icon fas fa-users" style="top:60%;right:15%;"></i>
        <i class="floating-icon fas fa-calendar-check" style="bottom:30%;left:20%;"></i>
        <i class="floating-icon fas fa-chart-line" style="top:40%;right:30%;"></i>
        <i class="floating-icon fas fa-clock" style="bottom:20%;right:10%;"></i>
        <i class="floating-icon fas fa-check-circle" style="top:70%;left:40%;"></i>
    </div>

    <div class="flex h-screen">
        <!-- Sidebar (partial) -->
        <?php
        $active = 'dashboard';
        include __DIR__ . '/_sidebar.php';
        ?>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Header -->
            <div class="bg-white shadow-sm border-b p-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold brand-primary">Dashboard</h1>
                        <p class="text-gray-600">Selamat datang di sistem absensi QR Code</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <!-- Add id so JS can animate / attach events -->
                            <i id="notificationBell" class="fas fa-bell text-gray-400 text-xl" aria-hidden="true"></i>
                            <div class="notification-badge" id="notificationCount">0</div>
                        </div>

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
                                <p class="font-semibold brand-primary" id="userName">
                                    <?php echo isset($_SESSION['nama_admin']) ? $_SESSION['nama_admin'] : 'Admin'; ?>
                                </p>
                                <p class="text-sm text-gray-500" id="userRole">
                                    <?php echo isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'Administrator'; ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content (kept unchanged) -->
            <div class="p-6">
                <!-- ... rest of content unchanged (stats cards, charts, recent activity) ... -->
                <!-- For brevity the rest of the same HTML remains — stats cards/chart/recent activity markup unchanged -->
                <!-- (full markup kept as in your current file) -->

                <!-- Welcome Card -->
                <div class="welcome-card p-6 mb-6 relative">
                    <div class="relative z-10">
                        <h2 class="text-2xl font-bold mb-2">
                            Selamat Datang<?php echo isset($_SESSION['nama_admin']) ? ', ' . explode(' ', $_SESSION['nama_admin'])[0] : ''; ?>! 👋
                        </h2>
                        <p class="text-gray-200 mb-4">Kelola sistem absensi dengan mudah dan efisien</p>
                        <button id="startNowBtn" class="bg-white text-blue-900 px-6 py-2 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                            Mulai Sekarang
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <div class="stats-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Total Karyawan</p>
                                <p class="text-3xl font-bold brand-primary" id="totalKaryawan">0</p>
                                <p class="text-gray-400 text-sm" id="karyawanTrend">Menunggu data...</p>
                            </div>
                            <div class="stats-icon brand-gradient">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stats-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Hadir Hari Ini</p>
                                <p class="text-3xl font-bold brand-primary" id="hadirHariIni">0</p>
                                <p class="text-gray-400 text-sm" id="persentaseHadir">Menunggu data...</p>
                            </div>
                            <div class="stats-icon bg-green-500">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stats-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Izin / Sakit</p>
                                <p class="text-3xl font-bold brand-primary" id="terlambat">0</p>
                                <p class="text-gray-400 text-sm" id="persentaseTerlambat">Menunggu data...</p>
                            </div>
                            <div class="stats-icon bg-yellow-500">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                    </div>
                    <div class="stats-card p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Alpa</p>
                                <p class="text-3xl font-bold brand-primary" id="tidakHadir">0</p>
                                <p class="text-gray-400 text-sm" id="persentaseTidakHadir">Menunggu data...</p>
                            </div>
                            <div class="stats-icon bg-red-500">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Recent Activity (kept) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Chart -->
                    <div class="chart-container">
                        <div class="p-6 border-b">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-xl font-bold brand-primary">Grafik Kehadiran Mingguan</h3>
                                    <p class="text-gray-600">7 hari terakhir</p>
                                </div>
                                <div class="w-12 h-12 bg-gradient-to-r from-blue-900 to-orange-500 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-chart-line text-white text-xl"></i>
                                </div>
                            </div>
                        </div>
                        <div class="p-6">
                            <div class="h-64 flex items-center justify-center" id="weeklyChart">
                                <div class="empty-state text-center">
                                    <i class="fas fa-chart-bar text-4xl mb-4 text-gray-300"></i>
                                    <p class="text-lg font-medium text-gray-600">Belum ada data kehadiran</p>
                                    <p class="text-sm text-gray-500">Grafik akan muncul setelah ada data absensi</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="chart-container">
                        <div class="p-6 border-b">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-xl font-bold brand-primary">Aktivitas Terbaru</h3>
                                    <p class="text-gray-600">Absensi hari ini</p>
                                </div>
                                <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-blue-500 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-history text-white text-xl"></i>
                                </div>
                            </div>
                        </div>

                        <div class="max-h-80 overflow-y-auto">
                            <div class="p-8 text-center text-gray-500" id="recentActivity">
                                <i class="fas fa-calendar-times text-4xl mb-3 block text-gray-300"></i>
                                <p class="text-lg font-medium text-gray-600">Belum ada aktivitas hari ini</p>
                                <p class="text-sm text-gray-500">Aktivitas absensi akan muncul di sini</p>
                            </div>
                        </div>

                        <div class="p-4 border-t text-center">
                            <a href="#" class="text-orange-600 hover:text-orange-700 font-semibold transition-colors">
                                Lihat Semua Aktivitas <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Set current user for frontend JS and motivation messages -->
    <script>
        window.currentUser = <?php echo json_encode([
            'name' => $_SESSION['nama_admin'] ?? 'Admin',
            'role' => isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'Administrator',
            'username' => $_SESSION['username'] ?? 'admin'
        ]); ?>;

        // optional messages (dashboard.js also provides its own list if needed)
        window.motivationMessages = window.motivationMessages || [
            { title: "Selamat Datang Admin Kece! 🎉", text: "Siap-siap jadi admin terhebat hari ini! Semangat mengelola absensi ya! 💪" },
            { title: "Selamat Datang Admin Jagoan! ⭐", text: "Hari ini pasti bakal produktif banget! Yuk kelola absensi dengan penuh semangat! 🔥" }
        ];
    </script>

    <!-- Frontend dashboard logic -->
    <script src="../assets/js/dashboard.js" defer></script>

    <!-- Small compatibility helpers: observe notificationCount changes and animate bell/badge -->
    <script>
        // when notificationCount value changes, briefly animate bell + badge
        function setBellActive(active) {
            const bell = document.getElementById('notificationBell');
            const badge = document.getElementById('notificationCount');
            if (!bell || !badge) return;
            if (active) {
                bell.classList.add('notify-bell-active');
                badge.classList.add('pop');
                // remove classes after animations complete
                setTimeout(() => {
                    bell.classList.remove('notify-bell-active');
                }, 3000);
                setTimeout(() => {
                    badge.classList.remove('pop');
                }, 1200);
            } else {
                bell.classList.remove('notify-bell-active');
                badge.classList.remove('pop');
            }
        }

        // Watch for changes to notificationCount text (so dashboard.js can update count and this reacts)
        (function watchNotificationCount(){
            const badge = document.getElementById('notificationCount');
            const bell = document.getElementById('notificationBell');
            if (!badge || !bell) return;

            // initial check on load (after dashboard.js runs)
            window.addEventListener('load', () => {
                const val = Number(badge.textContent) || 0;
                if (val > 0) setBellActive(true);
            });

            // MutationObserver to detect changes made by dashboard.js
            const obs = new MutationObserver(mutations => {
                for (const m of mutations) {
                    if (m.type === 'characterData' || m.type === 'childList') {
                        const val = Number(badge.textContent) || 0;
                        if (val > 0) {
                            setBellActive(true);
                        }
                    }
                }
            });

            // observe text changes inside the badge
            obs.observe(badge, { characterData: true, childList: true, subtree: true });
        })();

        // Make bell clickable: open notifications panel (placeholder)
        document.addEventListener('DOMContentLoaded', () => {
            const bell = document.getElementById('notificationBell');
            if (bell) {
                bell.addEventListener('click', () => {
                    alert('Fitur notifikasi: belum diimplementasikan. Nanti akan membuka daftar notifikasi.');
                });
            }
        });
    </script>

</body>
</html>