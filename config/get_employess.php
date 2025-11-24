<?php
include 'database.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT id_karyawan, nama, jabatan FROM karyawan WHERE status = 'aktif' ORDER BY nama");
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $employees]);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>