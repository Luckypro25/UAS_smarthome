<?php
session_start();
require_once('../config/database.php');

// Periksa login
if (!isset($_SESSION['users'])) {
    header("Location: login.php");
    exit();
}

// Ambil semua devices dengan informasi ruangan
$query = "SELECT devices.*, rooms.name as room_name 
          FROM devices 
          JOIN rooms ON devices.room_id = rooms.id 
          ORDER BY rooms.name, devices.name";
$result = $conn->query($query);

// Hitung total devices dan status
$total_devices = $result->num_rows;
$active_devices = 0;
$devices_by_type = [];

while ($row = $result->fetch_assoc()) {
    if ($row['status'] == 1) $active_devices++;
    $devices_by_type[$row['type']][] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devices - MBRK Smart Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.12);
            --glass-highlight: rgba(255, 255, 255, 0.15);
            --text-primary: rgba(255, 255, 255, 0.95);
            --text-secondary: rgba(255, 255, 255, 0.7);
            --accent-1: #93c5fd;
            --navbar-bg: #3498db;
        }

        body {
            background: linear-gradient(135deg, #1a1a1a, #2c3e50);
            color: var(--text-primary);
            min-height: 100vh;
            margin: 0;
            font-family: 'Poppins', sans-serif;
        }

        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            border-color: var(--accent-1);
            transform: translateY(-5px);
        }

        .stats-card {
            padding: 1.5rem;
            text-align: center;
        }

        .stats-card i {
            font-size: 2rem;
            color: var(--accent-1);
            margin-bottom: 1rem;
        }

        .device-type-section {
            margin-bottom: 2rem;
        }

        .device-type-header {
            color: var(--accent-1);
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--glass-border);
            padding-bottom: 0.5rem;
        }

        .device-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .device-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent-1);
        }

        .device-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--accent-1);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-active {
            background: rgba(74, 222, 128, 0.2);
            color: #4ade80;
            border: 1px solid rgba(74, 222, 128, 0.4);
        }

        .status-inactive {
            background: rgba(248, 113, 113, 0.2);
            color: #f87171;
            border: 1px solid rgba(248, 113, 113, 0.4);
        }

        .room-badge {
            background: var(--glass-highlight);
            color: var(--text-secondary);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
        }

        .navbar {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
        }

        .nav-link {
            color: var(--text-primary) !important;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--accent-1) !important;
        }

        .nav-link.active {
            color: var(--accent-1) !important;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="#">
                <i class="fas fa-home me-2"></i>MBRK Smart Home
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars text-white"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="rooms.php">Rooms</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="devices.php">Devices</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="glass-card stats-card">
                    <i class="fas fa-microchip"></i>
                    <h3><?php echo $total_devices; ?></h3>
                    <p class="text-secondary mb-0">Total Devices</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card stats-card">
                    <i class="fas fa-power-off"></i>
                    <h3><?php echo $active_devices; ?></h3>
                    <p class="text-secondary mb-0">Active Devices</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card stats-card">
                    <i class="fas fa-percentage"></i>
                    <h3><?php echo $total_devices > 0 ? round(($active_devices / $total_devices) * 100) : 0; ?>%</h3>
                    <p class="text-secondary mb-0">Usage Rate</p>
                </div>
            </div>
        </div>

        <!-- Devices by Type -->
        <?php foreach ($devices_by_type as $type => $devices): ?>
        <div class="device-type-section">
            <h4 class="device-type-header">
                <i class="fas fa-<?php 
                    echo match($type) {
                        'light' => 'lightbulb',
                        'ac' => 'snowflake',
                        'tv' => 'tv',
                        'fan' => 'fan',
                        default => 'plug'
                    }; 
                ?> me-2"></i>
                <?php echo ucfirst($type); ?> Devices
            </h4>
            <div class="row">
                <?php foreach ($devices as $device): ?>
                <div class="col-md-4 mb-3">
                    <div class="device-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h5 class="mb-0"><?php echo htmlspecialchars($device['name']); ?></h5>
                            <span class="room-badge">
                                <i class="fas fa-door-open me-1"></i>
                                <?php echo htmlspecialchars($device['room_name']); ?>
                            </span>
                        </div>
                        <div class="text-center mb-3">
                            <i class="fas fa-<?php 
                                echo match($type) {
                                    'light' => 'lightbulb',
                                    'ac' => 'snowflake',
                                    'tv' => 'tv',
                                    'fan' => 'fan',
                                    default => 'plug'
                                }; 
                            ?> device-icon"></i>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="status-badge <?php echo $device['status'] == 1 ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $device['status'] == 1 ? 'Active' : 'Inactive'; ?>
                            </span>
                            <a href="room_detail.php?id=<?php echo $device['room_id']; ?>" 
                               class="btn btn-sm btn-outline-light">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
