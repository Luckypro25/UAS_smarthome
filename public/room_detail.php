<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once('../config/database.php');

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['users'])) {
    header("Location: login.php");
    exit();
}

// Ambil data pengguna dari session
$user = $_SESSION['users'];

// Periksa apakah ada ID ruangan
if (!isset($_GET['id'])) {
    header("Location: rooms.php");
    exit();
}

$room_id = $_GET['id'];

// Ambil detail ruangan
$room_query = "SELECT * FROM rooms WHERE id = ?";
$stmt = $conn->prepare($room_query);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$room = $stmt->get_result()->fetch_assoc();

if (!$room) {
    header("Location: rooms.php");
    exit();
}

// Ambil daftar perangkat dalam ruangan
$devices_query = "SELECT * FROM devices WHERE room_id = ?";
$stmt = $conn->prepare($devices_query);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$devices_result = $stmt->get_result();

// Ganti bagian handle penambahan perangkat baru
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_device'])) {
    try {
        $device_name = $_POST['device_name'];
        $device_type = $_POST['device_type'];
        $status = 0; // 0 untuk inactive, 1 untuk active

        // Periksa apakah tabel memiliki kolom yang diperlukan
        $check_column = "SHOW COLUMNS FROM devices LIKE 'type'";
        $column_exists = $conn->query($check_column);
        
        if ($column_exists->num_rows == 0) {
            // Jika kolom 'type' belum ada, tambahkan kolom tersebut
            $add_column = "ALTER TABLE devices ADD COLUMN type VARCHAR(20) DEFAULT 'other'";
            $conn->query($add_column);
        }

        $stmt = $conn->prepare("INSERT INTO devices (name, type, room_id, status) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $stmt->bind_param("ssii", $device_name, $device_type, $room_id, $status);
        
        if ($stmt->execute()) {
            header("Location: room_detail.php?id=" . $room_id . "&success=added");
            exit();
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log("Error adding device: " . $e->getMessage());
        header("Location: room_detail.php?id=" . $room_id . "&error=add_failed&message=" . urlencode($e->getMessage()));
        exit();
    }
}
// Handle update perangkat
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_device'])) {
    $device_id = $_POST['device_id'];
    $device_name = $_POST['device_name'];
    $device_type = $_POST['device_type'];

    $stmt = $conn->prepare("UPDATE devices SET name = ?, type = ? WHERE id = ?");
    $stmt->bind_param("ssi", $device_name, $device_type, $device_id);
    
    if ($stmt->execute()) {
        header("Location: room_detail.php?id=" . $room_id);
        exit();
    }
}

// Handle delete perangkat
if (isset($_POST['delete_device'])) {
    $device_id = $_POST['device_id'];

    $stmt = $conn->prepare("DELETE FROM devices WHERE id = ?");
    $stmt->bind_param("i", $device_id);
    
    if ($stmt->execute()) {
        header("Location: room_detail.php?id=" . $room_id);
        exit();
    }
}

