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

// Create uploads directory if it doesn't exist
$upload_dir = '../uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add') {
            $nama = $_POST['nama'];
            $tanggal_lahir = $_POST['tanggal_lahir'];
            $jabatan = $_POST['jabatan'];
            $nomor_hp = $_POST['nomor_hp'];
            $lama_pengabdian = $_POST['lama_pengabdian'];
            $status = $_POST['status'];
            $kode_qr = $_POST['link_qr'];
            $foto = null;
            
            // Handle file upload
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 2 * 1024 * 1024; // 2MB
                
                if (in_array($_FILES['foto']['type'], $allowed_types) && $_FILES['foto']['size'] <= $max_size) {
                    $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                    $foto = 'emp_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
                    $upload_path = $upload_dir . $foto;
                    
                    if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                        // File uploaded successfully
                    } else {
                        $error_message = "Gagal mengupload foto!";
                        $foto = null;
                    }
                } else {
                    $error_message = "File tidak valid! Gunakan JPG/PNG/GIF maksimal 2MB.";
                }
            }
            
            $stmt = $pdo->prepare("INSERT INTO karyawan (nama, tanggal_lahir, jabatan, nomor_hp, lama_pengabdian, kode_qr, status, foto, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$nama, $tanggal_lahir, $jabatan, $nomor_hp, $lama_pengabdian, $kode_qr, $status, $foto]);
            
            $success_message = "Karyawan berhasil ditambahkan!";
        } elseif ($_POST['action'] === 'edit') {
            $id = $_POST['id_karyawan'];
            $nama = $_POST['nama'];
            $tanggal_lahir = $_POST['tanggal_lahir'];
            $jabatan = $_POST['jabatan'];
            $nomor_hp = $_POST['nomor_hp'];
            $lama_pengabdian = $_POST['lama_pengabdian'];
            $status = $_POST['status'];
            $kode_qr = $_POST['link_qr'];
            
            // Get current employee data
            $stmt = $pdo->prepare("SELECT foto FROM karyawan WHERE id_karyawan = ?");
            $stmt->execute([$id]);
            $current_employee = $stmt->fetch();
            $foto = $current_employee['foto'];
            
            // Handle file upload for edit
            if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 2 * 1024 * 1024; // 2MB
                
                if (in_array($_FILES['foto']['type'], $allowed_types) && $_FILES['foto']['size'] <= $max_size) {
                    // Delete old photo if exists
                    if ($foto && file_exists($upload_dir . $foto)) {
                        unlink($upload_dir . $foto);
                    }
                    
                    $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                    $foto = 'emp_' . time() . '_' . rand(1000, 9999) . '.' . $file_extension;
                    $upload_path = $upload_dir . $foto;
                    
                    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                        $error_message = "Gagal mengupload foto!";
                        $foto = $current_employee['foto']; // Keep old photo
                    }
                } else {
                    $error_message = "File tidak valid! Gunakan JPG/PNG/GIF maksimal 2MB.";
                }
            }
            
            $stmt = $pdo->prepare("UPDATE karyawan SET nama = ?, tanggal_lahir = ?, jabatan = ?, nomor_hp = ?, lama_pengabdian = ?, kode_qr = ?, status = ?, foto = ? WHERE id_karyawan = ?");
            $stmt->execute([$nama, $tanggal_lahir, $jabatan, $nomor_hp, $lama_pengabdian, $kode_qr, $status, $foto, $id]);
            
            $success_message = "Data karyawan berhasil diupdate!";
        } elseif ($_POST['action'] === 'delete') {
            $id = $_POST['id_karyawan'];
            
            // Get employee data to delete photo file
            $stmt = $pdo->prepare("SELECT foto FROM karyawan WHERE id_karyawan = ?");
            $stmt->execute([$id]);
            $employee = $stmt->fetch();
            
            // Delete photo file if exists
            if ($employee && $employee['foto'] && file_exists($upload_dir . $employee['foto'])) {
                unlink($upload_dir . $employee['foto']);
            }
            
            $stmt = $pdo->prepare("DELETE FROM karyawan WHERE id_karyawan = ?");
            $stmt->execute([$id]);
            
            $success_message = "Karyawan berhasil dihapus!";
        }
    }
}

