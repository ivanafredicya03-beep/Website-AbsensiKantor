<?php
// config/get_dashboard_stats.php
header('Content-Type: application/json; charset=utf-8');
require_once 'database.php'; // koneksi PDO $pdo
session_start();

// Pastikan request dari user yang sudah login (simple check)
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
    exit();
}

$response = [
    'success' => false,
    'stats' => [
        'total_karyawan' => 0,
        'hadir_hari_ini' => 0,
        'terlambat' => 0,
        'izin_sakit' => 0,
        'tidak_hadir' => 0
    ],
    'recent_activity' => [],
    'weekly_data' => [],
    'notifications' => 0
];

try {
    $today = date('Y-m-d');

    // Total Karyawan aktif
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM karyawan WHERE status = 'aktif'");
    $stmt->execute();
    $response['stats']['total_karyawan'] = (int)$stmt->fetchColumn();

    // Hadir hari ini (status 'hadir' dan jam_masuk tidak null)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = ? AND status = 'hadir' AND jam_masuk IS NOT NULL");
    $stmt->execute([$today]);
    $response['stats']['hadir_hari_ini'] = (int)$stmt->fetchColumn();

    // Terlambat (hanya hitung yang berstatus 'hadir' dan jam_masuk > 08:00:00)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = ? AND status = 'hadir' AND jam_masuk > '08:00:00'");
    $stmt->execute([$today]);
    $response['stats']['terlambat'] = (int)$stmt->fetchColumn();

    // Izin / Sakit hari ini (dari tabel izin)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM izin WHERE tanggal = ?");
    $stmt->execute([$today]);
    $response['stats']['izin_sakit'] = (int)$stmt->fetchColumn();

    // Tidak hadir = karyawan aktif yang tidak punya entri absensi hari ini
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM karyawan
        WHERE status = 'aktif' AND id_karyawan NOT IN (SELECT id_karyawan FROM absensi WHERE tanggal = ?)
    ");
    $stmt->execute([$today]);
    $response['stats']['tidak_hadir'] = (int)$stmt->fetchColumn();

    // Recent Activity (absensi hari ini, ambil jam_masuk/jam_pulang)
    $stmt = $pdo->prepare("
        SELECT k.nama, a.jam_masuk, a.jam_pulang, a.status
        FROM absensi a
        JOIN karyawan k ON a.id_karyawan = k.id_karyawan
        WHERE a.tanggal = ?
        ORDER BY a.jam_masuk DESC
        LIMIT 5
    ");
    $stmt->execute([$today]);
    $recent = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $jam_masuk = $row['jam_masuk'] ? date('H:i', strtotime($row['jam_masuk'])) : '-';
        $jam_pulang = $row['jam_pulang'] ? date('H:i', strtotime($row['jam_pulang'])) : null;
        $recent[] = [
            'nama' => $row['nama'],
            'status' => strtolower($row['status']),
            'waktu' => $jam_masuk,
            'waktu_pulang' => $jam_pulang
        ];
    }
    $response['recent_activity'] = $recent;

    // Weekly Data: Buat array 7 hari terakhir, isi 0 jika tidak ada data
    $startDate = date('Y-m-d', strtotime('-6 days')); // 6 hari lalu sampai hari ini -> total 7 hari
    $stmt = $pdo->prepare("
        SELECT DATE(tanggal) AS day, COUNT(*) AS value
        FROM absensi
        WHERE tanggal BETWEEN ? AND ?
        GROUP BY DATE(tanggal)
        ORDER BY day ASC
    ");
    $stmt->execute([$startDate, $today]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Map results by day for quick lookup
    $map = [];
    foreach ($rows as $r) {
        $map[$r['day']] = (int)$r['value'];
    }

    $weekly = [];
    $indoMap = ['Mon'=>'Sen','Tue'=>'Sel','Wed'=>'Rab','Thu'=>'Kam','Fri'=>'Jum','Sat'=>'Sab','Sun'=>'Min'];
    for ($i = 6; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $value = isset($map[$d]) ? $map[$d] : 0;
        $dayNameEng = date('D', strtotime($d)); // Mon, Tue, ...
        $dayLabel = $indoMap[$dayNameEng] ?? $dayNameEng;
        $weekly[] = [
            'day' => $dayLabel,
            'date' => $d,
            'value' => $value
        ];
    }
    $response['weekly_data'] = $weekly;

    // Notifikasi: hitung yang belum dibaca sesuai struktur tabel (belum_dibaca)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifikasi WHERE status = 'belum_dibaca'");
    $stmt->execute();
    $response['notifications'] = (int)$stmt->fetchColumn();

    $response['success'] = true;
} catch (Exception $e) {
    // Log error di server untuk debugging, jangan kirim raw message ke client
    error_log("get_dashboard_stats error: " . $e->getMessage());
    $response['success'] = false;
    $response['message'] = 'Terjadi kesalahan pada server.';
}

echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit();
?>