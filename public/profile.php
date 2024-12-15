<?php
session_start();
require_once('../config/database.php');

// Cek login
if (!isset($_SESSION['users'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['users'];

// Ambil data pengguna dari database
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user['id']);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();

// Set default profile image if not set
if (!isset($user_data['profile_image']) || empty($user_data['profile_image'])) {
    $user_data['profile_image'] = 'default.png';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $profile_image = $user_data['profile_image'];

    // Handle file upload
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == UPLOAD_ERR_OK) {
        $target_dir = "../uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["profile_image"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                $profile_image = $target_file;
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        } else {
            echo "File is not an image.";
        }
    }

    // Update user data
    $update_query = "UPDATE users SET username = ?, email = ?, profile_image = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("sssi", $username, $email, $profile_image, $user['id']);
    if ($stmt->execute()) {
        // Refresh user data
        $_SESSION['users']['username'] = $username;
        header("Location: profile.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - MBRK Smart Home</title>
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
            padding: 2rem;
            margin-top: 2rem;
        }

        .glass-card:hover {
            border-color: var(--accent-1);
            transform: translateY(-5px);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .profile-header h1 {
            font-size: 2.5rem;
            font-weight: 600;
            color: var(--accent-1);
            text-shadow: 0 0 20px rgba(147, 197, 253, 0.3);
        }

        .profile-header p {
            color: var(--text-secondary);
        }

        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            backdrop-filter: blur(5px);
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--accent-1);
            color: var(--text-primary);
        }

        .btn-primary {
            background-color: var(--accent-1);
            border-color: var(--accent-1);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #60a5fa;
            border-color: #60a5fa;
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

        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
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
                        <a class="nav-link" href="devices.php">Devices</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="profile.php">Profile</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="glass-card">
            <div class="profile-header">
                <h1>Profile</h1>
                <p>Manage your account settings and set e-mail preferences.</p>
                <img src="<?php echo htmlspecialchars($user_data['profile_image']); ?>" alt="Profile Image" class="profile-image">
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="profile_image" class="form-label">Profile Image</label>
                    <input type="file" class="form-control" id="profile_image" name="profile_image">
                </div>
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 