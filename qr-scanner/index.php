<?php
// Skrip ini dirancang untuk diakses oleh alat scanner QR code, 
// biasanya melalui POST request yang mengirimkan data QR.

require_once '../config/database.php';

header('Content-Type: application/json');

$response = [
    'status' => 'error',
    'message' => 'Aksi gagal.',
    'karyawan' => null
];

// Asumsi data QR dikirim melalui metode POST dengan kunci 'qr_code'
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['qr_code'])) {
    $kode_qr = trim($_POST['qr_code']);
    $tanggal_hari_ini = date('Y-m-d');
    $waktu_sekarang = date('H:i:s');

    if (empty($kode_qr)) {
        $response['message'] = 'Kode QR tidak boleh kosong.';
        echo json_encode($response);
        exit();
    }

    try {
        // 1. Cari ID Karyawan berdasarkan Kode QR
        $stmt_karyawan = $pdo->prepare("SELECT id_karyawan, nama, status FROM karyawan WHERE kode_qr = ?");
        $stmt_karyawan->execute([$kode_qr]);
        $karyawan = $stmt_karyawan->fetch();

        if (!$karyawan) {
            $response['message'] = 'Kode QR tidak valid atau karyawan tidak terdaftar.';
            echo json_encode($response);
            exit();
        }

        if ($karyawan['status'] == 'nonaktif') {
            $response['message'] = 'Karyawan ini berstatus nonaktif dan tidak bisa absen.';
            echo json_encode($response);
            exit();
        }

        $id_karyawan = $karyawan['id_karyawan'];
        $response['karyawan'] = ['nama' => $karyawan['nama']];

        // 2. Cek apakah sudah ada data absensi hari ini
        $stmt_absensi = $pdo->prepare("SELECT id_absensi, jam_masuk, jam_pulang FROM absensi WHERE id_karyawan = ? AND tanggal = ?");
        $stmt_absensi->execute([$id_karyawan, $tanggal_hari_ini]);
        $absensi_hari_ini = $stmt_absensi->fetch();

        if (!$absensi_hari_ini) {
            // --- PROSES JAM MASUK (CLOCK-IN) ---
            $stmt_insert = $pdo->prepare("INSERT INTO absensi (id_karyawan, tanggal, jam_masuk, status) VALUES (?, ?, ?, 'hadir')");
            $stmt_insert->execute([$id_karyawan, $tanggal_hari_ini, $waktu_sekarang]);

            $response['status'] = 'success';
            $response['message'] = 'Absensi Masuk berhasil dicatat! Selamat Bekerja, ' . $karyawan['nama'];
        } else if (empty($absensi_hari_ini['jam_pulang'])) {
            // --- PROSES JAM PULANG (CLOCK-OUT) ---

            $jam_masuk = new DateTime($absensi_hari_ini['jam_masuk']);
            $jam_pulang = new DateTime($waktu_sekarang);
            $durasi = $jam_masuk->diff($jam_pulang);
            
            // Hitung Total Jam Kerja (DECIMAL(5,2))
            $total_jam_float = $durasi->h + ($durasi->i / 60); // Jam + Menit/60
            
            $stmt_update = $pdo->prepare("UPDATE absensi SET jam_pulang = ?, total_jam = ?, status = 'hadir' WHERE id_absensi = ?");
            $stmt_update->execute([$waktu_sekarang, number_format($total_jam_float, 2), $absensi_hari_ini['id_absensi']]);

            $response['status'] = 'success';
            $response['message'] = 'Absensi Pulang berhasil dicatat! Total jam kerja: ' . $durasi->h . ' jam ' . $durasi->i . ' menit. Sampai Jumpa, ' . $karyawan['nama'];

        } else {
            // --- SUDAH ABSEN MASUK & PULANG ---
            $response['status'] = 'info';
            $response['message'] = 'Anda sudah melakukan Absensi Masuk dan Pulang hari ini.';
        }

    } catch (Exception $e) {
        // Tangani kesalahan database
        $response['message'] = 'Sistem Error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Akses tidak sah atau kode QR tidak diterima.';
}

echo json_encode($response);
?>