// Handle perubahan status perangkat
if (isset($_POST['toggle_status'])) {
    $device_id = $_POST['device_id'];
    $current_status = $_POST['current_status'];
    $new_status = ($current_status == 1) ? 0 : 1;

    $stmt = $conn->prepare("UPDATE devices SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $device_id);
    
    if ($stmt->execute()) {
        header("Location: room_detail.php?id=" . $room_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($room['name']); ?> - MBRK Smart Home</title>
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

        .glass-navbar {
            background: var(--glbgass-);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
        }

        .nav-link {
            color: var(--text-primary) !important;
            padding: 0.5rem 1rem;
            margin: 0 0.2rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background: var(--glass-highlight);
        }

        .nav-link.active {
            background: var(--accent-1);
            color: #fff !important;
        }

        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: var(--accent-1);
        }

        .device-card {
            background: var(--glass-bg);
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

        .device-card.active .device-icon {
            color: #4ade80;
        }

        .device-card.inactive .device-icon {
            color: #f87171;
        }

        .device-info {
            text-align: center;
            margin-bottom: 1rem;
        }

        .form-control {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: var(--text);
        }

        .form-control:focus {
            background: var(--glass-bg);
            border-color: var(--accent-1);
            color: var(--text-secondary);
            box-shadow: none;
        }

        .btn-primary {
            background: var(--accent-1);
            border: none;
        }

        .btn-primary:hover {
            background: #7cb3fc;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .status-active {
            background: #4ade80;
            color: #064e3b;
        }

        .status-inactive {
            background: #f87171;
            color: #7f1d1d;
        }

        .notification {
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            min-width: 300px;
            padding: 15px 20px;
            border-radius: 10px;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease-out;
        }

        .notification i {
            font-size: 1.2rem;
        }

        .notification.success {
            border-left: 4px solid #4ade80;
        }

        .notification.warning {
            border-left: 4px solid #fbbf24;
        }

        .notification.error {
            border-left: 4px solid #f87171;
        }

        .notification.info {
            border-left: 4px solid #60a5fa;
        }

        @keyframes slideDown {
            from {
                transform: translate(-50%, -20px);
                opacity: 0;
            }
            to {
                transform: translate(-50%, 0);
                opacity: 1;
            }
        }

        #voiceControlBtn.listening {
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .device-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .modal-content {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
        }

        .modal-header {
            border-bottom-color: var(--glass-border);
        }

        .modal-footer {
            border-top-color: var(--glass-border);
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg glass-navbar sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="#">
                <i class="fas fa-home me-2"></i>MBRK Smart Home
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars text-light"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="rooms.php">Rooms</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="devices.php">Devices</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-light"><?php echo htmlspecialchars($room['name']); ?></h2>
        <a href="rooms.php" class="btn btn-outline-light">
            <i class="fas fa-arrow-left me-2"></i>Back to Rooms
        </a>
    </div>

        <!-- Add Device Form -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Add New Device</h5>
                <form method="POST" class="d-flex gap-3">
                    <input type="text" class="form-control" name="device_name" placeholder="Device Name" required>
                    <select class="form-control" name="device_type" required>
                        <option value="light">Light</option>
                        <option value="ac">AC</option>
                        <option value="tv">TV</option>
                        <option value="fan">Fan</option>
                        <option value="other">Other</option>
                    </select>
                    <button type="submit" name="add_device" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add Device
                    </button>
                </form>
            </div>
        </div>
        <!-- Voice Control Button -->
        <div class="d-flex align-items-center mt-3 pt-3 border-top">
                <button id="voiceControlBtn" class="btn btn-primary d-flex align-items-center gap-2">
                    <i class="fas fa-microphone"></i>
                    <span>Voice Control</span>
                </button>
                <div class="ms-3 text-secondary">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        Tersedia perintah: "nyalakan/matikan [nama device]" atau "nyalakan/matikan semua"
                    </small>
                </div>
            </div>
        <!-- Devices List -->
        <div class="row">
            <?php while ($device = $devices_result->fetch_assoc()): 
                $deviceType = $device['type'] ?? 'other';
                $deviceIcon = '';
                switch($deviceType) {
                    case 'light':
                        $deviceIcon = 'fa-lightbulb';
                        break;
                    case 'ac':
                        $deviceIcon = 'fa-snowflake';
                        break;
                    case 'tv':
                        $deviceIcon = 'fa-tv';
                        break;
                    case 'fan':
                        $deviceIcon = 'fa-fan';
                        break;
                    default:
                        $deviceIcon = 'fa-plug';
                }
            ?>
            <div class="col-md-4 mb-4">
                <div class="device-card <?php echo $device['status'] == 1 ? 'active' : 'inactive'; ?>">
                    <div class="device-info">
                        <i class="fas <?php echo $deviceIcon; ?> device-icon"></i>
                        <h5 class="mb-0"><?php echo htmlspecialchars($device['name']); ?></h5>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="status-badge <?php echo $device['status'] == 1 ? 'status-active' : 'status-inactive'; ?>">
                            <?php echo $device['status'] == 1 ? 'Active' : 'Inactive'; ?>
                        </span>
                    </div>
                    <form method="POST" class="mb-2">
                        <input type="hidden" name="device_id" value="<?php echo $device['id']; ?>">
                        <input type="hidden" name="current_status" value="<?php echo $device['status']; ?>">
                        <button type="submit" name="toggle_status" class="btn btn-primary w-100">
                            <i class="fas <?php echo $device['status'] == 1 ? 'fa-power-off' : 'fa-play'; ?> me-2"></i>
                            <?php echo $device['status'] == 1 ? 'Turn Off' : 'Turn On'; ?>
                        </button>
                    </form>
                    <div class="device-actions">
                        <button type="button" class="btn btn-warning" 
                                onclick="editDevice(<?php echo $device['id']; ?>, '<?php echo htmlspecialchars($device['name']); ?>', '<?php echo $device['type']; ?>')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button type="button" class="btn btn-danger" 
                                onclick="deleteDevice(<?php echo $device['id']; ?>, '<?php echo htmlspecialchars($device['name']); ?>')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- Edit Device Modal -->
    <div class="modal fade" id="editDeviceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Device</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="device_id" id="edit_device_id">
                        <div class="mb-3">
                            <label class="form-label">Device Name</label>
                            <input type="text" class="form-control" name="device_name" id="edit_device_name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Device Type</label>
                            <select class="form-control" name="device_type" id="edit_device_type" required>
                                <option value="light">Light</option>
                                <option value="ac">AC</option>
                                <option value="tv">TV</option>
                                <option value="fan">Fan</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_device" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Device Modal -->
    <div class="modal fade" id="deleteDeviceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Delete Device</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete <span id="delete_device_name"></span>?
                </div>
                <div class="modal-footer">
                    <form method="POST">
                        <input type="hidden" name="device_id" id="delete_device_id">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_device" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        
        // Inisialisasi modal Bootstrap
        const editModal = new bootstrap.Modal(document.getElementById('editDeviceModal'));
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteDeviceModal'));
    // Tambahkan di bagian script
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const error = urlParams.get('error');
    const message = urlParams.get('message');
    
    if (success === 'added') {
        showNotification('Perangkat berhasil ditambahkan', 'success');
    }
    
    if (error === 'add_failed') {
        showNotification('Gagal menambahkan perangkat: ' + (message || 'Unknown error'), 'error');
    }
});
        // Fungsi untuk edit device
        function editDevice(id, name, type) {
            document.getElementById('edit_device_id').value = id;
            document.getElementById('edit_device_name').value = name;
            document.getElementById('edit_device_type').value = type;
            editModal.show();
        }

        // Fungsi untuk delete device
        function deleteDevice(id, name) {
            document.getElementById('delete_device_id').value = id;
            document.getElementById('delete_device_name').textContent = name;
            deleteModal.show();
        }

        // Voice Control
        const voiceControlBtn = document.getElementById('voiceControlBtn');
        let isListening = false;

        // Inisialisasi Web Speech API
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const recognition = new SpeechRecognition();

        // Inisialisasi Text to Speech
        const synth = window.speechSynthesis;

        recognition.lang = 'id-ID';
        recognition.continuous = false;
        recognition.interimResults = false;

        // Fungsi untuk mengucapkan text
        function speak(text) {
            // Hentikan speech yang sedang berjalan
            synth.cancel();

            const utterance = new SpeechSynthesisUtterance(text);
            utterance.lang = 'id-ID';
            utterance.volume = 1;
            utterance.rate = 1;
            utterance.pitch = 1;

            // Dapatkan suara dalam bahasa Indonesia jika tersedia
            const voices = synth.getVoices();
            const indonesianVoice = voices.find(voice => voice.lang.includes('id-ID'));
            if (indonesianVoice) {
                utterance.voice = indonesianVoice;
            }

            synth.speak(utterance);
        }

        // Event ketika tombol voice control diklik
        voiceControlBtn.addEventListener('click', () => {
            if (!isListening) {
                recognition.start();
                voiceControlBtn.innerHTML = '<i class="fas fa-microphone-slash me-2"></i>Stop Listening';
                voiceControlBtn.classList.add('btn-danger', 'listening');
                voiceControlBtn.classList.remove('btn-primary');
                showNotification('Voice control aktif', 'success');
                speak("Sistem siap menerima perintah");
            } else {
                recognition.stop();
                voiceControlBtn.innerHTML = '<i class="fas fa-microphone me-2"></i>Voice Control';
                voiceControlBtn.classList.remove('btn-danger', 'listening');
                voiceControlBtn.classList.add('btn-primary');
                showNotification('Voice control nonaktif', 'info');
                speak("Sistem berhenti mendengarkan");
            }
            isListening = !isListening;
        });

        // Event ketika suara terdeteksi
        recognition.onresult = (event) => {
            const command = event.results[0][0].transcript.toLowerCase();
            console.log('Command:', command);
            showNotification(`Perintah terdeteksi: "${command}"`, 'info');

            // Dapatkan semua perangkat
            const devices = document.querySelectorAll('.device-card');
            let deviceFound = false;
            
            // Handle perintah untuk semua perangkat
            if (command.includes('matikan semua')) {
                let devicesChanged = 0;
                devices.forEach(device => {
                    const currentStatus = device.querySelector('input[name="current_status"]').value;
                    if (currentStatus == '1') {
                        device.querySelector('button[name="toggle_status"]').click();
                        devicesChanged++;
                    }
                });
                if (devicesChanged > 0) {
                    speak(`Mematikan ${devicesChanged} perangkat`);
                    showNotification(`Berhasil mematikan ${devicesChanged} perangkat`, 'success');
                } else {
                    speak("Semua perangkat sudah dalam keadaan mati");
                    showNotification("Semua perangkat sudah dalam keadaan mati", 'warning');
                }
                return;
            } 
            else if (command.includes('nyalakan semua') || command.includes('hidupkan semua')) {
                let devicesChanged = 0;
                devices.forEach(device => {
                    const currentStatus = device.querySelector('input[name="current_status"]').value;
                    if (currentStatus == '0') {
                        device.querySelector('button[name="toggle_status"]').click();
                        devicesChanged++;
                    }
                });
                if (devicesChanged > 0) {
                    speak(`Menghidupkan ${devicesChanged} perangkat`);
                    showNotification(`Berhasil menghidupkan ${devicesChanged} perangkat`, 'success');
                } else {
                    speak("Semua perangkat sudah dalam keadaan hidup");
                    showNotification("Semua perangkat sudah dalam keadaan hidup", 'warning');
                }
                return;
            }

            // Handle perintah untuk perangkat individual
            devices.forEach(device => {
                const deviceName = device.querySelector('h5').textContent.toLowerCase();
                const toggleButton = device.querySelector('button[name="toggle_status"]');
                const currentStatus = device.querySelector('input[name="current_status"]').value;
                
                if (command.includes(deviceName)) {
                    deviceFound = true;
                    if (command.includes('nyalakan') || command.includes('hidupkan') || command.includes('on')) {
                        if (currentStatus == '0') {
                            toggleButton.click();
                            speak(`Menghidupkan ${deviceName}`);
                            showNotification(`Berhasil menghidupkan ${deviceName}`, 'success');
                        } else {
                            speak(`${deviceName} sudah dalam keadaan hidup`);
                            showNotification(`${deviceName} sudah dalam keadaan hidup`, 'warning');
                        }
                    } else if (command.includes('matikan') || command.includes('off')) {
                        if (currentStatus == '1') {
                            toggleButton.click();
                            speak(`Mematikan ${deviceName}`);
                            showNotification(`Berhasil mematikan ${deviceName}`, 'success');
                        } else {
                            speak(`${deviceName} sudah dalam keadaan mati`);
                            showNotification(`${deviceName} sudah dalam keadaan mati`, 'warning');
                        }
                    }
                }
            });

            if (!deviceFound && !command.includes('semua')) {
                speak("Maaf, perangkat tidak ditemukan");
                showNotification("Perangkat tidak ditemukan", 'error');
            }
        };

        // Event ketika recognition selesai
        recognition.onend = () => {
            if (isListening) {
                recognition.start();
            }
        };

        // Event ketika terjadi error
        recognition.onerror = (event) => {
            console.error('Speech recognition error:', event.error);
            showNotification('Error: ' + event.error, 'error');
            speak("Terjadi kesalahan pada sistem voice control");
            isListening = false;
            voiceControlBtn.innerHTML = '<i class="fas fa-microphone me-2"></i>Voice Control';
            voiceControlBtn.classList.remove('btn-danger', 'listening');
            voiceControlBtn.classList.add('btn-primary');
        };

        // Fungsi untuk menampilkan notifikasi
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            
            let icon;
            switch(type) {
                case 'success':
                    icon = '<i class="fas fa-check-circle"></i>';
                    break;
                case 'warning':
                    icon = '<i class="fas fa-exclamation-triangle"></i>';
                    break;
                case 'error':
                    icon = '<i class="fas fa-times-circle"></i>';
                    break;
                case 'info':
                default:
                    icon = '<i class="fas fa-info-circle"></i>';
                    break;
            }

            notification.innerHTML = `
                ${icon}
                <span>${message}</span>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translate(-50%, -20px)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
