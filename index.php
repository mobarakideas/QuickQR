<?php
session_start();
include "db.php";

$message = "";

// Registration
if (isset($_POST['register'])) {
    $name = $_POST['r_name'];
    $email = $_POST['r_email'];
    $password = password_hash($_POST['r_password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $email, $password])) {
        $message = "Registration successful. Please login.";
    } else {
        $message = "Registration failed. Email might be taken.";
    }
}

// Login
if (isset($_POST['login'])) {
    $email = $_POST['l_email'];
    $password = $_POST['l_password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header("Location: generate.php");
        exit();
    } else {
        $message = "Login failed.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>QuickQR | Login & Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: #fff;
            font-family: 'Segoe UI', sans-serif;
            min-height: 100vh;
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.5);
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6);
        }

        .btn-primary {
            background: #ff416c;
            border: none;
        }

        .btn-success {
            background: #00c9ff;
            border: none;
        }

        .btn-primary:hover {
            background: #ff4b2b;
        }

        .btn-success:hover {
            background: #007bb6;
        }

        .slide-in {
            animation: slideIn 0.8s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(30px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center mb-4 slide-in">🚀 QuickQR - Login / Register <img src="QRbutterfly.png" alt="" style="height: 3.5rem; width: 3.5rem;"></h2>

    <?php if ($message): ?>
        <div class="alert alert-warning text-center"><?= $message ?></div>
    <?php endif; ?>

    <div class="row justify-content-center g-4 slide-in">
        <!-- Login Card -->
        <div class="col-md-5">
            <div class="card p-4">
                <h4 class="text-center text-white">🔐 Login</h4>
                <form method="POST">
                    <input type="email" name="l_email" class="form-control mb-3" placeholder="Email" required>
                    <input type="password" name="l_password" class="form-control mb-3" placeholder="Password" required>
                    <button name="login" class="btn btn-success w-100">Login</button>
                </form>
            </div>
        </div>

        <!-- Register Card -->
        <div class="col-md-5">
            <div class="card p-4">
                <h4 class="text-center text-white">📝 Register</h4>
                <form method="POST">
                    <input type="text" name="r_name" class="form-control mb-3" placeholder="Name" required>
                    <input type="email" name="r_email" class="form-control mb-3" placeholder="Email" required>
                    <input type="password" name="r_password" class="form-control mb-3" placeholder="Password" required>
                    <button name="register" class="btn btn-primary w-100">Register</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>
