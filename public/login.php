<?php
session_start();
require_once('../config/database.php');

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email']) && isset($_POST['password'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $query = "SELECT * FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION["users"] = [
                        'id' => $user['id'],
                        'username' => $user['username'],
                        'email' => $user['email']
                    ];
                    header("Location: index.php");
                    exit();
                } else {
                    $error = "Password yang Anda masukkan salah.";
                }
            } else {
                $error = "Email tidak ditemukan.";
            }
            $stmt->close();
        } else {
            $error = "Terjadi kesalahan dalam memproses permintaan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MBRK Smart Home</title>
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

        .auth-container {
            display: flex;
            min-height: 100vh;
        }

        .auth-brand {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
        }

        .brand-content {
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .brand-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--accent-1);
            text-shadow: 0 0 20px rgba(147, 197, 253, 0.3);
        }

        .brand-content p {
            font-size: 1.2rem;
            color: var(--text-secondary);
            max-width: 600px;
            margin: 0 auto;
        }

        .auth-form {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
        }

        .form-container {
            width: 100%;
            max-width: 450px;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 20px;
            border: 1px solid var(--glass-border);
            backdrop-filter: blur(10px);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h2 {
            color: var(--accent-1);
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: var(--text-secondary);
            font-size: 1rem;
        }

        .form-floating {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-floating input {
            height: 60px !important;
            padding: 1rem !important;
            font-size: 1rem;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid var(--glass-border);
            color: var(--text-primary) !important;
        }

        .form-floating input:focus {
            background: rgba(255, 255, 255, 0.08) !important;
            border-color: var(--accent-1);
            box-shadow: 0 0 0 0.25rem rgba(147, 197, 253, 0.25);
        }

        .form-floating label {
            padding: 1rem !important;
            color: var(--text-secondary);
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            background: var(--accent-1);
            border: none;
            color: white;
            margin-top: 1rem;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            background: #60a5fa;
            box-shadow: 0 5px 15px rgba(147, 197, 253, 0.3);
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-secondary);
        }

        .register-link a {
            color: var(--accent-1);
            text-decoration: none;
            font-weight: 600;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .alert {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.3);
            color: #ff6b6b;
        }

        @media (max-width: 768px) {
            .auth-container {
                flex-direction: column;
            }

            .auth-brand {
                padding: 2rem 1rem;
            }

            .brand-content h1 {
                font-size: 2.5rem;
            }

            .auth-form {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Brand Section -->
        <div class="auth-brand">
            <div class="brand-content">
                <h1>MBRK Smart Home</h1>
                <p>Kontrol rumah Anda dengan mudah dan aman menggunakan teknologi smart home terkini</p>
            </div>
        </div>

        <!-- Form Section -->
        <div class="auth-form">
            <div class="form-container">
                <div class="form-header">
                    <h2>Login</h2>
                    <p>Masuk ke akun MBRK Smart Home Anda</p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="email" name="email" placeholder=" " required>
                        <label for="email">Email address</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="password" class="form-control" id="password" name="password" placeholder=" " required>
                        <label for="password">Password</label>
                    </div>

                    <button type="submit" class="btn btn-login">
                        Login
                    </button>
                </form>

                <div class="register-link">
                    Belum punya akun? <a href="register.php">Daftar disini</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
