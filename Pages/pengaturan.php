<!doctype html>
<html lang="id">
 <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pengaturan Sistem - Creed Creative</title>
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
        
        .settings-section {
            background: white;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
            border: 1px solid #e2e8f0;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .section-title i {
            margin-right: 12px;
            color: #f97316;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-label {
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .form-input {
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }
        
        .form-textarea {
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            resize: vertical;
            min-height: 100px;
            transition: border-color 0.2s ease;
        }
        
        .form-textarea:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }
        
        .form-select {
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            transition: border-color 0.2s ease;
        }
        
        .form-select:focus {
            outline: none;
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }
        
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 24px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 24px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 18px;
            width: 18px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: #f97316;
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #f97316, #ea580c);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.3);
        }
        
        .btn-secondary {
            background: #242761;
            color: white;
            border: none;
            padding: 12px 24px;
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
        
        .btn-danger {
            background: #dc2626;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .btn-danger:hover {
            background: #b91c1c;
            transform: translateY(-1px);
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
            justify-content: flex-end;
            margin-top: 24px;
            flex-wrap: wrap;
        }
        
        .info-box {
            background: #f0f9ff;
            border: 1px solid #0ea5e9;
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
        }
        
        .info-box p {
            margin: 0;
            color: #0c4a6e;
            font-size: 14px;
        }
        
        .warning-box {
            background: #fefce8;
            border: 1px solid #eab308;
            border-radius: 8px;
            padding: 16px;
            margin-top: 16px;
        }
        
        .warning-box p {
            margin: 0;
            color: #713f12;
            font-size: 14px;
        }
        
        .success-message {
            background: #f0fdf4;
            border: 1px solid #22c55e;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
            color: #15803d;
            display: none;
        }
        
        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }
        
        .admin-table th,
        .admin-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .admin-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #374151;
        }
        
        .admin-table tr:hover {
            background: #f8fafc;
        }
        
        .status-active {
            background: #dcfce7;
            color: #166534;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-inactive {
            background: #fef2f2;
            color: #dc2626;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                justify-content: stretch;
            }
            
            .action-buttons button {
                flex: 1;
            }
        }
    </style>

  <script src="/_sdk/data_sdk.js" type="text/javascript"></script>
  <script src="/_sdk/element_sdk.js" type="text/javascript"></script>
 </head>
 <body>
  <script>
        // Simulate PHP functionality with JavaScript
        const defaultSettings = {
            nama_perusahaan: 'CreedCreatives',
            email_perusahaan: 'info@creedcreatives.com',
            telepon_perusahaan: '+62 21 1234 5678',
            website_perusahaan: 'https://creedcreatives.com',
            alamat_perusahaan: 'Jl. Sudirman No. 123, Jakarta Pusat, DKI Jakarta 10220',
            jam_masuk: '08:00',
            jam_pulang: '17:00',
            toleransi_terlambat: 15,
            jam_istirahat_mulai: '12:00',
            jam_istirahat_selesai: '13:00',
            hari_kerja: 'senin-jumat',
            email_admin: 'admin@creedcreatives.com',
            waktu_laporan_harian: '18:00',
            notif_email_harian: 1,
            notif_keterlambatan: 1,
            laporan_mingguan: 0,
            min_panjang_password: 8,
            session_timeout: 60,
            max_login_gagal: 5,
            lockout_duration: 15
        };

        const sampleAdmins = [
            {
                username: 'admin',
                nama_admin: 'Administrator',
                role: 'superadmin',
                created_at: '2024-01-15'
            },
            {
                username: 'manager',
                nama_admin: 'Manager System',
                role: 'admin',
                created_at: '2024-01-20'
            }
        ];

        // Load settings on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadSettingsToForm();
            loadAdminTable();
        });

        function loadSettingsToForm() {
            const settings = JSON.parse(localStorage.getItem('systemSettings')) || defaultSettings;
            
            document.querySelector('input[name="companyName"]').value = settings.nama_perusahaan;
            document.querySelector('input[name="companyEmail"]').value = settings.email_perusahaan;
            document.querySelector('input[name="companyPhone"]').value = settings.telepon_perusahaan;
            document.querySelector('input[name="companyWebsite"]').value = settings.website_perusahaan;
            document.querySelector('textarea[name="companyAddress"]').value = settings.alamat_perusahaan;
            document.querySelector('input[name="workStartTime"]').value = settings.jam_masuk;
            document.querySelector('input[name="workEndTime"]').value = settings.jam_pulang;
            document.querySelector('input[name="lateTolerance"]').value = settings.toleransi_terlambat;
            document.querySelector('input[name="breakStartTime"]').value = settings.jam_istirahat_mulai;
            document.querySelector('input[name="breakEndTime"]').value = settings.jam_istirahat_selesai;
            document.querySelector('select[name="workDays"]').value = settings.hari_kerja;
            document.querySelector('input[name="adminEmail"]').value = settings.email_admin;
            document.querySelector('input[name="dailyReportTime"]').value = settings.waktu_laporan_harian;
            document.querySelector('input[name="dailyEmailNotif"]').checked = settings.notif_email_harian;
            document.querySelector('input[name="lateNotification"]').checked = settings.notif_keterlambatan;
            document.querySelector('input[name="weeklyReport"]').checked = settings.laporan_mingguan;
            document.querySelector('input[name="minPasswordLength"]').value = settings.min_panjang_password;
            document.querySelector('input[name="sessionTimeout"]').value = settings.session_timeout;
            document.querySelector('input[name="maxLoginAttempts"]').value = settings.max_login_gagal;
            document.querySelector('input[name="lockoutDuration"]').value = settings.lockout_duration;
        }

        function loadAdminTable() {
            const admins = JSON.parse(localStorage.getItem('systemAdmins')) || sampleAdmins;
            const tbody = document.querySelector('.admin-table tbody');
            
            tbody.innerHTML = '';
            admins.forEach(admin => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${admin.username}</td>
                    <td>${admin.nama_admin || 'N/A'}</td>
                    <td>${admin.role === 'superadmin' ? 'Super Admin' : 'Admin'}</td>
                    <td>${new Date(admin.created_at).toLocaleDateString('id-ID')}</td>
                    <td>
                        <button type="button" class="btn-danger" style="padding: 6px 12px; font-size: 12px;" onclick="resetPassword('${admin.username}')">
                            <i class="fas fa-key"></i>
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }
    </script>
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
     <div class="menu-item p-3 mb-2"><a href="laporan.php" class="flex items-center text-gray-300 hover:text-white"> <i class="fas fa-chart-bar mr-3"></i> <span>Laporan</span> </a>
     </div>
     <div class="menu-item active p-3 mb-2"><a href="pengaturan.php" class="flex items-center text-white"> <i class="fas fa-cog mr-3"></i> <span>Pengaturan</span> </a>
     </div>
    </nav><!-- Logout -->
    <div class="menu-item p-3 mt-4"><a href="#" onclick="showLogoutConfirmation()" class="flex items-center text-gray-300 hover:text-white"> <i class="fas fa-sign-out-alt mr-3"></i> <span>Logout</span> </a>
    </div>
   </div><!-- Main Content -->
   <div class="flex-1 overflow-auto"><!-- Header -->
    <div class="bg-white shadow-sm border-b p-6">
     <div class="flex justify-between items-center">
      <div>
       <h1 class="text-2xl font-bold brand-primary">Pengaturan Sistem</h1>
       <p class="text-gray-600">Konfigurasi dan pengaturan sistem absensi QR</p>
      </div>
      <div class="flex items-center space-x-4">
       <div class="relative"><i class="fas fa-bell text-gray-400 text-xl cursor-pointer hover:text-orange-500 transition-colors duration-200"></i>
        <div class="notification-badge">
         3
        </div>
       </div>
       <div class="flex items-center space-x-3">
        <div class="w-10 h-10 bg-gradient-to-r from-blue-900 to-orange-500 rounded-full flex items-center justify-center"><span class="text-white font-bold text-sm">AD</span>
        </div>
        <div>
         <p class="font-semibold brand-primary">Admin</p>
         <p class="text-sm text-gray-500">Administrator</p>
        </div>
       </div>
      </div>
     </div>
    </div>
    <div class="p-6"><!-- Success/Error Messages -->
     <div id="successMessage" class="success-message"><i class="fas fa-check-circle mr-2"></i> <span id="successText"></span>
     </div>
     <div id="errorMessage" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6 text-red-700" style="display: none;"><i class="fas fa-exclamation-circle mr-2"></i> <span id="errorText"></span>
     </div><!-- Form Settings -->
     <form method="POST" action=""><input type="hidden" name="action" value="save_settings"> <!-- Pengaturan Perusahaan -->
      <div class="settings-section">
       <div class="section-title"><i class="fas fa-building"></i> Informasi Perusahaan
       </div>
       <div class="form-grid">
        <div class="form-group"><label class="form-label">Nama Perusahaan</label> <input type="text" class="form-input" name="companyName" placeholder="Masukkan nama perusahaan" required>
        </div>
        <div class="form-group"><label class="form-label">Email Perusahaan</label> <input type="email" class="form-input" name="companyEmail" placeholder="email@perusahaan.com" required>
        </div>
        <div class="form-group"><label class="form-label">Nomor Telepon</label> <input type="tel" class="form-input" name="companyPhone" placeholder="+62 xxx xxxx xxxx">
        </div>
        <div class="form-group"><label class="form-label">Website</label> <input type="url" class="form-input" name="companyWebsite" placeholder="https://website.com">
        </div>
       </div>
       <div class="form-group" style="margin-top: 20px;"><label class="form-label">Alamat Perusahaan</label> <textarea class="form-textarea" name="companyAddress" placeholder="Masukkan alamat lengkap perusahaan"></textarea>
       </div>
      </div><!-- Pengaturan Jam Kerja -->
      <div class="settings-section">
       <div class="section-title"><i class="fas fa-clock"></i> Pengaturan Jam Kerja
       </div>
       <div class="form-grid">
        <div class="form-group"><label class="form-label">Jam Masuk</label> <input type="time" class="form-input" name="workStartTime" required>
        </div>
        <div class="form-group"><label class="form-label">Jam Pulang</label> <input type="time" class="form-input" name="workEndTime" required>
        </div>
        <div class="form-group"><label class="form-label">Toleransi Keterlambatan (menit)</label> <input type="number" class="form-input" name="lateTolerance" min="0" max="60" required>
        </div>
        <div class="form-group"><label class="form-label">Jam Istirahat Mulai</label> <input type="time" class="form-input" name="breakStartTime">
        </div>
        <div class="form-group"><label class="form-label">Jam Istirahat Selesai</label> <input type="time" class="form-input" name="breakEndTime">
        </div>
        <div class="form-group"><label class="form-label">Hari Kerja</label> <select class="form-select" name="workDays" required> <option value="senin-jumat">Senin - Jumat</option> <option value="senin-sabtu">Senin - Sabtu</option> <option value="custom">Custom</option> </select>
        </div>
       </div>
       <div class="info-box">
        <p><i class="fas fa-info-circle mr-2"></i>Pengaturan jam kerja akan berlaku untuk semua karyawan. Pastikan waktu sudah sesuai dengan kebijakan perusahaan.</p>
       </div>
      </div><!-- Pengaturan Notifikasi -->
      <div class="settings-section">
       <div class="section-title"><i class="fas fa-bell"></i> Pengaturan Notifikasi
       </div>
       <div class="form-grid">
        <div class="form-group"><label class="form-label">Email Admin</label> <input type="email" class="form-input" name="adminEmail" placeholder="admin@perusahaan.com" required>
        </div>
        <div class="form-group"><label class="form-label">Waktu Laporan Harian</label> <input type="time" class="form-input" name="dailyReportTime" required>
        </div>
       </div>
       <div class="form-grid" style="margin-top: 20px;">
        <div class="form-group"><label class="form-label">Notifikasi Email Harian</label> <label class="toggle-switch"> <input type="checkbox" name="dailyEmailNotif"> <span class="toggle-slider"></span> </label>
        </div>
        <div class="form-group"><label class="form-label">Notifikasi Keterlambatan</label> <label class="toggle-switch"> <input type="checkbox" name="lateNotification"> <span class="toggle-slider"></span> </label>
        </div>
        <div class="form-group"><label class="form-label">Laporan Mingguan</label> <label class="toggle-switch"> <input type="checkbox" name="weeklyReport"> <span class="toggle-slider"></span> </label>
        </div>
       </div>
      </div><!-- Pengaturan Keamanan -->
      <div class="settings-section">
       <div class="section-title"><i class="fas fa-shield-alt"></i> Keamanan Sistem
       </div>
       <div class="form-grid">
        <div class="form-group"><label class="form-label">Minimal Panjang Password</label> <input type="number" class="form-input" name="minPasswordLength" min="6" max="20" required>
        </div>
        <div class="form-group"><label class="form-label">Session Timeout (menit)</label> <input type="number" class="form-input" name="sessionTimeout" min="15" max="480" required>
        </div>
        <div class="form-group"><label class="form-label">Maksimal Login Gagal</label> <input type="number" class="form-input" name="maxLoginAttempts" min="3" max="10" required>
        </div>
        <div class="form-group"><label class="form-label">Lockout Duration (menit)</label> <input type="number" class="form-input" name="lockoutDuration" min="5" max="60" required>
        </div>
       </div>
      </div><!-- Action Buttons -->
      <div class="action-buttons"><button type="button" class="btn-secondary" onclick="resetToDefault()"> <i class="fas fa-undo mr-2"></i> Reset Default </button> <button type="submit" class="btn-primary"> <i class="fas fa-save mr-2"></i> Simpan Pengaturan </button>
      </div>
     </form><!-- Manajemen Admin -->
     <div class="settings-section">
      <div class="section-title"><i class="fas fa-users-cog"></i> Manajemen Admin
      </div>
      <form method="POST" action=""><input type="hidden" name="action" value="add_admin">
       <div class="form-grid">
        <div class="form-group"><label class="form-label">Username Admin Baru</label> <input type="text" class="form-input" name="username" placeholder="username_admin" required>
        </div>
        <div class="form-group"><label class="form-label">Password Admin Baru</label> <input type="password" class="form-input" name="password" placeholder="Password minimal 8 karakter" required>
        </div>
        <div class="form-group"><label class="form-label">Nama Lengkap</label> <input type="text" class="form-input" name="name" placeholder="Nama Lengkap Admin" required>
        </div>
        <div class="form-group"><label class="form-label">Level Admin</label> <select class="form-select" name="level" required> <option value="admin">Admin</option> <option value="super_admin">Super Admin</option> </select>
        </div>
       </div>
       <div class="action-buttons" style="margin-top: 16px; justify-content: flex-start;"><button type="submit" class="btn-secondary"> <i class="fas fa-plus mr-2"></i> Tambah Admin </button>
       </div>
      </form><!-- Daftar Admin -->
      <table class="admin-table">
       <thead>
        <tr>
         <th>Username</th>
         <th>Nama Lengkap</th>
         <th>Level</th>
         <th>Tanggal Dibuat</th>
         <th>Aksi</th>
        </tr>
       </thead>
       <tbody><!-- Admin rows will be populated by JavaScript -->
       </tbody>
      </table>
     </div><!-- Backup Database -->
     <div class="settings-section">
      <div class="section-title"><i class="fas fa-database"></i> Database Management
      </div>
      <form method="POST" action=""><input type="hidden" name="action" value="backup_database">
       <div class="action-buttons" style="justify-content: flex-start;"><button type="submit" class="btn-danger"> <i class="fas fa-database mr-2"></i> Backup Database </button>
       </div>
      </form>
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

        function resetToDefault() {
            if (confirm('Apakah Anda yakin ingin mengembalikan semua pengaturan ke default?')) {
                localStorage.removeItem('systemSettings');
                loadSettingsToForm();
                showMessage('Pengaturan berhasil direset ke default!', 'success');
            }
        }

        function resetPassword(username) {
            if (confirm(`Reset password untuk ${username}?`)) {
                const newPassword = Math.random().toString(36).slice(-8);
                showMessage(`Password admin ${username} berhasil direset! Password baru: ${newPassword}`, 'success');
            }
        }

        function showMessage(message, type) {
            const successDiv = document.getElementById('successMessage');
            const errorDiv = document.getElementById('errorMessage');
            
            if (type === 'success') {
                document.getElementById('successText').textContent = message;
                successDiv.style.display = 'block';
                errorDiv.style.display = 'none';
                setTimeout(() => {
                    successDiv.style.display = 'none';
                }, 5000);
            } else {
                document.getElementById('errorText').textContent = message;
                errorDiv.style.display = 'block';
                successDiv.style.display = 'none';
                setTimeout(() => {
                    errorDiv.style.display = 'none';
                }, 5000);
            }
        }

        // Handle form submissions
        document.addEventListener('DOMContentLoaded', function() {
            // Settings form - find by button text instead
            const saveButton = document.querySelector('button[type="submit"]:has(i.fa-save)') || 
                              Array.from(document.querySelectorAll('button[type="submit"]')).find(btn => 
                                  btn.textContent.includes('Simpan Pengaturan'));
            
            if (saveButton) {
                saveButton.closest('form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const settings = {
                        nama_perusahaan: formData.get('companyName'),
                        email_perusahaan: formData.get('companyEmail'),
                        telepon_perusahaan: formData.get('companyPhone'),
                        website_perusahaan: formData.get('companyWebsite'),
                        alamat_perusahaan: formData.get('companyAddress'),
                        jam_masuk: formData.get('workStartTime'),
                        jam_pulang: formData.get('workEndTime'),
                        toleransi_terlambat: parseInt(formData.get('lateTolerance')),
                        jam_istirahat_mulai: formData.get('breakStartTime'),
                        jam_istirahat_selesai: formData.get('breakEndTime'),
                        hari_kerja: formData.get('workDays'),
                        email_admin: formData.get('adminEmail'),
                        waktu_laporan_harian: formData.get('dailyReportTime'),
                        notif_email_harian: formData.get('dailyEmailNotif') ? 1 : 0,
                        notif_keterlambatan: formData.get('lateNotification') ? 1 : 0,
                        laporan_mingguan: formData.get('weeklyReport') ? 1 : 0,
                        min_panjang_password: parseInt(formData.get('minPasswordLength')),
                        session_timeout: parseInt(formData.get('sessionTimeout')),
                        max_login_gagal: parseInt(formData.get('maxLoginAttempts')),
                        lockout_duration: parseInt(formData.get('lockoutDuration'))
                    };
                    
                    localStorage.setItem('systemSettings', JSON.stringify(settings));
                    showMessage('Pengaturan berhasil disimpan!', 'success');
                });
            }

            // Add admin form - find by button text
            const addAdminButton = Array.from(document.querySelectorAll('button[type="submit"]')).find(btn => 
                btn.textContent.includes('Tambah Admin'));
            
            if (addAdminButton) {
                addAdminButton.closest('form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const username = formData.get('username');
                    const password = formData.get('password');
                    const name = formData.get('name');
                    const level = formData.get('level');
                    
                    // Validation
                    if (!username || !password || !name || !level) {
                        showMessage('Semua field harus diisi!', 'error');
                        return;
                    }
                    
                    if (password.length < 8) {
                        showMessage('Password minimal 8 karakter!', 'error');
                        return;
                    }
                    
                    const admins = JSON.parse(localStorage.getItem('systemAdmins')) || sampleAdmins;
                    
                    // Check if username exists
                    if (admins.find(admin => admin.username === username)) {
                        showMessage('Username sudah digunakan!', 'error');
                        return;
                    }
                    
                    const newAdmin = {
                        username: username,
                        nama_admin: name,
                        role: level === 'super_admin' ? 'superadmin' : 'admin',
                        created_at: new Date().toISOString().split('T')[0]
                    };
                    
                    admins.push(newAdmin);
                    localStorage.setItem('systemAdmins', JSON.stringify(admins));
                    loadAdminTable();
                    this.reset();
                    showMessage(`Admin ${username} berhasil ditambahkan!`, 'success');
                });
            }

            // Backup form - find by button text
            const backupButton = Array.from(document.querySelectorAll('button[type="submit"]')).find(btn => 
                btn.textContent.includes('Backup Database'));
            
            if (backupButton) {
                backupButton.closest('form').addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    // Show loading state
                    const originalText = backupButton.innerHTML;
                    backupButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
                    backupButton.disabled = true;
                    
                    // Simulate backup process
                    setTimeout(() => {
                        backupButton.innerHTML = originalText;
                        backupButton.disabled = false;
                        showMessage('Backup database berhasil dibuat! File tersimpan di server.', 'success');
                    }, 2000);
                });
            }
        });
    </script>
 <script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9952973cd5bac61e',t:'MTc2MTU3MjI3NS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>