// Get all employees
$stmt = $pdo->query("SELECT * FROM karyawan ORDER BY created_at DESC");
$employees = $stmt->fetchAll();
?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Karyawan - Sistem Absensi QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* ============ GLOBAL ============ */
* {
  box-sizing: border-box;
}

body {
  font-family: 'Poppins', sans-serif;
  background: #f5f6fa;
  color: #333;
  margin: 0;
}

/* BRAND COLORS */
.brand-gradient {
  background: linear-gradient(135deg, #222660 0%, #F36E2B 100%);
}

.brand-primary {
  color: #222660;
}

.brand-secondary {
  color: #F36E2B;
}

/* ============ SIDEBAR ============ */
.sidebar {
  background: linear-gradient(180deg, #222660 0%, #1a1d4a 100%);
  box-shadow: 4px 0 20px rgba(34, 38, 96, 0.3);
}

.menu-item {
  transition: all 0.3s ease;
  border-radius: 12px;
  margin: 4px 0;
  color: #fff;
  display: block;
  padding: 10px 16px;
}

.menu-item:hover {
  background: rgba(243, 110, 43, 0.2);
  transform: translateX(8px);
}

.menu-item.active {
  background: linear-gradient(135deg, #F36E2B 0%, #e55a1f 100%);
  box-shadow: 0 4px 15px rgba(243, 110, 43, 0.4);
}

/* ============ CARD ============ */
.card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
  border: 1px solid rgba(243, 110, 43, 0.1);
  transition: all 0.3s ease;
}

.card:hover {
  transform: translateY(-4px);
  box-shadow: 0 14px 35px rgba(0, 0, 0, 0.12);
}

/* ============ BUTTONS ============ */
.btn-primary, .btn-secondary, .btn-danger, .btn-success {
  border: none;
  border-radius: 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  padding: 10px 20px;
}

.btn-primary {
  background: linear-gradient(135deg, #222660 0%, #F36E2B 100%);
  color: white;
}

.btn-primary:hover {
  box-shadow: 0 6px 20px rgba(243, 110, 43, 0.3);
  transform: translateY(-2px);
}

.btn-secondary {
  background: #f8f9fa;
  color: #6c757d;
  border: 2px solid #e9ecef;
}

.btn-secondary:hover {
  background: #e9ecef;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-danger:hover {
  background: #b91c1c;
  transform: translateY(-1px);
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-success:hover {
  background: #218838;
  transform: translateY(-1px);
}

/* ============ FORM ============ */
.form-label {
  display: block;
  margin-bottom: 6px;
  font-weight: 600;
  color: #495057;
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
  box-shadow: 0 0 0 3px rgba(243, 110, 43, 0.15);
}

/* ============ TABLE ============ */
        .table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
            position: relative;
        }

        .table-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #6366f1 0%, #8b5cf6 50%, #f59e0b 100%);
            z-index: 1;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px;
        }

        .table th {
            background: #f8fafc;
            color: #374151;
            padding: 16px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
            position: relative;
        }

        .table th::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #6366f1 0%, #f59e0b 100%);
        }

        .table td {
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
            transition: all 0.2s ease;
        }

        .table tbody tr {
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
        }

        .table tbody tr:hover {
            background: #f9fafb;
            border-left: 3px solid #6366f1;
            transform: translateX(2px);
        }

        .table tbody tr:nth-child(even) {
            background: #fafbfc;
        }

/* ============ STATUS BADGES ============ */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            transition: all 0.2s ease;
        }

        .status-active {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .status-inactive {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .avatar-container-large {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, #222660 0%, #F36E2B 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
            border: 2px solid #fed7aa;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        /* ============ MODAL ============ */
        .modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }

        .modal.show {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 32px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            transform: scale(0.8);
            transition: all 0.3s ease;
        }

        .modal.show .modal-content {
            transform: scale(1);
        }

 /* ============ AVATAR & QR ============ */
        .avatar-container, .avatar-container-large {
            border-radius: 50%;
            background: linear-gradient(135deg, #222660 0%, #F36E2B 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .avatar-container {
            width: 50px;
            height: 50px;
            font-size: 16px;
        }

        .avatar-container-large {
            width: 64px;
            height: 64px;
            font-size: 20px;
        }

        .qr-link-container {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            border-radius: 12px;
            transition: all 0.2s ease;
        }

        .qr-link-container.has-link {
            background: #f0fdf4;
            border: 2px solid #bbf7d0;
        }

        .qr-link-container.no-link {
            background: #fef2f2;
            border: 2px dashed #fecaca;
        }

        .qr-link-container:hover {
            transform: scale(1.02);
        }

        /* ============ NOTIFICATION ============ */
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
            display: flex;
            align-items: center;
        }

        .notification-toast.show {
            transform: translateX(0);
        }

        .notification-success { background: #28a745; }
        .notification-error { background: #dc3545; }
        .notification-warning { background: #ffc107; color: #212529; }

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

        /* ============ UTILITIES ============ */
        .link-preview {
            font-size: 12px;
            color: #6c757d;
            word-break: break-all;
            max-width: 200px;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .search-container {
                width: 100%;
                margin-bottom: 16px;
            }
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
                <div class="menu-item active p-3 mb-2">
                    <a href="karyawan.php" class="flex items-center text-white">
                        <i class="fas fa-users mr-3"></i>
                        <span>Karyawan</span>
                    </a>
                </div>
                <div class="menu-item p-3 mb-2">
                    <a href="absensi.php" class="flex items-center text-gray-300 hover:text-white">
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
                        <h1 class="text-2xl font-bold brand-primary">Kelola Karyawan</h1>
                        <p class="text-gray-600">Tambah, edit, dan kelola data karyawan</p>
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
                <!-- Action Bar -->
                <div class="flex justify-between items-center mb-6">
                    <div class="search-container">
                        <input type="text" id="searchInput" class="form-input search-input" placeholder="Cari karyawan...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                    <button onclick="showAddModal()" class="btn-primary">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Karyawan
                    </button>
                </div>

                <!-- Employee Table -->
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>No. HP</th>
                                <th>Pengabdian</th>
                                <th>Status</th>
                                <th>QR Link</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="employeeTableBody">
                            <?php if (empty($employees)): ?>
                            <tr>
                                <td colspan="8" class="empty-state">
                                    <i class="fas fa-users text-6xl mb-4 block"></i>
                                    <h3 class="text-xl font-semibold mb-2">Belum ada karyawan</h3>
                                    <p>Tambahkan karyawan pertama untuk memulai</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($employees as $emp): ?>
                            <tr data-employee-id="<?php echo $emp['id_karyawan']; ?>">
                                <td>
                                    <?php if ($emp['foto'] && file_exists('../uploads/' . $emp['foto'])): ?>
                                        <img src="../uploads/<?php echo htmlspecialchars($emp['foto']); ?>" 
                                             alt="<?php echo htmlspecialchars($emp['nama']); ?>" 
                                             class="w-16 h-16 rounded-full object-cover border-2 border-orange-200">
                                    <?php else: ?>
                                        <div class="avatar-container-large">
                                            <?php 
                                            $nama = $emp['nama'];
                                            $words = explode(' ', $nama);
                                            if (count($words) >= 2) {
                                                echo strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
                                            } else {
                                                echo strtoupper(substr($nama, 0, 2));
                                            }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <p class="font-semibold"><?php echo htmlspecialchars($emp['nama']); ?></p>
                                        <p class="text-sm text-gray-500"><?php echo date('d/m/Y', strtotime($emp['tanggal_lahir'])); ?></p>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($emp['jabatan']); ?></td>
                                <td><?php echo htmlspecialchars($emp['nomor_hp']); ?></td>
                                <td><?php echo $emp['lama_pengabdian']; ?> tahun</td>
                                <td>
                                    <span class="status-badge <?php echo $emp['status'] === 'aktif' ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo ucfirst($emp['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="qr-link-container <?php echo !empty($emp['kode_qr']) ? 'has-link' : 'no-link'; ?>">
                                        <?php if (!empty($emp['kode_qr'])): ?>
                                            <div class="text-center">
                                                <i class="fas fa-qrcode text-2xl text-green-600 mb-1"></i>
                                                <div class="link-preview"><?php echo substr($emp['kode_qr'], 0, 30) . '...'; ?></div>
                                            </div>
                                        <?php else: ?>
                                            <i class="fas fa-qrcode text-2xl text-red-400"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex space-x-2">
                                        <?php if (!empty($emp['kode_qr'])): ?>
                                            <a href="<?php echo htmlspecialchars($emp['kode_qr']); ?>" target="_blank" class="btn-success" title="Test QR Link">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                        <button onclick="editEmployee(<?php echo $emp['id_karyawan']; ?>)" class="btn-secondary">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="deleteEmployee(<?php echo $emp['id_karyawan']; ?>)" class="btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold brand-primary">Edit Karyawan</h2>
                <button onclick="closeModal('editModal')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" id="editEmployeeForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id_karyawan" id="editIdKaryawan">
                
                <!-- Photo Upload Section -->
                <div class="mb-6 text-center">
                    <label class="form-label">
                        <i class="fas fa-camera mr-2"></i>Foto Karyawan
                    </label>
                    <div class="flex flex-col items-center">
                        <div id="editPhotoPreview" class="w-24 h-24 rounded-full bg-gray-200 border-2 border-dashed border-gray-300 flex items-center justify-center mb-4 overflow-hidden">
                            <i class="fas fa-user text-gray-400 text-2xl"></i>
                        </div>
                        <input type="file" name="foto" id="editFotoInput" accept="image/*" class="hidden">
                        <button type="button" onclick="document.getElementById('editFotoInput').click()" class="btn-secondary">
                            <i class="fas fa-upload mr-2"></i>Ganti Foto
                        </button>
                        <p class="text-sm text-gray-500 mt-2">JPG, PNG, GIF (Max 2MB)</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="form-label">
                            <i class="fas fa-user mr-2"></i>Nama Lengkap
                        </label>
                        <input type="text" name="nama" id="editNama" class="form-input" required>
                    </div>
                    
                    <div>
                        <label class="form-label">
                            <i class="fas fa-calendar mr-2"></i>Tanggal Lahir
                        </label>
                        <input type="date" name="tanggal_lahir" id="editTanggalLahir" class="form-input" required>
                    </div>
                    
                    <div>
                        <label class="form-label">
                            <i class="fas fa-briefcase mr-2"></i>Jabatan
                        </label>
                        <input type="text" name="jabatan" id="editJabatan" class="form-input" placeholder="Masukkan jabatan" required>
                    </div>
                    
                    <div>
                        <label class="form-label">
                            <i class="fas fa-phone mr-2"></i>Nomor HP
                        </label>
                        <input type="tel" name="nomor_hp" id="editNomorHp" class="form-input" required>
                    </div>
                    
                    <div>
                        <label class="form-label">
                            <i class="fas fa-clock mr-2"></i>Lama Pengabdian (Tahun)
                        </label>
                        <input type="number" name="lama_pengabdian" id="editPengabdian" step="0.1" min="0" class="form-input" required>
                    </div>
                    
                    <div>
                        <label class="form-label">
                            <i class="fas fa-toggle-on mr-2"></i>Status
                        </label>
                        <select name="status" id="editStatus" class="form-input" required>
                            <option value="aktif">Aktif</option>
                            <option value="non-aktif">Non-aktif</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-qrcode mr-2"></i>Link QR Code
                    </label>
                    <input type="url" name="link_qr" id="editLinkQr" class="form-input" placeholder="https://creedcreativespekanbaru.com/karyawan/CC001" required>
                    <p class="text-sm text-gray-500 mt-1">Contoh: https://creedcreativespekanbaru.com/karyawan/CC001</p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('editModal')" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Employee Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold brand-primary">Tambah Karyawan</h2>
                <button onclick="closeModal('addModal')" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form method="POST" id="addEmployeeForm" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                
                <!-- Photo Upload Section -->
                <div class="mb-6 text-center">
                    <label class="form-label">
                        <i class="fas fa-camera mr-2"></i>Foto Karyawan
                    </label>
                    <div class="flex flex-col items-center">
                        <div id="photoPreview" class="w-24 h-24 rounded-full bg-gray-200 border-2 border-dashed border-gray-300 flex items-center justify-center mb-4 overflow-hidden">
                            <i class="fas fa-user text-gray-400 text-2xl"></i>
                        </div>
                        <input type="file" name="foto" id="fotoInput" accept="image/*" class="hidden">
                        <button type="button" onclick="document.getElementById('fotoInput').click()" class="btn-secondary">
                            <i class="fas fa-upload mr-2"></i>Pilih Foto
                        </button>
                        <p class="text-sm text-gray-500 mt-2">JPG, PNG, GIF (Max 2MB)</p>
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="form-label">
                            <i class="fas fa-user mr-2"></i>Nama Lengkap
                        </label>
                        <input type="text" name="nama" class="form-input" required>
                    </div>
                    
                    <div>
                        <label class="form-label">
                            <i class="fas fa-calendar mr-2"></i>Tanggal Lahir
                        </label>
                        <input type="date" name="tanggal_lahir" class="form-input" required>
                    </div>
                    
                    <div>
                        <label class="form-label">
                            <i class="fas fa-briefcase mr-2"></i>Jabatan
                        </label>
                        <input type="text" name="jabatan" class="form-input" placeholder="Masukkan jabatan" required>
                    </div>
                    
                    <div>
                        <label class="form-label">
                            <i class="fas fa-phone mr-2"></i>Nomor HP
                        </label>
                        <input type="tel" name="nomor_hp" class="form-input" required>
                    </div>
                    
                    <div>
                        <label class="form-label">
                            <i class="fas fa-clock mr-2"></i>Lama Pengabdian (Tahun)
                        </label>
                        <input type="number" name="lama_pengabdian" step="0.1" min="0" class="form-input" required>
                    </div>
                    
                    <div>
                        <label class="form-label">
                            <i class="fas fa-toggle-on mr-2"></i>Status
                        </label>
                        <select name="status" class="form-input" required>
                            <option value="aktif">Aktif</option>
                            <option value="non-aktif">Non-aktif</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">
                        <i class="fas fa-qrcode mr-2"></i>Link QR Code
                    </label>
                    <input type="url" name="link_qr" class="form-input" placeholder="https://creedcreativespekanbaru.com/karyawan/CC001" required>
                    <p class="text-sm text-gray-500 mt-1">Contoh: https://creedcreativespekanbaru.com/karyawan/CC001</p>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeModal('addModal')" class="btn-secondary">Batal</button>
                    <button type="submit" class="btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logout Confirmation Modal -->
    <div id="logoutModal" class="modal">
        <div class="modal-content max-w-md">
            <div class="text-center">
                <i class="fas fa-sign-out-alt text-6xl text-red-500 mb-4"></i>
                <h2 class="text-2xl font-bold mb-4">Konfirmasi Logout</h2>
                <p class="text-gray-600 mb-6">Apakah Anda yakin ingin keluar dari sistem?</p>
                
                <div class="flex justify-center space-x-3">
                    <button onclick="closeModal('logoutModal')" class="btn-secondary">Batal</button>
                    <a href="logout.php" class="btn-danger">Ya, Logout</a>
                </div>
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
        // Modal functions
        function showAddModal() {
            document.getElementById('addModal').classList.add('show');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
            // Reset form and photo preview when closing
            if (modalId === 'addModal') {
                document.getElementById('addEmployeeForm').reset();
                document.getElementById('photoPreview').innerHTML = '<i class="fas fa-user text-gray-400 text-2xl"></i>';
            } else if (modalId === 'editModal') {
                document.getElementById('editEmployeeForm').reset();
                document.getElementById('editPhotoPreview').innerHTML = '<i class="fas fa-user text-gray-400 text-2xl"></i>';
            }
        }

        function showLogoutConfirmation() {
            document.getElementById('logoutModal').classList.add('show');
        }

        // Employee functions
        function editEmployee(id) {
            // Find the employee row
            const row = document.querySelector(`tr[data-employee-id="${id}"]`);
            if (!row) return;
            
            const cells = row.querySelectorAll('td');
            
            // Extract data from table row
            const nama = cells[1].querySelector('p.font-semibold').textContent.trim();
            const tanggalLahir = cells[1].querySelector('p.text-sm').textContent.trim();
            const jabatan = cells[2].textContent.trim();
            const nomorHp = cells[3].textContent.trim();
            const pengabdian = cells[4].textContent.replace(' tahun', '').trim();
            const status = cells[5].querySelector('span').textContent.toLowerCase().trim();
            
            // Get QR link from the preview div or from PHP data
            const qrContainer = cells[6].querySelector('.qr-link-container');
            let qrLink = '';
            if (qrContainer.classList.contains('has-link')) {
                // We need to get the actual link from PHP data since it's truncated in display
                // For now, we'll leave it empty and user can fill it
                qrLink = '';
            }
            
            // Populate edit form
            document.getElementById('editIdKaryawan').value = id;
            document.getElementById('editNama').value = nama;
            document.getElementById('editTanggalLahir').value = convertDateFormat(tanggalLahir);
            document.getElementById('editJabatan').value = jabatan;
            document.getElementById('editNomorHp').value = nomorHp;
            document.getElementById('editPengabdian').value = pengabdian;
            document.getElementById('editStatus').value = status;
            document.getElementById('editLinkQr').value = qrLink;
            
            // Handle photo preview
            const photoImg = cells[0].querySelector('img');
            const editPhotoPreview = document.getElementById('editPhotoPreview');
            
            if (photoImg) {
                editPhotoPreview.innerHTML = `<img src="${photoImg.src}" class="w-full h-full object-cover rounded-full">`;
            } else {
                const avatarDiv = cells[0].querySelector('.avatar-container-large');
                if (avatarDiv) {
                    editPhotoPreview.innerHTML = `<div class="w-full h-full rounded-full bg-gradient-to-r from-blue-900 to-orange-500 flex items-center justify-center text-white font-bold text-lg">${avatarDiv.textContent}</div>`;
                }
            }
            
            // Show edit modal
            document.getElementById('editModal').classList.add('show');
        }
        
        function convertDateFormat(dateStr) {
            // Convert from dd/mm/yyyy to yyyy-mm-dd
            const parts = dateStr.split('/');
            if (parts.length === 3) {
                return `${parts[2]}-${parts[1].padStart(2, '0')}-${parts[0].padStart(2, '0')}`;
            }
            return '';
        }

        function deleteEmployee(id) {
            if (confirm('Yakin ingin menghapus karyawan ini? Foto juga akan terhapus.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id_karyawan" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('#employeeTableBody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Photo preview functionality for Add Modal
        document.getElementById('fotoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('photoPreview');
            
            if (file) {
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showNotification('Ukuran file terlalu besar! Maksimal 2MB.', 'error');
                    e.target.value = '';
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    showNotification('Format file tidak didukung! Gunakan JPG, PNG, atau GIF.', 'error');
                    e.target.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover rounded-full">`;
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '<i class="fas fa-user text-gray-400 text-2xl"></i>';
            }
        });

        // Photo preview functionality for Edit Modal
        document.getElementById('editFotoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('editPhotoPreview');
            
            if (file) {
                // Validate file size (2MB)
                if (file.size > 2 * 1024 * 1024) {
                    showNotification('Ukuran file terlalu besar! Maksimal 2MB.', 'error');
                    e.target.value = '';
                    return;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    showNotification('Format file tidak didukung! Gunakan JPG, PNG, atau GIF.', 'error');
                    e.target.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover rounded-full">`;
                };
                reader.readAsDataURL(file);
            }
        });

        // Notification functions
        function showNotification(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `notification-toast notification-${type} show`;
            toast.innerHTML = `<i class="fas fa-info-circle mr-2"></i>${message}`;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // Auto-hide toast
        setTimeout(() => {
            const toast = document.getElementById('toast');
            if (toast) {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }
        }, 3000);

        // Close modal when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('modal')) {
                e.target.classList.remove('show');
            }
        });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'98fcdaa4e559b907',t:'MTc2MDY3MzI2MC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
