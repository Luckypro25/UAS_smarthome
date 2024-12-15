 <?php
session_start();
require_once('../../config/database.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Check if 'id' is set in the URL
if (!isset($_GET['id'])) {
    echo "Device ID not provided.";
    exit();
}

// Fetch device details
$device_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM devices WHERE id = ?");
$stmt->bind_param("i", $device_id);
$stmt->execute();
$device = $stmt->get_result()->fetch_assoc();

if (!$device) {
    echo "Device not found.";
    exit();
}

// Handle device control
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'] === 'on' ? 1 : 0;

    // Update device status in the database
    $stmt = $conn->prepare("UPDATE devices SET status = ? WHERE id = ?");
    $stmt->bind_param("ii", $status, $device_id);
    $stmt->execute();

    // Redirect back to the control page
    header("Location: control.php?id=" . $device_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Device - MBRK Smart Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e1b4b, #312e81);
            color: white;
            font-family: 'Poppins', sans-serif;
        }
        .control-container {
            margin-top: 50px;
        }
    </style>
</head>
<body>
    <div class="container control-container">
        <h2 class="text-center">Control Device: <?php echo $device['name']; ?></h2>
        <div class="text-center mb-4">
            <h4>Status: <?php echo $device['status'] ? 'On' : 'Off'; ?></h4>
        </div>

        <form method="POST" class="text-center">
            <button type="submit" name="status" value="on" class="btn btn-success">Turn On</button>
            <button type="submit" name="status" value="off" class="btn btn-danger">Turn Off</button>
        </form>

        <div class="text-center mt-4">
            <button id="voiceControlBtn" class="btn btn-primary">Control with Voice</button>
        </div>
    </div>

    <script>
        const voiceControlBtn = document.getElementById('voiceControlBtn');

        voiceControlBtn.addEventListener('click', function() {
            const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
            recognition.lang = 'id-ID'; // Set language to Indonesian
            recognition.interimResults = false;
            recognition.maxAlternatives = 1;

            recognition.start();

            recognition.onresult = function(event) {
                const command = event.results[0][0].transcript.toLowerCase();
                console.log('Voice command received: ' + command);

                if (command.includes('nyalakan') || command.includes('hidupkan')) {
                    document.querySelector('button[name="status"][value="on"]').click();
                } else if (command.includes('matikan') || command.includes('nonaktifkan')) {
                    document.querySelector('button[name="status"][value="off"]').click();
                } else {
                    alert('Perintah tidak dikenali. Silakan coba lagi.');
                }
            };

            recognition.onerror = function(event) {
                console.error('Speech recognition error detected: ' + event.error);
                alert('Terjadi kesalahan saat mendengarkan perintah suara.');
            };
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
