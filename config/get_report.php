<?php
include 'database.php';
header('Content-Type: application/json');

$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$employee_id = $_GET['employee_id'] ?? '';
$status = $_GET['status'] ?? '';

try {
    $sql = "SELECT a.*, k.nama, k.jabatan 
            FROM absensi a 
            JOIN karyawan k ON a.id_karyawan = k.id_karyawan 
            WHERE a.tanggal BETWEEN ? AND ?";
    
    $params = [$start_date, $end_date];
    
    if ($employee_id) {
        $sql .= " AND a.id_karyawan = ?";
        $params[] = $employee_id;
    }
    
    if ($status) {
        $sql .= " AND a.status = ?";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY a.tanggal DESC, a.jam_masuk DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get statistics
    $stats_sql = "SELECT status, COUNT(*) as count FROM absensi WHERE tanggal BETWEEN ? AND ?";
    if ($employee_id) {
        $stats_sql .= " AND id_karyawan = ?";
    }
    $stats_sql .= " GROUP BY status";
    
    $stats_stmt = $pdo->prepare($stats_sql);
    $stats_params = [$start_date, $end_date];
    if ($employee_id) $stats_params[] = $employee_id;
    
    $stats_stmt->execute($stats_params);
    $stats_raw = $stats_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stats = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0];
    foreach ($stats_raw as $stat) {
        $stats[$stat['status']] = $stat['count'];
    }
    
    echo json_encode(['success' => true, 'data' => $data, 'stats' => $stats]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>