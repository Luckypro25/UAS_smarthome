<?php
session_start();
require_once('../config/database.php');

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['users'])) {  // Ubah dari 'user_id' menjadi 'users'
    header("Location: login.php");
    exit();
}

// Ambil data pengguna dari session
$user = $_SESSION['users'];  // Ubah dari $_SESSION['user_id'] menjadi $_SESSION['users']

// Fetch rooms from database
$rooms_query = "SELECT * FROM rooms";
$rooms_result = $conn->query($rooms_query);

// Handle form submission for adding a new room
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_room'])) {
    $room_name = $_POST['room_name'];
    $room_description = $_POST['room_description'];

    // Insert new room into the database
    $stmt = $conn->prepare("INSERT INTO rooms (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $room_name, $room_description);
    $stmt->execute();

    // Redirect to the same page to see the new room
    header("Location: rooms.php");
    exit();
}

// Handle room deletion
if (isset($_GET['delete'])) {
    $room_id = $_GET['delete'];
    
    // Mulai transaksi
    $conn->begin_transaction();
    
    try {
        // Hapus devices yang terkait dengan room terlebih dahulu
        $stmt = $conn->prepare("DELETE FROM devices WHERE room_id = ?");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        
        // Kemudian hapus room
        $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->bind_param("i", $room_id);
        $stmt->execute();
        
        // Commit transaksi jika semua berhasil
        $conn->commit();
        
        header("Location: rooms.php");
        exit();
    } catch (Exception $e) {
        // Rollback jika terjadi error
        $conn->rollback();
        $error = "Gagal menghapus ruangan: " . $e->getMessage();
    }
}

// Handle room update
if (isset($_POST['update_room'])) {
    $room_id = $_POST['room_id'];
    $room_name = $_POST['room_name'];
    $room_description = $_POST['room_description'];

    $stmt = $conn->prepare("UPDATE rooms SET name = ?, description = ? WHERE id = ?");
    $stmt->bind_param("ssi", $room_name, $room_description, $room_id);
    $stmt->execute();
    header("Location: rooms.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms - MBRK Smart Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--glass-border);
        }
        .navbar-brand {
            color: var(---glass-border) !important;
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

        .dashboard-container {
            padding: 2rem 1rem;
        }

        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 15px;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-5px);
            border-color: var(--accent-1);
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .card-text {
            color: var(--text-secondary);
        }

        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--accent-1);
            border: none;
        }

        .btn-primary:hover {
            background: #7cb3fc;
            transform: translateY(-2px);
        }

        .input-group {
            background: var(--glass-bg);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .form-control {
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            color: var(--text-primary);
            padding: 0.8rem;
        }

        .form-control:focus {
            background: var(--glass-highlight);
            border-color: var(--accent-1);
            color: var(--text-primary);
            box-shadow: none;
        }

        .modal-content {
            background: linear-gradient(135deg, #1a1a1a, #2c3e50);
            border: 1px solid var(--glass-border);
        }

        .modal-header {
            border-bottom: 1px solid var(--glass-border);
            color: var(--text-primary);
        }

        .modal-footer {
            border-top: 1px solid var(--glass-border);
        }

        .btn-close {
            color: var(--text-primary);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--glass-border);
        }

        .dropdown-menu {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
        }

        .dropdown-item {
            color: var(--text-primary);
        }

        .dropdown-item:hover {
            background: var(--glass-highlight);
            color: var(--text-primary);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeIn 0.5s ease forwards;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg glass-navbar sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-home"></i> MBRK Smart Home
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <i class="fas fa-bars text-light"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-chart-line me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="rooms.php">
                            <i class="fas fa-door-open me-2"></i>Rooms
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="devices.php">
                            <i class="fas fa-microchip me-2"></i>Devices
                        </a>
                    </li>
                </ul>
                <div class="user-profile">
                    <img src="https://ui-avatars.com/api/?name=<?php echo $user['username']; ?>&background=random" alt="Profile">
                    <div class="dropdown">
                        <button class="btn dropdown-toggle text-light" type="button" data-bs-toggle="dropdown">
                            <?php echo $user['username']; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end glass-container">
                            <li><a class="dropdown-item" href="profile.php">
                                <i class="fas fa-user me-2"></i>Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="dashboard-container">
        <div class="container-fluid">
            <h2 class="mb-4 text-light font-bold">Rooms</h2>

            <!-- Add Room Form -->
            <form method="POST" class="mb-4">
                <div class="input-group">
                    <input type="text" class="form-control" name="room_name" placeholder="Room Name" required>
                    <input type="text" class="form-control" name="room_description" placeholder="Room Description" required>
                    <button class="btn btn-primary" type="submit" name="add_room">
                        <i class="fas fa-plus"></i> Add Room
                    </button>
                </div>
            </form>

            <!-- Rooms Section -->
            <div class="row g-4 mb-4">
                <?php while ($room = $rooms_result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card room-card">
                        <div class="card-body">
                            <h5 class="card-title text-light"><?php echo $room['name']; ?></h5>
                            <p class="card-text text-secondary"><?php echo $room['description']; ?></p>
                            <a href="room_detail.php?id=<?php echo $room['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-arrow-right"></i> View Devices
                            </a>
                            <a href="#" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#editRoomModal<?php echo $room['id']; ?>">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="?delete=<?php echo $room['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this room?');">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </div>
                    </div>

                    <!-- Edit Room Modal -->
                    <div class="modal fade" id="editRoomModal<?php echo $room['id']; ?>" tabindex="-1" aria-labelledby="editRoomModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editRoomModalLabel">Edit Room</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                        <div class="mb-3">
                                            <label for="room_name" class="form-label">Room Name</label>
                                            <input type="text" class="form-control" name="room_name" value="<?php echo $room['name']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="room_description" class="form-label">Room Description</label>
                                            <input type="text" class="form-control" name="room_description" value="<?php echo $room['description']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary" name="update_room">Update Room</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>