<?php
session_start();
require_once('../../config/database.php');

// Cek login
if (!isset($_SESSION['users'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Ambil data JSON dari request
$data = json_decode(file_get_contents('php://input'), true);

// Validasi input
if (!isset($data['status']) || !in_array($data['status'], [0, 1])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status value']);
    exit();
}

$newStatus = (int)$data['status'];

try {
    // Mulai transaksi
    $conn->begin_transaction();

    // Update status semua perangkat
    $query = "UPDATE devices SET status = ?, updated_at = CURRENT_TIMESTAMP";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $newStatus);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update devices status");
    }

    // Commit transaksi
    $conn->commit();

    // Kirim response sukses
    echo json_encode([
        'success' => true,
        'message' => 'All devices status updated successfully',
        'new_status' => $newStatus
    ]);

} catch (Exception $e) {
    // Rollback jika terjadi error
    $conn->rollback();
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// Tutup koneksi
$stmt->close();
$conn->close();