<?php
$host = 'localhost';
$username = 'root';
$password = '';  // Sesuaikan dengan password database Anda
$database = 'smarthome';

try {
    $conn = new mysqli($host, $username, $password, $database);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Set charset ke UTF-8
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Fungsi untuk menghitung jumlah perangkat aktif
function getActiveDevicesCount($conn) {
    $query = "SELECT COUNT(*) as count FROM devices WHERE status = 'active'";
    $result = $conn->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    return 0;
}

// Fungsi untuk menghitung jumlah ruangan
function getRoomsCount($conn) {
    $query = "SELECT COUNT(*) as count FROM rooms";
    $result = $conn->query($query);
    if ($result) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    return 0;
}
?>
