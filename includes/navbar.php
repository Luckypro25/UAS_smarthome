<?php
// Misalnya, session atau variabel user sudah ada
$user = [
    'username' => 'JohnDoe', // Ganti dengan variabel dari session atau database
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MBRK Smart Home</title>
    <!-- Link to Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.12);
            --glass-highlight: rgba(255, 255, 255, 0.15);
            --text-primary: rgba(255, 255, 255, 0.95);  /* white */
            --text-secondary: rgba(255, 255, 255, 0.7);
            --accent-1: #93c5fd;  /* Soft Blue */
            --navbar-bg: #3498db;  /* Blue background for the navbar */
        }

        .navbar {
            background: var(--navbar-bg);
            border-bottom: 1px solid var(--glass-border);
        }

        .navbar .navbar-brand {
            font-weight: bold;
            color: var(--text-primary);
        }

        .navbar .navbar-nav .nav-link {
            color: var(--text-primary);
        }

        .navbar .navbar-nav .nav-link:hover {
            color: var(--accent-1);
        }

        .navbar .dropdown-menu {
            background-color: var(--glass-bg);
            border: 1px solid var(--glass-border);
        }

        .navbar .dropdown-item {
            color: #000; /* Black text for dropdown */
        }

        .navbar .dropdown-item:hover {
            background-color: var(--glass-highlight);
            color: #000; /* Black text on hover */
        }

        .navbar .user-profile img {
            width: 35px;
            height: 35px;
        }

        .navbar .user-profile button {
            background-color: transparent;
            border: none;
            color: var(--text-primary);
        }

        .navbar-toggler-icon {
            background-color: var(--text-primary);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <!-- Logo dan nama MBRK Smart Home -->
            <a class="navbar-brand d-flex align-items-center" href="#">
                <i class="fas fa-home me-2"></i> <!-- Ikon Rumah Pintar -->
                MBRK Smart Home
            </a>
            
            <!-- Toggler button for mobile view -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navbar links -->
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
                
                <!-- User profile and dropdown menu -->
                <div class="d-flex align-items-center">
                    <!-- Avatar -->
                    <div class="dropdown user-profile">
                        <button class="btn dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <!-- Avatar berdasarkan inisial nama -->
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=random" alt="Profile" class="rounded-circle me-2">
                            <?php echo htmlspecialchars($user['username']); ?>
                        </button>
                        
                        <ul class="dropdown-menu" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"></script>
</body>
</html>
