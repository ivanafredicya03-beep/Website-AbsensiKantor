
<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Database connection (adjust as needed)
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
?>
<!doctype html>
<html lang="id">
 <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Absensi - Creed Creative</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
        body {
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            padding: 0;
            background: #f1f5f9;
            min-height: 100%;
            color: #333;
        }
        
        html {
            height: 100%;
        }
        
        .app-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            background: linear-gradient(135deg, #242761, #1e293b);
            width: 256px;
            flex-shrink: 0;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
        }
        
        /* ============ ANIMATIONS ============ */
        .pulse-animation {
            animation: logoPulse 3s ease-in-out infinite;
        }

        @keyframes logoPulse {
            0%, 100% { transform: scale(1); box-shadow: 0 4px 15px rgba(255, 255, 255, 0.3); }
            50% { transform: scale(1.05); box-shadow: 0 8px 25px rgba(255, 255, 255, 0.5); }
        }

        .logo-container {
            transition: all 0.3s ease;
        }

        .logo-container:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 30px rgba(255, 255, 255, 0.4);
        }

        /* Subtle micro-animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(15px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.4s ease-out;
        }

        .animate-slideInLeft {
            animation: slideInLeft 0.3s ease-out;
        }
        
        .menu-item {
            border-radius: 12px;
            transition: all 0.2s ease;
        }
        
        .menu-item:hover {
            background: rgba(255,255,255,0.1);
        }
        
        .menu-item.active {
            background: linear-gradient(135deg, #f97316, #ea580c);
            box-shadow: 0 4px 15px rgba(249, 115, 22, 0.3);
        }
        
        .main-content {
            flex: 1;
            overflow-y: auto;
        }
        
        .brand-primary {
            color: #242761;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        .filter-section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 32px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        
        .filter-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
        }
        
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        .filter-label {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .filter-input {
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }
        
        .filter-input:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }
        
        .filter-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }
        
        .btn-filter {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-filter:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.3);
        }
        
        .btn-secondary {
            background: #242761;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-secondary:hover {
            background: #1e293b;
            transform: translateY(-1px);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: 800;
            color: #242761;
            margin-bottom: 8px;
        }
        
        .stat-label {
            font-size: 14px;
            color: #64748b;
            font-weight: 500;
        }
        
        .table-section {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        
        .table-header {
            background: #f8fafc;
            padding: 20px 24px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .table-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
        }
        
        .table-container {
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background: #f8fafc;
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }
        
        .data-table td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }
        
        .data-table tr:hover {
            background: #f8fafc;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-hadir {
            background: #dcfce7;
            color: #166534;
        }
        
        .status-terlambat {
            background: #fef3c7;
            color: #92400e;
        }
        
        .status-izin {
            background: #dbeafe;
            color: #1e40af;
        }
        
        .status-sakit {
            background: #fce7f3;
            color: #be185d;
        }
        
        .status-alpa {
            background: #fef2f2;
            color: #dc2626;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #64748b;
            font-style: italic;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
            color: #64748b;
        }
        
        .footer-info {
            text-align: center;
            padding: 20px;
            color: #94a3b8;
            font-size: 12px;
            background: #f8fafc;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .filter-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-buttons {
                flex-direction: column;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>

  <script src="/_sdk/data_sdk.js" type="text/javascript"></script>
  <script src="/_sdk/element_sdk.js" type="text/javascript"></script>
 </head>
 <body>
  <div class="app-layout"><!-- Sidebar -->
   <div class="sidebar w-64 p-6 flex flex-col"><!-- Logo -->
    <div class="text-center mb-8">
     <div class="logo-container pulse-animation w-16 h-16 bg-white rounded-xl mx-auto mb-3 flex items-center justify-center border-4 border-white shadow-lg"><img src="../assets/images/logo.png.webp" alt="Logo" class="w-full h-full object-contain" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"> <i class="fas fa-qrcode text-3xl text-gray-700" style="display: none;"></i>
     </div>
     <h2 class="text-white font-bold text-lg">QR Absensi</h2>
     <p class="text-gray-300 text-sm">CreedCreatives</p>
    </div><!-- Menu -->
    <nav class="flex-1">
     <div class="menu-item p-3 mb-2"><a href="dashboard.php" class="flex items-center text-gray-300 hover:text-white"> <i class="fas fa-home mr-3"></i> <span>Dashboard</span> </a>
     </div>
     <div class="menu-item p-3 mb-2"><a href="karyawan.php" class="flex items-center text-gray-300 hover:text-white"> <i class="fas fa-users mr-3"></i> <span>Karyawan</span> </a>
     </div>
     <div class="menu-item p-3 mb-2"><a href="absensi.php" class="flex items-center text-gray-300 hover:text-white"> <i class="fas fa-calendar-check mr-3"></i> <span>Absensi</span> </a>
     </div>
     <div class="menu-item active p-3 mb-2"><a href="laporan.php" class="flex items-center text-white"> <i class="fas fa-chart-bar mr-3"></i> <span>Laporan</span> </a>
     </div>
     <div class="menu-item p-3 mb-2"><a href="pengaturan.php" class="flex items-center text-gray-300 hover:text-white"> <i class="fas fa-cog mr-3"></i> <span>Pengaturan</span> </a>
     </div>
    </nav><!-- Logout -->
    <div class="menu-item p-3 mt-4"><a href="#" onclick="showLogoutConfirmation()" class="flex items-center text-gray-300 hover:text-white"> <i class="fas fa-sign-out-alt mr-3"></i> <span>Logout</span> </a>
    </div>
   </div><!-- Main Content -->
   <div class="flex-1 overflow-auto"><!-- Header -->
    <div class="bg-white shadow-sm border-b p-6">
     <div class="flex justify-between items-center">
      <div>
       <h1 class="text-2xl font-bold brand-primary">Laporan Absensi</h1>
       <p class="text-gray-600">Monitoring dan analisis data kehadiran karyawan</p>
      </div>
      <div class="flex items-center space-x-4">
       <div class="relative"><i class="fas fa-bell text-gray-400 text-xl cursor-pointer hover:text-orange-500 transition-colors duration-200"></i>
        <div class="notification-badge">
         3
        </div>
       </div>
       <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-gradient-to-r from-blue-900 to-orange-500 rounded-full flex items-center justify-center"><span class="text-white font-bold text-sm"> <!--?php 
                                    if (isset($_SESSION['nama_admin'])) {
                                        $nama = $_SESSION['nama_admin'];
                                        $words = explode(' ', $nama);
                                        if (count($words) -->= 2) { echo strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1)); } else { echo strtoupper(substr($nama, 0, 2)); } } else { echo 'AD'; } ?&gt; </span>
        </div>
        <div>
         <p class="font-semibold brand-primary"><!--?php echo isset($_SESSION['nama_admin']) ? $_SESSION['nama_admin'] : 'Admin'; ?--></p>
         <p class="text-sm text-gray-500"><!--?php echo isset($_SESSION['role']) ? ucfirst($_SESSION['role']) : 'Administrator'; ?--></p>
        </div>
       </div>
      </div>
     </div>
    </div>
    <div class="p-6"><!-- Filter Section -->
     <div class="filter-section animate-fadeInUp">
      <div class="filter-title"><i class="fas fa-filter mr-2"></i> Filter Laporan
      </div>
      <div class="filter-grid">
       <div class="filter-group"><label class="filter-label">Tanggal Mulai</label> <input type="date" class="filter-input" id="startDate">
       </div>
       <div class="filter-group"><label class="filter-label">Tanggal Akhir</label> <input type="date" class="filter-input" id="endDate">
       </div>
       <div class="filter-group"><label class="filter-label">Karyawan</label> <select class="filter-input" id="employeeFilter"> <option value="">Semua Karyawan</option> </select>
       </div>
       <div class="filter-group"><label class="filter-label">Status</label> <select class="filter-input" id="statusFilter"> <option value="">Semua Status</option> <option value="hadir">Hadir</option> <option value="izin">Izin</option> <option value="sakit">Sakit</option> <option value="alpa">Alpa</option> </select>
       </div>
      </div>
      <div class="filter-buttons"><button class="btn-filter" onclick="loadReport()"> <i class="fas fa-search mr-2"></i> Tampilkan Laporan </button> <button class="btn-secondary" onclick="setToday()"> <i class="fas fa-calendar-day mr-2"></i> Hari Ini </button> <button class="btn-secondary" onclick="setThisWeek()"> <i class="fas fa-calendar-week mr-2"></i> Minggu Ini </button> <button class="btn-secondary" onclick="setThisMonth()"> <i class="fas fa-calendar mr-2"></i> Bulan Ini </button> <button class="btn-secondary" onclick="exportReport()"> <i class="fas fa-download mr-2"></i> Export Excel </button>
      </div>
     </div><!-- Statistics -->
     <div class="stats-grid" id="statsGrid">
      <div class="stat-card animate-slideInLeft" style="animation-delay: 0.1s;">
       <div class="stat-number" id="totalHadir">
        0
       </div>
       <div class="stat-label"><i class="fas fa-check-circle text-green-500 mr-1"></i> Total Hadir
       </div>
      </div>
      <div class="stat-card animate-slideInLeft" style="animation-delay: 0.2s;">
       <div class="stat-number" id="totalIzin">
        0
       </div>
       <div class="stat-label"><i class="fas fa-calendar-alt text-blue-500 mr-1"></i> Izin
       </div>
      </div>
      <div class="stat-card animate-slideInLeft" style="animation-delay: 0.3s;">
       <div class="stat-number" id="totalSakit">
        0
       </div>
       <div class="stat-label"><i class="fas fa-thermometer-half text-pink-500 mr-1"></i> Sakit
       </div>
      </div>
      <div class="stat-card animate-slideInLeft" style="animation-delay: 0.4s;">
       <div class="stat-number" id="totalAlpa">
        0
       </div>
       <div class="stat-label"><i class="fas fa-times-circle text-red-500 mr-1"></i> Alpa
       </div>
      </div>
     </div><!-- Table Section -->
     <div class="table-section animate-fadeInUp" style="animation-delay: 0.5s;">
      <div class="table-header">
       <div class="table-title"><i class="fas fa-table mr-2"></i> Data Absensi
       </div>
      </div>
      <div class="table-container">
       <table class="data-table">
        <thead>
         <tr>
          <th>Tanggal</th>
          <th>ID Karyawan</th>
          <th>Nama</th>
          <th>Jabatan</th>
          <th>Jam Masuk</th>
          <th>Jam Pulang</th>
          <th>Total Jam</th>
          <th>Status</th>
         </tr>
        </thead>
        <tbody id="reportTableBody">
         <tr>
          <td colspan="8" class="loading">Memuat data...</td>
         </tr>
        </tbody>
       </table>
      </div>
     </div>
    </div>
   </div>
  </div><!-- Logout Confirmation Modal -->
  <div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
   <div class="bg-white rounded-lg p-6 max-w-sm mx-4">
    <div class="text-center"><i class="fas fa-sign-out-alt text-4xl text-red-500 mb-4"></i>
     <h3 class="text-lg font-semibold text-gray-900 mb-2">Konfirmasi Logout</h3>
     <p class="text-gray-600 mb-6">Apakah Anda yakin ingin keluar dari sistem?</p>
     <div class="flex space-x-3"><button onclick="hideLogoutConfirmation()" class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400"> Batal </button> <button onclick="logout()" class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"> Logout </button>
     </div>
    </div>
   </div>
  </div>
  <script>
        function showLogoutConfirmation() {
            document.getElementById('logoutModal').classList.remove('hidden');
            document.getElementById('logoutModal').classList.add('flex');
        }

        function hideLogoutConfirmation() {
            document.getElementById('logoutModal').classList.add('hidden');
            document.getElementById('logoutModal').classList.remove('flex');
        }

        function logout() {
            window.location.href = 'login.php';
        }

        // Load employees for filter
        async function loadEmployees() {
            try {
                const response = await fetch('../config/get_employees.php');
                const result = await response.json();
                
                const select = document.getElementById('employeeFilter');
                select.innerHTML = '<option value="">Semua Karyawan</option>';
                
                if (result.success && result.data) {
                    result.data.forEach(emp => {
                        const option = document.createElement('option');
                        option.value = emp.id_karyawan;
                        option.textContent = `${emp.id_karyawan} - ${emp.nama}`;
                        select.appendChild(option);
                    });
                }
                
            } catch (error) {
                console.error('Error loading employees:', error);
                const select = document.getElementById('employeeFilter');
                select.innerHTML = '<option value="">Semua Karyawan</option>';
            }
        }

        // Set default dates
        function setDefaultDates() {
            const today = new Date();
            const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            
            document.getElementById('startDate').value = startOfMonth.toISOString().split('T')[0];
            document.getElementById('endDate').value = today.toISOString().split('T')[0];
        }
        
        // Set filter to today
        function setToday() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('startDate').value = today;
            document.getElementById('endDate').value = today;
            loadReport();
        }
        
        // Set filter to this week
        function setThisWeek() {
            const today = new Date();
            const startOfWeek = new Date(today.setDate(today.getDate() - today.getDay()));
            const endOfWeek = new Date(today.setDate(today.getDate() - today.getDay() + 6));
            
            document.getElementById('startDate').value = startOfWeek.toISOString().split('T')[0];
            document.getElementById('endDate').value = endOfWeek.toISOString().split('T')[0];
            loadReport();
        }
        
        // Set filter to this month
        function setThisMonth() {
            const today = new Date();
            const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            
            document.getElementById('startDate').value = startOfMonth.toISOString().split('T')[0];
            document.getElementById('endDate').value = endOfMonth.toISOString().split('T')[0];
            loadReport();
        }

        // Load report data
        async function loadReport() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const employeeId = document.getElementById('employeeFilter').value;
            const status = document.getElementById('statusFilter').value;
            
            if (!startDate || !endDate) {
                alert('Pilih tanggal mulai dan akhir terlebih dahulu');
                return;
            }
            
            try {
                document.getElementById('reportTableBody').innerHTML = '<tr><td colspan="8" class="loading">Memuat data...</td></tr>';
                
                const params = new URLSearchParams({
                    start_date: startDate,
                    end_date: endDate,
                    employee_id: employeeId,
                    status: status
                });
                
                const response = await fetch(`../config/get_report.php?${params}`);
                const data = await response.json();
                
                if (data.success) {
                    displayReport(data.data);
                    updateStats(data.stats);
                } else {
                    document.getElementById('reportTableBody').innerHTML = '<tr><td colspan="8" class="no-data">Gagal memuat data dari server</td></tr>';
                    updateStats({hadir: 0, izin: 0, sakit: 0, alpa: 0});
                }
                
            } catch (error) {
                console.error('Error loading report:', error);
                document.getElementById('reportTableBody').innerHTML = '<tr><td colspan="8" class="no-data">Terjadi kesalahan saat memuat data</td></tr>';
            }
        }

        function updateStats(stats) {
            document.getElementById('totalHadir').textContent = stats.hadir || 0;
            document.getElementById('totalIzin').textContent = stats.izin || 0;
            document.getElementById('totalSakit').textContent = stats.sakit || 0;
            document.getElementById('totalAlpa').textContent = stats.alpa || 0;
        }

        function displayReport(data) {
            const tbody = document.getElementById('reportTableBody');
            tbody.innerHTML = '';

            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="no-data">Tidak ada data yang ditemukan</td></tr>';
                return;
            }

            data.forEach((item, index) => {
                const statusClass = {
                    'hadir': 'status-hadir',
                    'terlambat': 'status-terlambat',
                    'izin': 'status-izin',
                    'sakit': 'status-sakit',
                    'alpa': 'status-alpa'
                };

                const statusText = {
                    'hadir': 'Hadir',
                    'terlambat': 'Terlambat',
                    'izin': 'Izin',
                    'sakit': 'Sakit',
                    'alpa': 'Alpa'
                };

                // Format total jam to show only hours and minutes
                let totalJamFormatted = '-';
                if (item.total_jam && item.total_jam !== '-') {
                    // If total_jam is in decimal format (e.g., 8.5), convert to hours and minutes
                    if (!isNaN(item.total_jam)) {
                        const totalHours = parseFloat(item.total_jam);
                        const hours = Math.floor(totalHours);
                        const minutes = Math.round((totalHours - hours) * 60);
                        totalJamFormatted = `${hours} jam ${minutes} menit`;
                    } else {
                        // If already formatted, use as is
                        totalJamFormatted = item.total_jam;
                    }
                }

                const row = `
                    <tr>
                        <td>${item.tanggal || '-'}</td>
                        <td>${item.id_karyawan || '-'}</td>
                        <td>${item.nama || '-'}</td>
                        <td>${item.jabatan || '-'}</td>
                        <td>${item.jam_masuk || '-'}</td>
                        <td>${item.jam_keluar || '-'}</td>
                        <td>${totalJamFormatted}</td>
                        <td>
                            <span class="status-badge ${statusClass[item.status] || 'status-alpa'}">
                                ${statusText[item.status] || 'Tidak Diketahui'}
                            </span>
                        </td>
                    </tr>
                `;
                tbody.innerHTML += row;
            });
        }

        async function exportReport() {
            const startDate = document.getElementById('startDate').value;
            const endDate = document.getElementById('endDate').value;
            const employeeId = document.getElementById('employeeFilter').value;
            const status = document.getElementById('statusFilter').value;
            
            if (!startDate || !endDate) {
                alert('Pilih tanggal mulai dan akhir terlebih dahulu');
                return;
            }
            
            try {
                const params = new URLSearchParams({
                    start_date: startDate,
                    end_date: endDate,
                    employee_id: employeeId,
                    status: status,
                    export: 'csv'
                });
                
                const response = await fetch(`../config/export_report.php?${params}`);
                
                if (response.ok) {
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const link = document.createElement('a');
                    link.href = url;
                    link.download = `laporan_absensi_${startDate}_${endDate}.csv`;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    window.URL.revokeObjectURL(url);
                } else {
                    alert('Gagal mengekspor data');
                }
                
            } catch (error) {
                console.error('Error exporting report:', error);
                alert('Terjadi kesalahan saat mengekspor data');
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Load employees for filter dropdown
            loadEmployees();
            
            // Set default dates
            setDefaultDates();
            
            // Load initial report
            loadReport();
        });
    </script>
 <script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9950a10161c70557',t:'MTc2MTU1MTcwMy4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>