<?php
session_start();
require_once('../../config/database.php');

// Cek login
if (!isset($_SESSION['users'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Ambil status semua perangkat
$query = "SELECT id, status FROM devices";
$result = $conn->query($query);

$devices = [];
while ($row = $result->fetch_assoc()) {
    $devices[] = $row;
}

header('Content-Type: application/json');
echo json_encode($devices); 