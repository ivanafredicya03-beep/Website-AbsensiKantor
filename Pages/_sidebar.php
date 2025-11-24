<?php
$active = $active ?? '';
?>
<div class="sidebar w-64 p-6 flex flex-col">
  <div class="text-center mb-8">
    <div class="logo-container w-16 h-16 bg-white rounded-xl mx-auto mb-3 flex items-center justify-center border-4 border-white shadow-lg">
      <img src="../assets/images/logo.png.webp" alt="Logo" class="w-full h-full object-contain">
    </div>
    <h2 class="text-white font-bold text-lg">QR Absensi</h2>
    <p class="text-gray-300 text-sm">CreedCreatives</p>
  </div>

  <nav class="flex-1">
    <div class="menu-item p-3 mb-2 <?= $active === 'dashboard' ? 'active' : '' ?>">
      <a href="dashboard.php" class="flex items-center <?= $active === 'dashboard' ? 'text-white' : 'text-gray-300 hover:text-white' ?>"><i class="fas fa-home mr-3"></i><span>Dashboard</span></a>
    </div>
    <div class="menu-item p-3 mb-2 <?= $active === 'karyawan' ? 'active' : '' ?>">
      <a href="karyawan.php" class="flex items-center <?= $active === 'karyawan' ? 'text-white' : 'text-gray-300 hover:text-white' ?>"><i class="fas fa-users mr-3"></i><span>Karyawan</span></a>
    </div>
    <div class="menu-item p-3 mb-2 <?= $active === 'absensi' ? 'active' : '' ?>">
      <a href="absensi.php" class="flex items-center <?= $active === 'absensi' ? 'text-white' : 'text-gray-300 hover:text-white' ?>"><i class="fas fa-calendar-check mr-3"></i><span>Absensi</span></a>
    </div>
    <div class="menu-item p-3 mb-2 <?= $active === 'laporan' ? 'active' : '' ?>">
      <a href="laporan.php" class="flex items-center <?= $active === 'laporan' ? 'text-white' : 'text-gray-300 hover:text-white' ?>"><i class="fas fa-chart-bar mr-3"></i><span>Laporan</span></a>
    </div>
    <div class="menu-item p-3 mb-2 <?= $active === 'pengaturan' ? 'active' : '' ?>">
      <a href="pengaturan.php" class="flex items-center <?= $active === 'pengaturan' ? 'text-white' : 'text-gray-300 hover:text-white' ?>"><i class="fas fa-cog mr-3"></i><span>Pengaturan</span></a>
    </div>
  </nav>

  <div class="menu-item p-3 mt-4">
    <a href="#" onclick="showLogoutConfirmation()" class="flex items-center text-gray-300 hover:text-white"><i class="fas fa-sign-out-alt mr-3"></i><span>Logout</span></a>
  </div>
</div>