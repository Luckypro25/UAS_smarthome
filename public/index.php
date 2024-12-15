<?php
session_start();
require_once('../config/database.php');

// Cek login
if (!isset($_SESSION['users'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['users'];

// Ambil statistik
$stats = [
    'total_rooms' => $conn->query("SELECT COUNT(*) as count FROM rooms")->fetch_assoc()['count'],
    'total_devices' => $conn->query("SELECT COUNT(*) as count FROM devices")->fetch_assoc()['count'],
    'active_devices' => $conn->query("SELECT COUNT(*) as count FROM devices WHERE status = 1")->fetch_assoc()['count']
];

// Ambil ruangan dengan perangkat aktif terbanyak
$top_rooms_query = "SELECT r.name, COUNT(d.id) as device_count, 
                    SUM(CASE WHEN d.status = 1 THEN 1 ELSE 0 END) as active_devices
                    FROM rooms r
                    LEFT JOIN devices d ON r.id = d.room_id
                    GROUP BY r.id
                    ORDER BY active_devices DESC
                    LIMIT 4";
$top_rooms = $conn->query($top_rooms_query);

// Ambil aktivitas terbaru
$recent_activities_query = "SELECT d.name as device_name, d.type, d.status, r.name as room_name, 
                           d.updated_at
                           FROM devices d
                           JOIN rooms r ON d.room_id = r.id
                           ORDER BY d.updated_at DESC
                           LIMIT 5";
$recent_activities = $conn->query($recent_activities_query);

// Fungsi untuk mendapatkan nama hari dalam Bahasa Indonesia
function getIndonesianDay($timestamp) {
    $days = array(
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    );
    return $days[date('l', $timestamp)];
}

// Fungsi untuk mendapatkan nama bulan dalam Bahasa Indonesia
function getIndonesianMonth($timestamp) {
    $months = array(
        'January' => 'Januari',
        'February' => 'Februari',
        'March' => 'Maret',
        'April' => 'April',
        'May' => 'Mei',
        'June' => 'Juni',
        'July' => 'Juli',
        'August' => 'Agustus',
        'September' => 'September',
        'October' => 'Oktober',
        'November' => 'November',
        'December' => 'Desember'
    );
    return $months[date('F', $timestamp)];
}

$current_timestamp = time();
$current_date = getIndonesianDay($current_timestamp) . ', ' . 
                date('d') . ' ' . 
                getIndonesianMonth($current_timestamp) . ' ' . 
                date('Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MBRK Smart Home</title>
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
            height: 100%;
        }

        .glass-card:hover {
            border-color: var(--accent-1);
            transform: translateY(-5px);
        }

        .welcome-card {
            background: linear-gradient(45deg, rgba(147, 197, 253, 0.1), rgba(52, 152, 219, 0.1));
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
        }

        .stats-card {
            padding: 1.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, var(--accent-1) 0%, transparent 60%);
            opacity: 0.1;
            transform: rotate(30deg);
        }

        .stats-icon {
            font-size: 2.5rem;
            color: var(--accent-1);
            margin-bottom: 1rem;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .room-card {
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        .room-icon {
            width: 50px;
            height: 50px;
            background: var(--glass-highlight);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--accent-1);
            margin-right: 1rem;
        }

        .activity-item {
            padding: 1rem;
            border-bottom: 1px solid var(--glass-border);
            transition: all 0.3s ease;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-item:hover {
            background: var(--glass-highlight);
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: var(--glass-highlight);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--accent-1);
        }

        .time-badge {
            background: var(--glass-highlight);
            color: var(--text-secondary);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
        }

        .progress {
            background: var(--glass-highlight);
            height: 8px;
            border-radius: 4px;
        }

        .progress-bar {
            background: var(--accent-1);
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

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }

        .action-btn {
            background: var(--glass-highlight);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            background: var(--accent-1);
            color: #fff;
            transform: translateY(-3px);
        }

        .weather-widget {
            padding: 1.5rem;
            text-align: center;
            background: linear-gradient(45deg, rgba(147, 197, 253, 0.1), rgba(52, 152, 219, 0.1));
            transition: all 0.3s ease;
        }

        .weather-widget h3 {
            font-size: 2rem;
            margin: 0.5rem 0;
            color: var(--text-primary);
        }

        .weather-widget p {
            color: var(--text-secondary);
        }

        .weather-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--accent-1);
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .pulse {
            animation: pulse 2s infinite;
        }
        .datetime-section {
        text-align: center;
        margin-bottom: 2rem;
        padding: 2rem;
        background: linear-gradient(45deg, rgba(147, 197, 253, 0.1), rgba(52, 152, 219, 0.1));
        border-radius: 20px;
        backdrop-filter: blur(10px);
    }

    .clock {
        font-size: 3.5rem;
        font-weight: 600;
        color: var(--accent-1);
        text-shadow: 0 0 20px rgba(147, 197, 253, 0.3);
        margin-bottom: 0.5rem;
    }

    .date {
        font-size: 1.2rem;
        color: var(--text-secondary);
    }

    .location {
        margin-top: 1rem;
        font-size: 1.1rem;
        color: var(--text-primary);
    }

    .location i {
        color: var(--accent-1);
        margin-right: 0.5rem;
    }

    .page-title {
        text-align: center;
        margin-bottom: 2rem;
        color: var(--accent-1);
        font-size: 2.5rem;
        font-weight: 600;
        text-shadow: 0 0 20px rgba(147, 197, 253, 0.3);
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    .fade-in {
        animation: fadeIn 1s ease-in;
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
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="rooms.php">Rooms</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="devices.php">Devices</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn text-white dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($user['username']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Page Title -->
        <h1 class="page-title mb-4">
            <i class="fas fa-home me-2"></i>
            MBRK Smart Home Dashboard
        </h1>

        

        <!-- Welcome Section -->
        <div class="welcome-card glass-card">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                    <p class="text-secondary">Here's what's happening in your smart home today</p>
                    <div class="quick-actions">
                        <a href="rooms.php" class="action-btn">
                            <i class="fas fa-door-open mb-2"></i>
                            <div>Manage Rooms</div>
                        </a>
                        <a href="devices.php" class="action-btn">
                            <i class="fas fa-microchip mb-2"></i>
                            <div>Manage Devices</div>
                        </a>
                        <a href="#" class="action-btn" id="toggleAllDevices">
                            <i class="fas fa-power-off mb-2"></i>
                            <div>Toggle All</div>
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="weather-widget glass-card">
                        <i class="fas fa-sun weather-icon pulse"></i>
                        <h3>28°C</h3>
                        <p class="mb-0">Sunny Day</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- DateTime & Location Section -->
        <div class="datetime-section glass-card mb-4">
            <div class="clock" id="clock">00:00:00</div>
            <div class="date"><?php echo $current_date; ?></div>
            <div class="location" id="location">
                <i class="fas fa-map-marker-alt"></i>
                <span>Mencari lokasi...</span>
            </div>
        </div>
        <!-- Stats Row -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="glass-card stats-card">
                    <i class="fas fa-door-open stats-icon"></i>
                    <div class="stats-number"><?php echo $stats['total_rooms']; ?></div>
                    <div class="text-secondary">Total Rooms</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card stats-card">
                    <i class="fas fa-microchip stats-icon"></i>
                    <div class="stats-number"><?php echo $stats['total_devices']; ?></div>
                    <div class="text-secondary">Total Devices</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card stats-card">
                    <i class="fas fa-bolt stats-icon"></i>
                    <div class="stats-number"><?php echo $stats['active_devices']; ?></div>
                    <div class="text-secondary">Active Devices</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top Rooms -->
            <div class="col-md-8 mb-4">
                <div class="glass-card">
                    <div class="card-body">
                        <h4 class="mb-4">Room Overview</h4>
                        <?php while ($room = $top_rooms->fetch_assoc()): ?>
                        <div class="room-card glass-card mb-3">
                            <div class="d-flex align-items-center">
                                <div class="room-icon">
                                    <i class="fas fa-door-open"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="mb-2"><?php echo htmlspecialchars($room['name']); ?></h5>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: <?php echo ($room['device_count'] > 0) ? ($room['active_devices'] / $room['device_count'] * 100) : 0; ?>%"></div>
                                    </div>
                                    <div class="mt-2 text-secondary">
                                        <?php echo $room['active_devices']; ?> of <?php echo $room['device_count']; ?> devices active
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="col-md-4 mb-4">
                <div class="glass-card">
                    <div class="card-body">
                        <h4 class="mb-4">Recent Activities</h4>
                        <?php while ($activity = $recent_activities->fetch_assoc()): ?>
                        <div class="activity-item">
                            <div class="d-flex align-items-center">
                                <div class="activity-icon me-3">
                                    <i class="fas fa-<?php 
                                        echo match($activity['type']) {
                                            'light' => 'lightbulb',
                                            'ac' => 'snowflake',
                                            'tv' => 'tv',
                                            'fan' => 'fan',
                                            default => 'plug'
                                        }; 
                                    ?>"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($activity['device_name']); ?></h6>
                                        <span class="time-badge">
                                            <?php echo date('H:i', strtotime($activity['updated_at'])); ?>
                                        </span>
                                    </div>
                                    <small class="text-secondary">
                                        <?php echo htmlspecialchars($activity['room_name']); ?> - 
                                        <?php echo $activity['status'] == 1 ? 'Turned On' : 'Turned Off'; ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update Clock
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('clock').textContent = `${hours}:${minutes}:${seconds}`;
        }

        // Update clock setiap detik
        setInterval(updateClock, 1000);
        updateClock(); // Initial call

        // Get Location
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    position => {
                        const latitude = position.coords.latitude;
                        const longitude = position.coords.longitude;
                        
                        // Get weather data
                        getWeather(latitude, longitude);
                        
                        // Get location name (existing code)
                        fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`)
                            .then(response => response.json())
                            .then(data => {
                                const city = data.address.city || data.address.town || data.address.village || 'Unknown City';
                                const region = data.address.state || '';
                                document.getElementById('location').innerHTML = `
                                    <i class="fas fa-map-marker-alt"></i>
                                    ${city}, ${region}
                                `;
                            })
                            .catch(error => {
                                console.error('Error getting location name:', error);
                                document.getElementById('location').innerHTML = `
                                    <i class="fas fa-map-marker-alt"></i>
                                    Lokasi tidak dapat ditampilkan
                                `;
                            });
                    },
                    error => {
                        console.error('Error getting location:', error);
                        document.getElementById('location').innerHTML = `
                            <i class="fas fa-map-marker-alt"></i>
                            Lokasi tidak diizinkan
                        `;
                    }
                );
            } else {
                document.getElementById('location').innerHTML = `
                    <i class="fas fa-map-marker-alt"></i>
                    Geolocation tidak didukung
                `;
            }
        }

        // Get location when page loads
        getLocation();

        // Toggle All Devices
        document.getElementById('toggleAllDevices').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Tampilkan konfirmasi
            const confirmToggle = confirm('Apakah Anda yakin ingin mengubah status semua perangkat?');
            if (!confirmToggle) return;

            // Ambil semua perangkat dari database
            fetch('api/get_devices_status.php')
                .then(response => response.json())
                .then(devices => {
                    // Hitung jumlah perangkat yang aktif
                    const activeDevices = devices.filter(device => device.status == 1).length;
                    // Tentukan status baru berdasarkan jumlah perangkat aktif
                    const newStatus = activeDevices > 0 ? 0 : 1;

                    // Kirim permintaan untuk mengubah status semua perangkat
                    return fetch('api/toggle_all_devices.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            status: newStatus
                        })
                    });
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        // Jika berhasil, refresh halaman
                        window.location.reload();
                    } else {
                        // Jika gagal, tampilkan pesan error
                        alert('Gagal mengubah status perangkat: ' + (result.error || 'Unknown error'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mengubah status perangkat');
                });
        });

        // Update time
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleTimeString();
        }
        setInterval(updateTime, 1000);
        updateTime();
        
        function getWeather(latitude, longitude) {
            const API_KEY = 'YOUR_OPENWEATHERMAP_API_KEY'; // Ganti dengan API key Anda
            const weatherUrl = `https://api.openweathermap.org/data/2.5/weather?lat=${latitude}&lon=${longitude}&units=metric&appid=${API_KEY}`;
            
            fetch(weatherUrl)
                .then(response => response.json())
                .then(data => {
                    const temp = Math.round(data.main.temp);
                    const weatherDesc = data.weather[0].main;
                    const weatherIcon = getWeatherIcon(data.weather[0].icon);
                    
                    document.querySelector('.weather-widget').innerHTML = `
                        <i class="fas ${weatherIcon} weather-icon pulse"></i>
                        <h3>${temp}°C</h3>
                        <p class="mb-0">${weatherDesc}</p>
                    `;
                })
                .catch(error => {
                    console.error('Error fetching weather:', error);
                    document.querySelector('.weather-widget').innerHTML = `
                        <i class="fas fa-cloud weather-icon"></i>
                        <p class="mb-0">Weather data unavailable</p>
                    `;
                });
        }

        function getWeatherIcon(iconCode) {
            const iconMap = {
                '01d': 'fa-sun', // clear sky day
                '01n': 'fa-moon', // clear sky night
                '02d': 'fa-cloud-sun', // few clouds day
                '02n': 'fa-cloud-moon', // few clouds night
                '03d': 'fa-cloud', // scattered clouds
                '03n': 'fa-cloud',
                '04d': 'fa-cloud', // broken clouds
                '04n': 'fa-cloud',
                '09d': 'fa-cloud-showers-heavy', // shower rain
                '09n': 'fa-cloud-showers-heavy',
                '10d': 'fa-cloud-rain', // rain
                '10n': 'fa-cloud-rain',
                '11d': 'fa-bolt', // thunderstorm
                '11n': 'fa-bolt',
                '13d': 'fa-snowflake', // snow
                '13n': 'fa-snowflake',
                '50d': 'fa-smog', // mist
                '50n': 'fa-smog'
            };
            
            return iconMap[iconCode] || 'fa-cloud';
        }
    </script>
</body>
</html>
